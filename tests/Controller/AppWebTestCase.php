<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Panther\PantherTestCase;

class AppWebTestCase extends PantherTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::createClient();
        self::initDatabase();
        self::purgeDatabase();
        // self::loadFixtures();
        self::ensureKernelShutdown();
    }

    /**
     * Create database schema.
     */
    private static function initDatabase(): void
    {
        $entityManager = self::getEntityManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

    /**
     * Load fixtures.
     */
    private static function loadFixtures(): void
    {
        // self::getService(AppFixtures::class)->load(self::getEntityManager());
    }

    /**
     * Return a service from container.
     *
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    private static function getService($id)
    {
        /** @var T */
        return static::getContainer()->get((string) $id);
    }

    /**
     * Return an entity manager.
     */
    public static function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface */
        $entityManager = self::getService(EntityManagerInterface::class);

        return $entityManager;
    }

    /**
     * Purge database.
     */
    private static function purgeDatabase(): void
    {
        $purger = new ORMPurger(self::getEntityManager());
        $purger->purge();
    }

    /**
     * Return an User object from given username.
     */
    public static function getUser(string $username): User
    {
        /** @var User */
        return self::getService(UserRepository::class)->findOneBy(['username' => $username]);
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
        $user = self::getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
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
        $user = self::getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
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
