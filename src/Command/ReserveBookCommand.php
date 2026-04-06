<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reserve-book',
    description: 'Reserve a book for a user',
)]
class ReserveBookCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        
        // Get the librarian
        $librarian = $em->getRepository(User::class)->findOneBy(['email' => 'librarian@example.com']);
        if (!$librarian) {
            $output->writeln('<error>Librarian not found</error>');
            return Command::FAILURE;
        }

        // Get a book
        $book = $em->getRepository(Book::class)->findOneBy(['title' => 'Dune']);
        if (!$book) {
            $output->writeln('<error>Book not found</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>📚 Book:</info> ' . $book->getTitle() . ' (Stock: ' . $book->getStock() . ')');
        $output->writeln('<info>👤 Librarian:</info> ' . $librarian->getFirstName() . ' ' . $librarian->getLastName());
        $output->writeln('');

        // Check existing reservations
        $existing = $em->getRepository(Reservation::class)->findBy([
            'user' => $librarian,
            'book' => $book,
            'status' => ['pending', 'active']
        ]);

        if ($existing) {
            $output->writeln('<warning>⚠️  A reservation already exists for this book</warning>');
            return Command::SUCCESS;
        }

        // Create reservation
        $reservation = new Reservation();
        $reservation->setUser($librarian);
        $reservation->setBook($book);
        $reservation->setReservedAt(new \DateTime());
        $reservation->setStatus('pending');

        $start = new \DateTime();
        $start->modify('+1 day');
        $end = clone $start;
        $end->modify('+7 days');
        $due = clone $end;
        $due->modify('+2 days');

        $reservation->setStartDate($start);
        $reservation->setEndDate($end);
        $reservation->setDueDate($due);

        $em->persist($reservation);
        $em->flush();

        $output->writeln('<fg=green>✅ RESERVATION CREATED!</>');
        $output->writeln('');
        $output->writeln('<info>Details:</info>');
        $output->writeln('  ID: ' . $reservation->getId());
        $output->writeln('  Book: ' . $book->getTitle());
        $output->writeln('  User: ' . $librarian->getFirstName());
        $output->writeln('  Status: ' . $reservation->getStatus());
        $output->writeln('  From: ' . $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'));

        return Command::SUCCESS;
    }
}
