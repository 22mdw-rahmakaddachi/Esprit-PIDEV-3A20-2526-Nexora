<?php

namespace App\Tests\Entity;

use App\Entity\Destination;
use App\Entity\DestinationImage;
use PHPUnit\Framework\TestCase;

class DestinationTest extends TestCase
{
    // ─── Helper ─────────────────────────────────────────────────────────────

    private function makeDestination(): Destination
    {
        $d = new Destination();
        $d->setNom('Sahara Tunisien');
        $d->setDescription('Un désert magnifique.');
        $d->setLocalisation('Tozeur, Tunisie');
        $d->setCapaciteMax(20);
        $d->setNbParticipants(5);
        $d->setDateLancement(new \DateTime('+30 days'));
        return $d;
    }

    // ─── Tests getters / setters simples ────────────────────────────────────

    public function testSetAndGetNom(): void
    {
        $d = new Destination();
        $d->setNom('Paris');
        $this->assertEquals('Paris', $d->getNom());
    }

    public function testSetAndGetDescription(): void
    {
        $d = new Destination();
        $d->setDescription('La ville de l\'amour.');
        $this->assertEquals('La ville de l\'amour.', $d->getDescription());
    }

    public function testSetAndGetLocalisation(): void
    {
        $d = new Destination();
        $d->setLocalisation('Tunis, Tunisie');
        $this->assertEquals('Tunis, Tunisie', $d->getLocalisation());
    }

    public function testSetAndGetCapaciteMax(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(50);
        $this->assertEquals(50, $d->getCapaciteMax());
    }

    public function testSetAndGetNbParticipants(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(10);
        $d->setNbParticipants(3);
        $this->assertEquals(3, $d->getNbParticipants());
    }

    public function testSetAndGetCurrency(): void
    {
        $d = new Destination();
        $d->setCurrency('TND');
        $this->assertEquals('TND', $d->getCurrency());
    }

    public function testSetAndGetPlugType(): void
    {
        $d = new Destination();
        $d->setPlugType('Type C');
        $this->assertEquals('Type C', $d->getPlugType());
    }

    public function testSetAndGetSurvivalPhrases(): void
    {
        $d = new Destination();
        $d->setSurvivalPhrases('Merci = Shukran');
        $this->assertEquals('Merci = Shukran', $d->getSurvivalPhrases());
    }

    public function testSetAndGetPanoramaUrl(): void
    {
        $d = new Destination();
        $d->setPanoramaUrl('https://example.com/panorama.jpg');
        $this->assertEquals('https://example.com/panorama.jpg', $d->getPanoramaUrl());
    }

    public function testSetAndGetProgramme(): void
    {
        $d = new Destination();
        $d->setProgramme('Jour 1 : Arrivée. Jour 2 : Visite.');
        $this->assertEquals('Jour 1 : Arrivée. Jour 2 : Visite.', $d->getProgramme());
    }

    public function testReminderSentDefaultFalse(): void
    {
        $d = new Destination();
        $this->assertFalse($d->isReminderSent());
    }

    public function testSetReminderSent(): void
    {
        $d = new Destination();
        $d->setReminderSent(true);
        $this->assertTrue($d->isReminderSent());
    }

    // ─── Tests statut automatique ────────────────────────────────────────────

    public function testUpdateStatutAutomaticallyComplet(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(10);
        $d->setNbParticipants(10);
        $this->assertEquals('Complet', $d->getStatut());
    }

    public function testUpdateStatutAutomaticallyDisponible(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(10);
        $d->setNbParticipants(5);
        $this->assertEquals('Disponible', $d->getStatut());
    }

    public function testStatutDisponibleWhenZeroParticipants(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(10);
        $d->setNbParticipants(0);
        $this->assertEquals('Disponible', $d->getStatut());
    }

    public function testStatutCompleWhenParticipantsExceedCapacity(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(5);
        $d->setNbParticipants(8);
        $this->assertEquals('Complet', $d->getStatut());
    }

