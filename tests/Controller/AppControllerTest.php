<?php

namespace App\Tests\Controller;

use App\Entity\User;

class AppControllerTest extends AppWebTestCase
{
    public function testRedirectIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/', [], [], ['HTTPS' => 'On']);
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertSelectorTextContains('h3', 'Please sign in');
    }

    public function testCreateAndActivateUser()
    {
        $client = static::createClient();
        $client->request('GET', '/register', [], [], ['HTTPS' => 'On']);
        $client->submitForm('register[save]', [
            'register[username]' => 'Sacha',
            'register[password][first]' => '123456',
            'register[password][second]' => '123456',
            'register[email]' => 'sacha@mail.com',
            'register[pokemonApiId]' => 4,
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());

        // Assert emailing
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Thank you for registration', $message->getSubject());
        $this->assertSame('contact@pokemon.com', key($message->getFrom()));
        $this->assertSame('sacha@mail.com', key($message->getTo()));
        $this->assertContains(
            'Activate your account',
            $message->getBody()
        );

        // Assert new user
        $user = self::getEntityManager()->getRepository(User::class)->findOneBy(['email' => 'sacha@mail.com']);
        $this->assertSame(false, $user->getIsActivated());

        // Assert user inactivated
        $client->request('GET', '/login', [], [], ['HTTPS' => 'On']);
        $client->submitForm('Sign in', [
            'email' => 'sacha@mail.com',
            'password' => '123456',
        ]);
        $client->followRedirect();
        $this->assertContains('You need to confirm your email address', $client->getResponse()->getContent());

        // Assert user activated
        $token = $user->getToken()->toString();
        $client->request('GET', '/email_confirm/?token=' . $token, [], [], ['HTTPS' => 'On']);
        $client->followRedirect();
        $this->assertContains('Thank you, your account is now activated', $client->getResponse()->getContent());

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            'email' => 'sacha@mail.com',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertContains('Nice to see you, Sacha!', $client->getResponse()->getContent());
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = $this->createUserAndLogIn('Sacha', 'sacha@mail.com', '123456', 7);
        $client->followRedirects();
        $client->request('GET', $url, [], [], ['HTTPS' => 'On']);
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        yield ['/'];
        yield ['/index'];
        yield ['/account'];
        yield ['/account/password'];
        yield ['/account/delete'];
        yield ['/adventure'];
        yield ['/contact'];
        yield ['/city'];
        yield ['/email_confirm/'];
        yield ['/password/forgotten/'];
        yield ['/tournament'];
        yield ['/trainer'];
        yield ['/trainer/pokemons'];
        yield ['/trainer/list'];
        yield ['/exchange'];

        // ...
    }

    public function testPokemonExchangeBetween2Trainers()
    {
        $misty = $this->createUserAndLogIn('Misty', 'misty@mail.com', '123456', 1);
        $ash = $this->createUserAndLogIn('Ash', 'ash@mail.com', '123456', 4);

        $ash->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertContains('Charmander', $ash->getResponse()->getContent());
        $misty->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertContains('Bulbasaur', $misty->getResponse()->getContent());

        $ash->request('GET', '/trainer/list', [], [], ['HTTPS' => 'On']);
        $ash->clickLink('Misty');
        $ash->clickLink('Do you want to exchange pokemons with this trainer?');
        $ash->submitForm('Submit');
        $ash->followRedirect();
        $this->assertContains('Your request of pokemons exchange has been submit', $ash->getResponse()->getContent());

        $misty->request('GET', '/exchange', [], [], ['HTTPS' => 'On']);
        $misty->clickLink('Modify');
        $misty->submitForm('Submit');
        $misty->followRedirect();
        $this->assertContains(
            'The modification of pokemons exchange has been submit',
            $misty->getResponse()->getContent()
        );

        $ash->request('GET', '/exchange', [], [], ['HTTPS' => 'On']);
        $ash->clickLink('Accept');
        $ash->followRedirect();
        $this->assertContains('You have accepted the exchange', $ash->getResponse()->getContent());

        $ash->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertContains('Bulbasaur', $ash->getResponse()->getContent());
        $misty->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertContains('Charmander', $misty->getResponse()->getContent());
    }

    public function createUser(string $username, string $email, string $password, int $pokemonId147)
    {
        $client = static::createClient();
        $client->request('GET', '/register', [], [], ['HTTPS' => 'On']);
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
        $client->request('GET', '/register', [], [], ['HTTPS' => 'On']);
        $client->submitForm('register[save]', [
            'register[username]' => $username,
            'register[password][first]' => $password,
            'register[password][second]' => $password,
            'register[email]' => $email,
            'register[pokemonApiId]' => $pokemonId147,
        ]);
        $user = self::getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
        $token = $user->getToken()->toString();
        $client->request('GET', '/email_confirm/?token=' . $token, [], [], ['HTTPS' => 'On']);
        $client->request('GET', '/login', [], [], ['HTTPS' => 'On']);
        $client->submitForm('Sign in', [
            'email' => $email,
            'password' => $password,
        ]);

        return $client;
    }
}
