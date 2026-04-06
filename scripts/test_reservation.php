<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Entity\Reservation;
use Symfony\Component\DependencyInjection\ContainerBuilder;

$kernel = new App\Kernel($_ENV['APP_ENV'] ?? 'dev', (bool)($_ENV['APP_DEBUG'] ?? false));
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get('doctrine.orm.entity_manager');

// Récupérer les données
$bookRepo = $em->getRepository('App\Entity\Book');
$userRepo = $em->getRepository('App\Entity\User');

// Récupérer le bibliothécaire
$librarian = $userRepo->findOneBy(['email' => 'librarian@example.com']);
if (!$librarian) {
    echo "❌ Bibliothécaire non trouvé\n";
    exit(1);
}

// Récupérer un livre avec du stock
$book = $bookRepo->findOneBy(['title' => 'Dune']);
if (!$book) {
    $book = $bookRepo->findAll()[0] ?? null;
}

if (!$book) {
    echo "❌ Aucun livre disponible\n";
    exit(1);
}

echo "📚 Livre sélectionné: " . $book->getTitle() . " (Stock: " . $book->getStock() . ")\n";
echo "👤 Bibliothécaire: " . $librarian->getFirstName() . " " . $librarian->getLastName() . "\n\n";

// Vérifier les réservations existantes
$existingReservations = $em->getRepository(Reservation::class)->findBy([
    'user' => $librarian,
    'book' => $book,
    'status' => ['pending', 'active']
]);

if ($existingReservations) {
    echo "⚠️  Une réservation existe déjà pour ce livre.\n";
    echo "Réservations existantes:\n";
    foreach ($existingReservations as $res) {
        echo "  - Status: " . $res->getStatus() . "\n";
    }
    exit(0);
}

// Créer une réservation
$reservation = new Reservation();
$reservation->setUser($librarian);
$reservation->setBook($book);
$reservation->setReservedAt(new \DateTime());
$reservation->setStatus('pending');

// Définir les dates
$now = new \DateTime();
$startDate = clone $now;
$startDate->modify('+1 day');
$endDate = clone $startDate;
$endDate->modify('+7 days');
$dueDate = clone $endDate;
$dueDate->modify('+2 days');

$reservation->setStartDate($startDate);
$reservation->setEndDate($endDate);
$reservation->setDueDate($dueDate);

// Persister et flush
$em->persist($reservation);
$em->flush();

echo "✅ Réservation créée avec succès!\n\n";
echo "Détails de la réservation:\n";
echo "  ID: " . $reservation->getId() . "\n";
echo "  Livre: " . $book->getTitle() . "\n";
echo "  Utilisateur: " . $librarian->getFirstName() . " " . $librarian->getLastName() . "\n";
echo "  Status: " . $reservation->getStatus() . "\n";
echo "  Date de début: " . $startDate->format('Y-m-d') . "\n";
echo "  Date de fin: " . $endDate->format('Y-m-d') . "\n";
echo "  Date de retour prévue: " . $dueDate->format('Y-m-d') . "\n";
