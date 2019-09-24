<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Controller\CustomWebTestCase;

class AppControllerTest extends CustomWebTestCase
{
    public function testRedirectIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Please sign in');
    }

    public function testCreateAndActivateUser() {
        $client = static::createClient();
        $client->request('GET', '/register');

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
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'sacha@mail.com']);
        $this->assertSame(false, $user->getIsActivated());
        
        // Assert user not activated
        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            'email' => 'sacha@mail.com',
            'password' => '123456',
        ]);
        $client->followRedirect();
        $this->assertContains('You need to confirm your email address', $client->getResponse()->getContent());

        // Assert user activated
        $token = $user->getToken()->toString();
        $client->request('GET', '/email_confirm/?token='.$token);
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
        $client->request('GET', $url);
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
}
