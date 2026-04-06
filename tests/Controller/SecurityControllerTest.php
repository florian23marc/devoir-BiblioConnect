<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPage()
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginAndAddBook()
    {
        $client = static::createClient();

        // Test login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'librarian@example.com',
            'password' => 'librarian123',
        ]);
        $client->submit($form);

        // Should redirect to librarian dashboard
        $this->assertResponseRedirects('/librarian');
        $client->followRedirect();

        // Now test adding a book
        $crawler = $client->request('GET', '/book/new');
        $this->assertResponseIsSuccessful();

        // Note: Adding a book requires file upload, so this is a basic test
        $this->assertSelectorExists('form');
    }
}