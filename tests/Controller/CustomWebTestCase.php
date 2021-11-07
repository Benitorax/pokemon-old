<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomWebTestCase extends WebTestCase
{
    protected EntityManager $entityManager;
    protected function setUp()
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function createUser(string $username, string $email, string $password, int $pokemonId147)
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $client->submitForm('register[save]', [
            'register[username]' => $username,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[email]' => $email,
            'register[pokemonApiId]' => $pokemonId147,
        ]);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $token = $user->getToken()->toString();
        $client->request('GET', '/email_confirm/?token=' . $token);
    }

    public function createUserAndLogIn(string $username, string $email, string $password, int $pokemonId147)
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $client->submitForm('register[save]', [
            'register[username]' => $username,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[email]' => $email,
            'register[pokemonApiId]' => $pokemonId147,
        ]);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $token = $user->getToken()->toString();
        $client->request('GET', '/email_confirm/?token=' . $token);
        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            'email' => $email,
            'password' => $password,
        ]);

        return $client;
    }
}
