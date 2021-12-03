<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

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
        $this->assertEmailCount(1);

        // Assert emailing
        $mailCollector = $client->getProfile()->getCollector('mailer');
        $messageEvents = $mailCollector->getEvents();
        $emails = $messageEvents->getMessages();
        $message = $emails[0];
        $this->assertInstanceOf(TemplatedEmail::class, $message);
        $this->assertSame('Thank you for registration', $message->getSubject());
        $this->assertSame('contact@pokemon.com', $message->getFrom()[0]->getAddress());
        $this->assertSame('sacha@mail.com', $message->getTo()[0]->getAddress());
        $this->assertMatchesRegularExpression(
            '#Activate your account#',
            $message->getTextBody()
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

        // Assert user activated
        $token = $user->getToken()->__toString();
        $client->request('GET', '/email_confirm/?token=' . $token, [], [], ['HTTPS' => 'On']);
        $client->followRedirect();
        $this->assertMatchesRegularExpression(
            '#Thank you, your account is now activated#',
            $client->getResponse()->getContent()
        );

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            'email' => 'sacha@mail.com',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
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
        $this->assertMatchesRegularExpression('#Charmander#', $ash->getResponse()->getContent());
        $misty->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertMatchesRegularExpression('#Bulbasaur#', $misty->getResponse()->getContent());

        $ash->request('GET', '/trainer/list', [], [], ['HTTPS' => 'On']);
        $ash->clickLink('Misty');
        $ash->clickLink('Exchange pokemon');
        $ash->submitForm('Submit');
        $ash->followRedirect();
        $this->assertMatchesRegularExpression(
            '#Your request of pokemons exchange has been submit#',
            $ash->getResponse()->getContent()
        );

        $misty->request('GET', '/exchange', [], [], ['HTTPS' => 'On']);
        $misty->clickLink('Modify');
        $misty->submitForm('Submit');
        $misty->followRedirect();
        $this->assertMatchesRegularExpression(
            '#The modification of pokemons exchange has been submit#',
            $misty->getResponse()->getContent()
        );

        $ash->request('GET', '/exchange', [], [], ['HTTPS' => 'On']);
        $ash->clickLink('Accept');
        $ash->followRedirect();
        $this->assertMatchesRegularExpression('#You have accepted the exchange#', $ash->getResponse()->getContent());

        $ash->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertMatchesRegularExpression('#Bulbasaur#', $ash->getResponse()->getContent());
        $misty->request('GET', '/trainer/pokemons', [], [], ['HTTPS' => 'On']);
        $this->assertMatchesRegularExpression('#Charmander#', $misty->getResponse()->getContent());
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
        $token = $user->getToken()->__toString();
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
        $token = $user->getToken();

        // there is no token if already created
        if ($token) {
            $token = $token->__toString();
            $client->request('GET', '/email_confirm/?token=' . $token, [], [], ['HTTPS' => 'On']);
            $client->request('GET', '/login', [], [], ['HTTPS' => 'On']);
            $client->submitForm('Sign in', [
                'email' => $email,
                'password' => $password,
            ]);
        }

        return $client;
    }
}
