<?php
/**
 * Reverse Engineering Script — génère les entités Symfony depuis la DB
 * Usage : php reverse-engineer.php
 */

$dbName   = 'java';
$host     = '127.0.0.1';
$user     = 'root';
$password = '';

$entityDir    = __DIR__ . '/src/Entity';
$repositoryDir = __DIR__ . '/src/Repository';

if (!is_dir($entityDir))     mkdir($entityDir,     0777, true);
if (!is_dir($repositoryDir)) mkdir($repositoryDir, 0777, true);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Connexion échouée : " . $e->getMessage() . "\n");
}

// ── Récupérer toutes les tables ──
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// ── Récupérer les clés étrangères ──
$fkQuery = $pdo->prepare("
    SELECT
        TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = :db
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
$fkQuery->execute([':db' => $dbName]);
$foreignKeys = $fkQuery->fetchAll(PDO::FETCH_ASSOC);

// Indexer les FK par table
$fkMap = [];
foreach ($foreignKeys as $fk) {
    $fkMap[$fk['TABLE_NAME']][$fk['COLUMN_NAME']] = [
        'ref_table'  => $fk['REFERENCED_TABLE_NAME'],
        'ref_column' => $fk['REFERENCED_COLUMN_NAME'],
    ];
}

// ── Helpers ──
function toCamelCase(string $str): string {
    return lcfirst(str_replace('_', '', ucwords($str, '_')));
}
function toPascalCase(string $str): string {
    return str_replace('_', '', ucwords($str, '_'));
}
function phpType(string $mysqlType): string {
    if (str_contains($mysqlType, 'int'))     return 'int';
    if (str_contains($mysqlType, 'float') || str_contains($mysqlType, 'double') || str_contains($mysqlType, 'decimal')) return 'float';
    if (str_contains($mysqlType, 'bool') || str_contains($mysqlType, 'tinyint(1)')) return 'bool';
    if (str_contains($mysqlType, 'date') || str_contains($mysqlType, 'time'))       return '\DateTimeInterface';
    return 'string';
}
function doctrineType(string $mysqlType): string {
    if (str_contains($mysqlType, 'tinyint(1)')) return 'boolean';
    if (str_contains($mysqlType, 'bigint'))  return 'bigint';
    if (str_contains($mysqlType, 'int'))     return 'integer';
    if (str_contains($mysqlType, 'float'))   return 'float';
    if (str_contains($mysqlType, 'double'))  return 'float';
    if (str_contains($mysqlType, 'decimal')) return 'decimal';
    if (str_contains($mysqlType, 'text'))    return 'text';
    if (str_contains($mysqlType, 'datetime')) return 'datetime';
    if (str_contains($mysqlType, 'date'))    return 'date';
    if (str_contains($mysqlType, 'time'))    return 'time';
    if (str_contains($mysqlType, 'json'))    return 'json';
    return 'string';
}

foreach ($tables as $table) {
    $className  = toPascalCase($table);
    $columns    = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $tableFks   = $fkMap[$table] ?? [];

    $properties  = '';
    $getterSetters = '';
    $useStatements = '';
    $constructorLines = '';
    $usedClasses = [];

    foreach ($columns as $col) {
        $colName  = $col['Field'];
        $colType  = strtolower($col['Type']);
        $nullable = $col['Null'] === 'YES';
        $propName = toCamelCase($colName);

        // Clé primaire
        if ($col['Key'] === 'PRI') {
            $properties .= "    #[ORM\\Id]\n";
            $properties .= "    #[ORM\\GeneratedValue]\n";
            $properties .= "    #[ORM\\Column]\n";
            $properties .= "    private ?int \$$propName = null;\n\n";

            $getterSetters .= "    public function get" . ucfirst($propName) . "(): ?int\n    {\n        return \$this->$propName;\n    }\n\n";
            continue;
        }

        // Clé étrangère → ManyToOne
        if (isset($tableFks[$colName])) {
            $refTable     = $tableFks[$colName]['ref_table'];
            $refClass     = toPascalCase($refTable);
            $relPropName  = toCamelCase(preg_replace('/_id$/i', '', $colName));

            if (!in_array($refClass, $usedClasses)) {
                $usedClasses[] = $refClass;
            }

            // Les relations ManyToOne sont toujours nullable avec = null
            $properties .= "    #[ORM\\ManyToOne(targetEntity: $refClass::class)]\n";
            $properties .= "    #[ORM\\JoinColumn(nullable: true)]\n";
            $properties .= "    private ?$refClass \$$relPropName = null;\n\n";

            $getterSetters .= "    public function get" . ucfirst($relPropName) . "(): ?$refClass\n    {\n        return \$this->$relPropName;\n    }\n\n";
            $getterSetters .= "    public function set" . ucfirst($relPropName) . "(?$refClass \$$relPropName): static\n    {\n        \$this->$relPropName = \$$relPropName;\n        return \$this;\n    }\n\n";
            continue;
        }

        // Colonne normale
        $dType   = doctrineType($colType);
        $pType   = phpType($colType);
        $nullStr = $nullable ? '?' : '';

        $colAttr = "    #[ORM\\Column";
        $extras  = [];
        if ($dType !== 'string')  $extras[] = "type: '$dType'";
        if ($nullable)            $extras[] = "nullable: true";
        if (str_contains($colType, 'decimal')) {
            preg_match('/decimal\((\d+),(\d+)\)/', $colType, $m);
            if ($m) { $extras[] = "precision: {$m[1]}"; $extras[] = "scale: {$m[2]}"; }
        }
        if (preg_match('/varchar\((\d+)\)/', $colType, $m)) {
            $extras[] = "length: {$m[1]}";
        }
        $colAttr .= $extras ? '(' . implode(', ', $extras) . ')' : '';
        $colAttr .= "]\n";

        $defaultVal = $nullable ? ' = null' : ($pType === 'int' ? ' = 0' : ($pType === 'float' ? ' = 0.0' : ($pType === 'bool' ? ' = false' : ($pType === '\DateTimeInterface' ? '' : " = ''"))));
        // DateTimeInterface ne peut pas avoir de valeur par défaut scalaire
        if ($pType === '\DateTimeInterface') {
            $nullStr = '?';
            $defaultVal = ' = null';
        }
        $properties .= $colAttr;
        $properties .= "    private {$nullStr}$pType \$$propName$defaultVal;\n\n";

        $getterSetters .= "    public function get" . ucfirst($propName) . "(): {$nullStr}$pType\n    {\n        return \$this->$propName;\n    }\n\n";
        $getterSetters .= "    public function set" . ucfirst($propName) . "({$nullStr}$pType \$$propName): static\n    {\n        \$this->$propName = \$$propName;\n        return \$this;\n    }\n\n";
    }

    // use statements pour les entités référencées
    foreach ($usedClasses as $cls) {
        $useStatements .= "use App\\Entity\\$cls;\n";
    }

    // ── Générer le fichier Entity ──
    $entityContent = "<?php\n\nnamespace App\\Entity;\n\nuse App\\Repository\\{$className}Repository;\nuse Doctrine\\ORM\\Mapping as ORM;\n$useStatements\n#[ORM\\Entity(repositoryClass: {$className}Repository::class)]\n#[ORM\\Table(name: '$table')]\nclass $className\n{\n$properties$getterSetters}\n";

    file_put_contents("$entityDir/$className.php", $entityContent);
    echo "✅ Entity créée : $className\n";

    // ── Générer le fichier Repository ──
    $repoContent = "<?php\n\nnamespace App\\Repository;\n\nuse App\\Entity\\$className;\nuse Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository;\nuse Doctrine\\Persistence\\ManagerRegistry;\n\n/**\n * @extends ServiceEntityRepository<$className>\n */\nclass {$className}Repository extends ServiceEntityRepository\n{\n    public function __construct(ManagerRegistry \$registry)\n    {\n        parent::__construct(\$registry, $className::class);\n    }\n}\n";

    file_put_contents("$repositoryDir/{$className}Repository.php", $repoContent);
    echo "✅ Repository créé : {$className}Repository\n";
}

echo "\n🎉 Terminé ! Lance maintenant : php bin/console make:entity --regenerate\n";
