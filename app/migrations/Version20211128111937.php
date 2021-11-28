<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211128111937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('delete from book');
        $this->addSql('delete from author');

        $authorName = 'Лев Толстой';
        for ($authorId = 1; $authorId <= 10000; $authorId ++ ) {
            $this->addSql("
                insert into author (id, name)
                values(:authorId, :name)
            ", ['authorId' => $authorId, 'name' => $authorName . $authorId]);

            $book = 'Война и мир';
            $this->addSql("
                insert into book (name, author_id)
                values(:book, :authorId)        
            ", ['book' => $book . '|' . $authorId, 'authorId' => $authorId]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('delete from book');
        $this->addSql('delete from author');
        $this->addSql('ALTER TABLE book AUTO_INCREMENT =1;');
        $this->addSql('ALTER TABLE author AUTO_INCREMENT =1;');
    }
}
