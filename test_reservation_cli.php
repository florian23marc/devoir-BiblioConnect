<?php
require_once dirname(__FILE__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    
    $app = new App\Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
    $container = $app->getContainer();
    $em = $container->get('doctrine.orm.entity_manager');
    
    use App\Entity\Reservation;
    
    // Récupérer le bibliothécaire et le livre
    $librarian = $em->getRepository('App:User')->findOneBy(['email' => 'librarian@example.com']);
    $book = $em->getRepository('App:Book')->findOneBy(['title' => 'Dune']);
    
    if (!$librarian || !$book) {
        echo "❌ Erreur: Bibliothécaire ou livre non trouvé\n";
        return;
    }
    
    echo "📚 Livre: " . $book->getTitle() . " (Stock: " . $book->getStock() . ")\n";
    echo "👤 Bibliothécaire: " . $librarian->getFirstName() . " " . $librarian->getLastName() . "\n\n";
    
    // Vérifier les réservations existantes
    $existing = $em->getRepository(Reservation::class)->findBy([
        'user' => $librarian,
        'book' => $book,
        'status' => ['pending', 'active']
    ]);
    
    if ($existing) {
        echo "⚠️  Une réservation existe déjà\n";
        return;
    }
    
    // Créer la réservation
    $res = new Reservation();
    $res->setUser($librarian);
    $res->setBook($book);
    $res->setReservedAt(new \DateTime());
    $res->setStatus('pending');
    
    $start = new \DateTime();
    $start->modify('+1 day');
    $end = clone $start;
    $end->modify('+7 days');
    $due = clone $end;
    $due->modify('+2 days');
    
    $res->setStartDate($start);
    $res->setEndDate($end);
    $res->setDueDate($due);
    
    $em->persist($res);
    $em->flush();
    
    echo "✅ RÉSERVATION CRÉÉE!\n\n";
    echo "Details:\n";
    echo "  ID: " . $res->getId() . "\n";
    echo "  Livre: " . $book->getTitle() . "\n";
    echo "  Utilisateur: " . $librarian->getFirstName() . "\n";
    echo "  Statut: " . $res->getStatus() . "\n";
    echo "  Du: " . $start->format('Y-m-d') . " au " . $end->format('Y-m-d') . "\n";
};
