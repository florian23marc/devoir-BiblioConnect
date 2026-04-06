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
use App\Entity\Favorite;
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
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $librarian = new User();
        $librarian->setEmail('librarian@example.com');
        $librarian->setFirstName('Bibliothécaire');
        $librarian->setLastName('Bibliothécaire');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->passwordHasher->hashPassword($librarian, 'librarian123'));
        $manager->persist($librarian);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstName('Usager');
        $user->setLastName('Simple');
        $user->setRoles([]);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        // ========== LANGUAGES ==========
        $languages = [];
        $languageNames = ['French', 'English', 'Italian', 'Portuguese', 'Spanish', 'Arabic', 'Russian', 'Chinese'];
        foreach ($languageNames as $name) {
            $language = new Language();
            $language->setName($name);
            $manager->persist($language);
            $languages[$name] = $language;
        }

        // ========== CATEGORIES ==========
        $categories = [];
        $categoryNames = ['Fiction', 'Non-fiction', 'Science Fiction', 'Fantasy', 'Mystery', 'Romance', 'Thriller', 'Biography', 'History', 'Science', 'Self-help', 'Poetry', 'Children', 'Young Adult', 'Comics'];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[$name] = $category;
        }

        // ========== BOOKS ==========
        $booksData = [
            [
                'title' => 'test 1',
                'author' => 'flo',
                'description' => 'des',
                'stock' => 3,
                'image' => 'Crche_Provencale_2-69d10d607f211.JPG',
                'language' => 'French',
                'categories' => ['Fiction']
            ],
            [
                'title' => 'Le Petit Prince',
                'author' => 'Antoine de Saint-Exupéry',
                'description' => 'Un aviateur échoué dans le désert rencontre un petit prince venu d\'une autre planète. À travers leurs échanges, une réflexion poétique sur l\'amitié, l\'amour et le sens de la vie.',
                'stock' => 4,
                'image' => '71IF1ngy57L_AC_UF10001000_QL80_-69d3a9799b1b4.jpg',
                'language' => 'French',
                'categories' => ['Fiction']
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'Dans une société totalitaire où le Grand Frère surveille tout, Winston Smith travaille pour le Ministère de la Vérité et rêve en secret de liberté et de rébellion.',
                'stock' => 2,
                'image' => '33368130_1984-69d3aa1237bfb.jpg',
                'language' => 'English',
                'categories' => ['Fiction']
            ],
            [
                'title' => 'Harry Potter à l\'école des sorciers',
                'author' => 'J.K. Rowling',
                'description' => 'Harry Potter, jeune orphelin vivant chez ses oncle et tante, découvre qu\'il est un sorcier et rejoint l\'école de magie Poudlard où il fera face à de nombreux dangers.',
                'stock' => 3,
                'image' => 'CouvertureItalie2021HP1-69d3aae2641db.webp',
                'language' => 'Italian',
                'categories' => ['Fantasy']
            ],
            [
                'title' => 'Le Seigneur des Anneaux',
                'author' => 'J.R.R. Tolkien',
                'description' => 'Frodon Sacquet, un hobbit, doit traverser la Terre du Milieu pour détruire l\'Anneau unique dans les feux du Mont Destin et sauver le monde du Seigneur des Ténèbres Sauron.',
                'stock' => 3,
                'image' => 'Capturedcran20260406144716-69d3ab76930a1.png',
                'language' => 'Spanish',
                'categories' => ['Fantasy']
            ],
            [
                'title' => 'L\'Étranger',
                'author' => 'Albert Camus',
                'description' => 'Meursault, un homme totalement indifférent au monde qui l\'entoure, commet un meurtre absurde sur une plage algérienne et fait face à la justice et à la condamnation de la société.',
                'stock' => 4,
                'image' => 'Ltranger__Albert_Camus-69d3ac10a1ace.jpg',
                'language' => 'French',
                'categories' => ['Fiction']
            ],
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'description' => 'Un classique de la science-fiction',
                'stock' => 4,
                'image' => '7_9782221255728_1_75-69d3af1f481f9.jpg',
                'language' => 'English',
                'categories' => ['Science Fiction']
            ],
        ];

        $books = [];
        foreach ($booksData as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setAuthor($data['author']);
            $book->setDescription($data['description']);
            $book->setStock($data['stock']);
            $book->setImage($data['image']);
            $book->setLanguage($languages[$data['language']]);
            $book->setCreatedAt(new \DateTime());

            foreach ($data['categories'] as $categoryName) {
                $book->addCategory($categories[$categoryName]);
            }

            $manager->persist($book);
            $books[] = $book;
        }

        // ========== RESERVATIONS ==========
        $reservationsData = [
            [
                'user_id' => 1,
                'book_id' => 0,
                'reserved_at' => '2026-04-06 13:04:02',
                'due_date' => '2026-04-17 15:03:00',
                'start_date' => '2026-04-07 15:03:00',
                'end_date' => '2026-04-14 15:03:00',
                'status' => 'cancelled'
            ],
            [
                'user_id' => 1,
                'book_id' => 3,
                'reserved_at' => '2026-04-06 13:05:55',
                'due_date' => '2026-04-02 15:05:00',
                'start_date' => '2026-04-07 15:05:00',
                'end_date' => '2026-04-06 13:36:29',
                'status' => 'returned'
            ],
            [
                'user_id' => 1,
                'book_id' => 1,
                'reserved_at' => '2026-04-06 13:16:26',
                'due_date' => '2026-04-20 13:16:00',
                'start_date' => '2026-04-06 14:16:00',
                'end_date' => '2026-04-06 13:36:35',
                'status' => 'returned'
            ],
            [
                'user_id' => 2,
                'book_id' => 4,
                'reserved_at' => '2026-04-06 13:39:57',
                'due_date' => '2026-04-20 13:39:00',
                'start_date' => '2026-04-06 13:54:24',
                'end_date' => '2026-04-13 13:39:00',
                'status' => 'active'
            ],
            [
                'user_id' => 2,
                'book_id' => 5,
                'reserved_at' => '2026-04-06 13:58:16',
                'due_date' => '2026-04-20 13:58:00',
                'start_date' => '2026-04-06 13:58:50',
                'end_date' => '2026-04-13 13:58:00',
                'status' => 'active'
            ],
            [
                'user_id' => 3,
                'book_id' => 2,
                'reserved_at' => '2026-04-06 14:05:37',
                'due_date' => '2026-04-20 14:05:00',
                'start_date' => '2026-04-06 14:53:35',
                'end_date' => '2026-04-13 14:05:00',
                'status' => 'active'
            ],
            [
                'user_id' => 2,
                'book_id' => 6,
                'reserved_at' => '2026-04-06 14:56:54',
                'due_date' => '2026-04-16 14:56:54',
                'start_date' => '2026-04-07 14:56:54',
                'end_date' => '2026-04-14 14:56:54',
                'status' => 'pending'
            ]
        ];

        $userReferences = [$admin, $librarian, $user];

        foreach ($reservationsData as $data) {
            $reservation = new Reservation();
            $reservation->setUser($userReferences[$data['user_id'] - 1]);
            $reservation->setBook($books[$data['book_id']]);
            $reservation->setReservedAt(new \DateTime($data['reserved_at']));
            $reservation->setDueDate(new \DateTime($data['due_date']));
            $reservation->setStartDate(new \DateTime($data['start_date']));
            $reservation->setEndDate(new \DateTime($data['end_date']));
            $reservation->setStatus($data['status']);
            $manager->persist($reservation);
        }

        // ========== REVIEWS ==========
        $reviewsData = [
            ['user_id' => 1, 'book_id' => 0, 'rating' => 4, 'comment' => 'dadada', 'created_at' => '2026-04-04 13:08:48'],
            ['user_id' => 1, 'book_id' => 3, 'rating' => 5, 'comment' => 'super livre', 'created_at' => '2026-04-06 13:05:20'],
            ['user_id' => 2, 'book_id' => 1, 'rating' => 5, 'comment' => 'Beau livre', 'created_at' => '2026-04-06 13:40:43'],
            ['user_id' => 2, 'book_id' => 0, 'rating' => 4, 'comment' => 'test', 'created_at' => '2026-04-06 14:02:39'],
            ['user_id' => 3, 'book_id' => 6, 'rating' => 5, 'comment' => 'tres bon livre', 'created_at' => '2026-04-06 14:06:39'],
            ['user_id' => 3, 'book_id' => 3, 'rating' => 3, 'comment' => 'livre moyen', 'created_at' => '2026-04-06 14:13:17']
        ];

        foreach ($reviewsData as $data) {
            $review = new Review();
            $review->setUser($userReferences[$data['user_id'] - 1]);
            $review->setBook($books[$data['book_id']]);
            $review->setRating($data['rating']);
            $review->setComment($data['comment']);
            $review->setCreatedAt(new \DateTime($data['created_at']));
            $manager->persist($review);
        }

        // ========== FAVORITES ==========
        $favoritesData = [
            ['user_id' => 1, 'book_id' => 6],
            ['user_id' => 2, 'book_id' => 6],
            ['user_id' => 3, 'book_id' => 6]
        ];

        foreach ($favoritesData as $data) {
            $favorite = new Favorite();
            $favorite->setUser($userReferences[$data['user_id'] - 1]);
            $favorite->setBook($books[$data['book_id']]);
            $manager->persist($favorite);
        }

        $manager->flush();
    }
}
