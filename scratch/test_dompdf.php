<?php

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

echo "Testing Dompdf...\n";

try {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml('<h1>Test Ticket</h1><p>Hello World</p>');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $output = $dompdf->output();
    echo "PDF generated successfully: " . strlen($output) . " bytes\n";
} catch (\Throwable $e) {
    echo "Dompdf Error: " . $e->getMessage() . "\n";
}
