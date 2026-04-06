<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-initial-data',
    description: 'Load initial categories and languages',
)]
class LoadInitialDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Add categories
        $categories = [
            'Fiction',
            'Non-fiction',
            'Science Fiction',
            'Fantasy',
            'Mystery',
            'Romance',
            'Thriller',
            'Biography',
            'History',
            'Science',
            'Self-help',
            'Poetry',
            'Children',
            'Young Adult',
            'Comics',
        ];

        foreach ($categories as $categoryName) {
            $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
            if (!$existing) {
                $category = new Category();
                $category->setName($categoryName);
                $this->entityManager->persist($category);
                $io->text("Created category: $categoryName");
            }
        }

        // Add languages
        $languages = [
            'French',
            'English',
            'Italian',
            'Portuguese',
            'Spanish',
            'Arabic',
            'Russian',
            'Chinese',
        ];

        foreach ($languages as $languageName) {
            $existing = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $languageName]);
            if (!$existing) {
                $language = new Language();
                $language->setName($languageName);
                $this->entityManager->persist($language);
                $io->text("Created language: $languageName");
            }
        }

        $this->entityManager->flush();
        $io->success('Initial data loaded successfully!');

        return Command::SUCCESS;
    }
}
