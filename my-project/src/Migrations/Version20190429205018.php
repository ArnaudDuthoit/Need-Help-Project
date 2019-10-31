<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190429205018 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contact ADD created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX UNIQ_DB021E967478AF67 ON messages');
        $this->addSql('DROP INDEX UNIQ_DB021E964632BB48 ON messages');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DB021E967478AF67 ON messages (to_id_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DB021E964632BB48 ON messages (from_id_id)');
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8A76ED395');
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8C18272');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9A76ED395');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contact DROP created_at');
        $this->addSql('DROP INDEX UNIQ_DB021E964632BB48 ON messages');
        $this->addSql('DROP INDEX UNIQ_DB021E967478AF67 ON messages');
        $this->addSql('CREATE INDEX UNIQ_DB021E964632BB48 ON messages (from_id_id)');
        $this->addSql('CREATE INDEX UNIQ_DB021E967478AF67 ON messages (to_id_id)');
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8C18272');
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8A76ED395');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9A76ED395');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
