<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Language;
use App\Entity\Category;
use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\Review;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Creating Users
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setFirstName('User');
            $user->setLastName('Test ' . $i);
            $user->setEmail('user' . $i . '@example.com');
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
        }

        // Creating Languages
        $languages = ['English', 'French', 'Spanish'];
        foreach ($languages as $lang) {
            $language = new Language();
            $language->setName($lang);
            $manager->persist($language);
        }

        // Creating Categories
        $categories = ['Fiction', 'Science', 'History'];
        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat);
            $manager->persist($category);
        }

        // Creating Books
        for ($j = 1; $j <= 10; $j++) {
            $book = new Book();
            $book->setTitle('Book Title ' . $j);
            $book->setAuthor('Author ' . $j);
            $book->setDescription('Description for book ' . $j);
            $book->setStock(rand(2, 10));
            $book->setCreatedAt(new \DateTime());
            $manager->persist($book);
        }

        $manager->flush();
    }
}
