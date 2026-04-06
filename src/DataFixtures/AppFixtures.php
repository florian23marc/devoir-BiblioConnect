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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // ========== USERS ==========
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $librarian = new User();
        $librarian->setEmail('librarian@example.com');
        $librarian->setFirstName('Librarian');
        $librarian->setLastName('User');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->passwordHasher->hashPassword($librarian, 'librarian123'));
        $manager->persist($librarian);

        $user1 = new User();
        $user1->setEmail('user@example.com');
        $user1->setFirstName('John');
        $user1->setLastName('Doe');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'user123'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('marie@example.com');
        $user2->setFirstName('Marie');
        $user2->setLastName('Smith');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'user123'));
        $manager->persist($user2);

        // ========== LANGUAGES ==========
        $english = new Language();
        $english->setName('English');
        $manager->persist($english);

        $french = new Language();
        $french->setName('French');
        $manager->persist($french);

        $spanish = new Language();
        $spanish->setName('Spanish');
        $manager->persist($spanish);

        // ========== CATEGORIES ==========
        $fiction = new Category();
        $fiction->setName('Fiction');
        $manager->persist($fiction);

        $sciFi = new Category();
        $sciFi->setName('Science-Fiction');
        $manager->persist($sciFi);

        $fantasy = new Category();
        $fantasy->setName('Fantasy');
        $manager->persist($fantasy);

        $mystery = new Category();
        $mystery->setName('Mystère');
        $manager->persist($mystery);

        $romance = new Category();
        $romance->setName('Romance');
        $manager->persist($romance);

        // ========== BOOKS ==========
        $book1 = new Book();
        $book1->setTitle('The Great Gatsby');
        $book1->setAuthor('F. Scott Fitzgerald');
        $book1->setDescription('A classic American novel about wealth and love');
        $book1->setStock(5);
        $book1->setLanguage($english);
        $book1->setCreatedAt(new \DateTime());
        $book1->addCategory($fiction);
        $manager->persist($book1);

        $book2 = new Book();
        $book2->setTitle('1984');
        $book2->setAuthor('George Orwell');
        $book2->setDescription('A dystopian novel about totalitarianism');
        $book2->setStock(3);
        $book2->setLanguage($english);
        $book2->setCreatedAt(new \DateTime());
        $book2->addCategory($fiction);
        $manager->persist($book2);

        $book3 = new Book();
        $book3->setTitle('Le Seigneur des Anneaux');
        $book3->setAuthor('J.R.R. Tolkien');
        $book3->setDescription('Une épopée fantastique épique');
        $book3->setStock(4);
        $book3->setLanguage($french);
        $book3->setCreatedAt(new \DateTime());
        $book3->addCategory($fantasy);
        $manager->persist($book3);

        $book4 = new Book();
        $book4->setTitle('Dune');
        $book4->setAuthor('Frank Herbert');
        $book4->setDescription('An epic science fiction novel');
        $book4->setStock(2);
        $book4->setLanguage($english);
        $book4->setCreatedAt(new \DateTime());
        $book4->addCategory($sciFi);
        $manager->persist($book4);

        $book5 = new Book();
        $book5->setTitle('Le Petit Prince');
        $book5->setAuthor('Antoine de Saint-Exupéry');
        $book5->setDescription('Un conte poétique et philosophique');
        $book5->setStock(8);
        $book5->setLanguage($french);
        $book5->setCreatedAt(new \DateTime());
        $book5->addCategory($fiction);
        $manager->persist($book5);

        // ========== RESERVATIONS ==========
        $reservation1 = new Reservation();
        $reservation1->setUser($user1);
        $reservation1->setBook($book1);
        $reservation1->setReservedAt(new \DateTime());
        $reservation1->setStatus('pending');
        $manager->persist($reservation1);

        $reservation2 = new Reservation();
        $reservation2->setUser($user2);
        $reservation2->setBook($book3);
        $reservation2->setReservedAt(new \DateTime('-5 days'));
        $reservation2->setStatus('active');
        $reservation2->setStartDate(new \DateTime('-4 days'));
        $reservation2->setDueDate(new \DateTime('+10 days'));
        $manager->persist($reservation2);

        $reservation3 = new Reservation();
        $reservation3->setUser($librarian);
        $reservation3->setBook($book5);
        $reservation3->setReservedAt(new \DateTime('-20 days'));
        $reservation3->setStatus('returned');
        $reservation3->setStartDate(new \DateTime('-20 days'));
        $reservation3->setEndDate(new \DateTime('-2 days'));
        $manager->persist($reservation3);

        // ========== REVIEWS ==========
        $review1 = new Review();
        $review1->setUser($user1);
        $review1->setBook($book1);
        $review1->setRating(5);
        $review1->setComment('Amazing book! Highly recommended.');
        $review1->setCreatedAt(new \DateTime('-10 days'));
        $manager->persist($review1);

        $review2 = new Review();
        $review2->setUser($user2);
        $review2->setBook($book3);
        $review2->setRating(4);
        $review2->setComment('A classic tale with wonderful descriptions.');
        $review2->setCreatedAt(new \DateTime('-5 days'));
        $manager->persist($review2);

        $review3 = new Review();
        $review3->setUser($librarian);
        $review3->setBook($book5);
        $review3->setRating(5);
        $review3->setComment('A beautiful and touching story for all ages.');
        $review3->setCreatedAt(new \DateTime('-3 days'));
        $manager->persist($review3);

        $manager->flush();
    }
}
