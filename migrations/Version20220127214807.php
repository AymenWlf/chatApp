<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127214807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY message_conversation');
        $this->addSql('DROP INDEX last_message_id ON conversation');
        $this->addSql('ALTER TABLE conversation CHANGE last_message_id last_message_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9ECED022B FOREIGN KEY (last_message_id_id) REFERENCES message (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8E26E9ECED022B ON conversation (last_message_id_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY user_message');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY conversation_message');
        $this->addSql('DROP INDEX id_user ON message');
        $this->addSql('DROP INDEX id_conversation ON message');
        $this->addSql('ALTER TABLE message ADD id_conversation_id INT NOT NULL, ADD id_user_id INT NOT NULL, DROP id_conversation, DROP id_user');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE0F2C01E FOREIGN KEY (id_conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F79F37AE5 FOREIGN KEY (id_user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FE0F2C01E ON message (id_conversation_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F79F37AE5 ON message (id_user_id)');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY user_participant');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY conversation_participant');
        $this->addSql('DROP INDEX id_conversation ON participant');
        $this->addSql('DROP INDEX user_id ON participant');
        $this->addSql('ALTER TABLE participant ADD id_user_id INT NOT NULL, ADD id_conversation_id INT NOT NULL, DROP user_id, DROP id_conversation, CHANGE messages_read_at messages_read_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1179F37AE5 FOREIGN KEY (id_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11E0F2C01E FOREIGN KEY (id_conversation_id) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B1179F37AE5 ON participant (id_user_id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11E0F2C01E ON participant (id_conversation_id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9ECED022B');
        $this->addSql('DROP INDEX UNIQ_8A8E26E9ECED022B ON conversation');
        $this->addSql('ALTER TABLE conversation CHANGE last_message_id_id last_message_id INT NOT NULL');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT message_conversation FOREIGN KEY (last_message_id) REFERENCES message (id)');
        $this->addSql('CREATE INDEX last_message_id ON conversation (last_message_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE0F2C01E');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F79F37AE5');
        $this->addSql('DROP INDEX IDX_B6BD307FE0F2C01E ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F79F37AE5 ON message');
        $this->addSql('ALTER TABLE message ADD id_conversation INT NOT NULL, ADD id_user INT NOT NULL, DROP id_conversation_id, DROP id_user_id');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT user_message FOREIGN KEY (id_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT conversation_message FOREIGN KEY (id_conversation) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX id_user ON message (id_user)');
        $this->addSql('CREATE INDEX id_conversation ON message (id_conversation)');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B1179F37AE5');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11E0F2C01E');
        $this->addSql('DROP INDEX IDX_D79F6B1179F37AE5 ON participant');
        $this->addSql('DROP INDEX IDX_D79F6B11E0F2C01E ON participant');
        $this->addSql('ALTER TABLE participant ADD user_id INT NOT NULL, ADD id_conversation INT NOT NULL, DROP id_user_id, DROP id_conversation_id, CHANGE messages_read_at messages_read_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT user_participant FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT conversation_participant FOREIGN KEY (id_conversation) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX id_conversation ON participant (id_conversation)');
        $this->addSql('CREATE INDEX user_id ON participant (user_id)');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