    public function testStatutChangesWhenCapaciteMaxUpdated(): void
    {
        $d = new Destination();
        $d->setCapaciteMax(10);
        $d->setNbParticipants(10);
        $this->assertEquals('Complet', $d->getStatut());

        // Augmenter la capacité → devient disponible
        $d->setCapaciteMax(20);
        $this->assertEquals('Disponible', $d->getStatut());
    }

    // ─── Tests isExpired ─────────────────────────────────────────────────────

    public function testIsExpiredTrue(): void
    {
        $d = new Destination();
        $d->setDateLancement(new \DateTime('-1 day'));
        $this->assertTrue($d->isExpired());
    }

    public function testIsExpiredFalse(): void
    {
        $d = new Destination();
        $d->setDateLancement(new \DateTime('+1 day'));
        $this->assertFalse($d->isExpired());
    }

    public function testIsExpiredWhenNoDate(): void
    {
        $d = new Destination();
        // Pas de date → non expiré
        $this->assertFalse($d->isExpired());
    }

    // ─── Tests toDisplayUrl ──────────────────────────────────────────────────

    public function testToDisplayUrlLocalPath(): void
    {
        $url = Destination::toDisplayUrl('/uploads/destinations/image.jpg');
        $this->assertEquals('/uploads/destinations/image.jpg', $url);
    }

    public function testToDisplayUrlGoogleDriveUcFormat(): void
    {
        $url = Destination::toDisplayUrl('https://drive.google.com/uc?id=ABC123');
        $this->assertStringContainsString('thumbnail?id=ABC123', $url);
    }

    public function testToDisplayUrlGoogleDriveSlashDFormat(): void
    {
        $url = Destination::toDisplayUrl('https://drive.google.com/file/d/XYZ789/view');
        $this->assertStringContainsString('thumbnail?id=XYZ789', $url);
    }

    public function testToDisplayUrlAlreadyThumbnail(): void
    {
        $url = 'https://drive.google.com/thumbnail?id=ABC123&sz=w1200';
        $this->assertEquals($url, Destination::toDisplayUrl($url));
    }

    // ─── Tests images ────────────────────────────────────────────────────────

    public function testGetFirstImageEmptyWhenNoImages(): void
    {
        $d = new Destination();
        $this->assertEquals('', $d->getFirstImage());
    }

    public function testGetFirstImageFromLegacyField(): void
    {
        $d = new Destination();
        $d->setImages('/uploads/img1.jpg,/uploads/img2.jpg');
        $this->assertEquals('/uploads/img1.jpg', $d->getFirstImage());
    }

    public function testGetImagesListEmpty(): void
    {
        $d = new Destination();
        $this->assertIsArray($d->getImagesList());
        $this->assertEmpty($d->getImagesList());
    }

    public function testGetImagesListFromLegacyField(): void
    {
        $d = new Destination();
        $d->setImages('/uploads/img1.jpg,/uploads/img2.jpg');
        $list = $d->getImagesList();
        $this->assertCount(2, $list);
        $this->assertEquals('/uploads/img1.jpg', $list[0]);
        $this->assertEquals('/uploads/img2.jpg', $list[1]);
    }

    public function testGetImagesCountZero(): void
    {
        $d = new Destination();
        $this->assertEquals(0, $d->getImagesCount());
    }

    // ─── Tests collections ───────────────────────────────────────────────────

    public function testReviewsCollectionInitiallyEmpty(): void
    {
        $d = new Destination();
        $this->assertCount(0, $d->getReviews());
    }

    public function testDestinationImagesCollectionInitiallyEmpty(): void
    {
        $d = new Destination();
        $this->assertCount(0, $d->getDestinationImages());
    }

    public function testIdIsNullByDefault(): void
    {
        $d = new Destination();
        $this->assertNull($d->getId());
    }
}
