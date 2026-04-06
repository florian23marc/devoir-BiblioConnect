<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'create-admin')]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates default users for the application.')
            ->setHelp('This command creates an administrator, a librarian and a regular user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $defaultUsers = [
            [
                'email' => 'admin@example.com',
                'firstName' => 'Admin',
                'lastName' => 'Admin',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'admin123',
                'label' => 'Administrateur',
            ],
            [
                'email' => 'librarian@example.com',
                'firstName' => 'Bibliothécaire',
                'lastName' => 'Bibliothécaire',
                'roles' => ['ROLE_LIBRARIAN'],
                'password' => 'librarian123',
                'label' => 'Bibliothécaire',
            ],
            [
                'email' => 'user@example.com',
                'firstName' => 'Usager',
                'lastName' => 'Simple',
                'roles' => [],
                'password' => 'user123',
                'label' => 'Utilisateur',
            ],
        ];

        foreach ($defaultUsers as $defaultUser) {
            $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $defaultUser['email']]);
            if ($existing) {
                $output->writeln(sprintf('%s existant : %s', $defaultUser['label'], $defaultUser['email']));
                continue;
            }

            $user = new User();
            $user->setEmail($defaultUser['email']);
            $user->setFirstName($defaultUser['firstName']);
            $user->setLastName($defaultUser['lastName']);
            $user->setRoles($defaultUser['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $defaultUser['password']));

            $this->entityManager->persist($user);
            $output->writeln(sprintf('%s créé : %s / %s', $defaultUser['label'], $defaultUser['email'], $defaultUser['password']));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}