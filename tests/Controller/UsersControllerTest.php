<?php

namespace App\Tests\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UsersControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $userRepository;
    private string $path = '/users/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->manager->getRepository(Users::class);

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[prenom]' => 'Testing',
            'user[nom]' => 'Testing',
            'user[email]' => 'Testing',
            'user[num]' => 'Testing',
            'user[role]' => 'Testing',
            'user[mdp]' => 'Testing',
            'user[tentative]' => 'Testing',
            'user[validation]' => 'Testing',
            'user[blockUntil]' => 'Testing',
            'user[blockLevel]' => 'Testing',
            'user[resetCode]' => 'Testing',
            'user[resetExpiration]' => 'Testing',
            'user[fingerId]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->userRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Users();
        $fixture->setPrenom('My Title');
        $fixture->setNom('My Title');
        $fixture->setEmail('My Title');
        $fixture->setNum('My Title');
        $fixture->setRole('My Title');
        $fixture->setMdp('My Title');
        $fixture->setTentative('My Title');
        $fixture->setValidation('My Title');
        $fixture->setBlockUntil('My Title');
        $fixture->setBlockLevel('My Title');
        $fixture->setResetCode('My Title');
        $fixture->setResetExpiration('My Title');
        $fixture->setFingerId('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Users();
        $fixture->setPrenom('Value');
        $fixture->setNom('Value');
        $fixture->setEmail('Value');
        $fixture->setNum('Value');
        $fixture->setRole('Value');
        $fixture->setMdp('Value');
        $fixture->setTentative('Value');
        $fixture->setValidation('Value');
        $fixture->setBlockUntil('Value');
        $fixture->setBlockLevel('Value');
        $fixture->setResetCode('Value');
        $fixture->setResetExpiration('Value');
        $fixture->setFingerId('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[prenom]' => 'Something New',
            'user[nom]' => 'Something New',
            'user[email]' => 'Something New',
            'user[num]' => 'Something New',
            'user[role]' => 'Something New',
            'user[mdp]' => 'Something New',
            'user[tentative]' => 'Something New',
            'user[validation]' => 'Something New',
            'user[blockUntil]' => 'Something New',
            'user[blockLevel]' => 'Something New',
            'user[resetCode]' => 'Something New',
            'user[resetExpiration]' => 'Something New',
            'user[fingerId]' => 'Something New',
        ]);

        self::assertResponseRedirects('/users/');

        $fixture = $this->userRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getPrenom());
        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getNum());
        self::assertSame('Something New', $fixture[0]->getRole());
        self::assertSame('Something New', $fixture[0]->getMdp());
        self::assertSame('Something New', $fixture[0]->getTentative());
        self::assertSame('Something New', $fixture[0]->getValidation());
        self::assertSame('Something New', $fixture[0]->getBlockUntil());
        self::assertSame('Something New', $fixture[0]->getBlockLevel());
        self::assertSame('Something New', $fixture[0]->getResetCode());
        self::assertSame('Something New', $fixture[0]->getResetExpiration());
        self::assertSame('Something New', $fixture[0]->getFingerId());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Users();
        $fixture->setPrenom('Value');
        $fixture->setNom('Value');
        $fixture->setEmail('Value');
        $fixture->setNum('Value');
        $fixture->setRole('Value');
        $fixture->setMdp('Value');
        $fixture->setTentative('Value');
        $fixture->setValidation('Value');
        $fixture->setBlockUntil('Value');
        $fixture->setBlockLevel('Value');
        $fixture->setResetCode('Value');
        $fixture->setResetExpiration('Value');
        $fixture->setFingerId('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/users/');
        self::assertSame(0, $this->userRepository->count([]));
    }
}
