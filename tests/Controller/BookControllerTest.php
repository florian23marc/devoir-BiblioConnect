<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    public function testBookIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/book/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Livres');
    }

    public function testBookShow()
    {
        $client = static::createClient();
        $client->request('GET', '/book/1'); // Assuming book with ID 1 exists

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.card');
    }
}