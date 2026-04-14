<?php

namespace App\Tests\Entity;

use App\Entity\ParticipationDemande;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ParticipationDemandeValidationTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function makeValid(): ParticipationDemande
    {
        $d = new ParticipationDemande();
        $d->setClientId(47);
        $d->setClientNom('Anoire Douiri');
        $d->setClientEmail('anoire@test.com');
        $d->setClientTelephone('55123456');
        $d->setStatut('EN_ATTENTE');
        $d->setDateDemande(new \DateTime());
        return $d;
    }

    /** Filtre les erreurs sur 'activite' (nécessite DB) */
    private function erreursSansActivite(ConstraintViolationListInterface $errors): array
    {
        return array_filter(
            iterator_to_array($errors),
            fn($e) => $e->getPropertyPath() !== 'activite'
        );
    }

    // ── CLIENT NOM ──

    public function testClientNomObligatoire(): void
    {
        $d = $this->makeValid();
        $d->setClientNom('');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Nom vide doit générer une erreur');
    }

    public function testClientNomTropCourt(): void
    {
        $d = $this->makeValid();
        $d->setClientNom('A');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Nom trop court doit générer une erreur');
    }

    public function testClientNomValide(): void
    {
        $d = $this->makeValid();
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertEmpty($errors, 'Demande valide ne doit pas avoir d\'erreurs');
    }

    // ── EMAIL ──

    public function testEmailObligatoire(): void
    {
        $d = $this->makeValid();
        $d->setClientEmail('');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Email vide doit générer une erreur');
    }

    public function testEmailInvalide(): void
    {
        $d = $this->makeValid();
        $d->setClientEmail('pas-un-email');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Email invalide doit générer une erreur');
    }

    public function testEmailValide(): void
    {
        $d = $this->makeValid();
        $d->setClientEmail('test@example.com');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertEmpty($errors, 'Email valide ne doit pas générer d\'erreur');
    }

    // ── TELEPHONE ──

    public function testTelephoneObligatoire(): void
    {
        $d = $this->makeValid();
        $d->setClientTelephone('');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Téléphone vide doit générer une erreur');
    }

    public function testTelephoneFormatInvalide(): void
    {
        $d = $this->makeValid();
        $d->setClientTelephone('abc');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Téléphone avec lettres doit générer une erreur');
    }

    public function testTelephoneTropCourt(): void
    {
        $d = $this->makeValid();
        $d->setClientTelephone('123');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Téléphone trop court doit générer une erreur');
    }

    public function testTelephoneValide(): void
    {
        $d = $this->makeValid();
        $d->setClientTelephone('55123456');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertEmpty($errors, 'Téléphone valide ne doit pas générer d\'erreur');
    }

    // ── STATUT ──

    public function testStatutInvalide(): void
    {
        $d = $this->makeValid();
        $d->setStatut('STATUT_INCONNU');
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'Statut invalide doit générer une erreur');
    }

    public function testStatutValide(): void
    {
        foreach (['EN_ATTENTE', 'ACCEPTEE', 'REFUSEE'] as $statut) {
            $d = $this->makeValid();
            $d->setStatut($statut);
            $errors = $this->erreursSansActivite($this->validator->validate($d));
            $this->assertEmpty($errors, "Statut '$statut' devrait être valide");
        }
    }

    // ── CLIENT ID ──

    public function testClientIdNegatifInvalide(): void
    {
        $d = $this->makeValid();
        $d->setClientId(-1);
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'ClientId négatif doit générer une erreur');
    }

    public function testClientIdValide(): void
    {
        $d = $this->makeValid();
        $d->setClientId(47);
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertEmpty($errors);
    }

    // ── DATE DEMANDE ──

    public function testDateDemandeObligatoire(): void
    {
        $d = $this->makeValid();
        $d->setDateDemande(null);
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertNotEmpty($errors, 'DateDemande null doit générer une erreur');
    }

    public function testDateDemandeValide(): void
    {
        $d = $this->makeValid();
        $d->setDateDemande(new \DateTime());
        $errors = $this->erreursSansActivite($this->validator->validate($d));
        $this->assertEmpty($errors);
    }
}
