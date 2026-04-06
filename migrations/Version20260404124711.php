<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404124711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add initial categories and languages';
    }

    public function up(Schema $schema): void
    {
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

        foreach ($categories as $category) {
            $this->addSql("INSERT INTO category (name, created_at) VALUES (?, NOW()) ON CONFLICT DO NOTHING", [$category]);
        }

        // Add languages
        $languages = [
            'French' => 'fr',
            'English' => 'en',
            'Italian' => 'it',
            'Portuguese' => 'pt',
            'Spanish' => 'es',
            'Arabic' => 'ar',
            'Russian' => 'ru',
            'Chinese' => 'zh',
        ];

        foreach ($languages as $name => $code) {
            $this->addSql("INSERT INTO language (name, code, created_at) VALUES (?, ?, NOW()) ON CONFLICT DO NOTHING", [$name, $code]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
