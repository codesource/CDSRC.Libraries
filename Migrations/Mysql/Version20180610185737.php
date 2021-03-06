<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180610185737 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('ALTER TABLE cdsrc_libraries_trsl_abstracttranslation DROP FOREIGN KEY FK_1E4E69EAF6DF2DC1');
        $this->addSql('ALTER TABLE cdsrc_libraries_trsl_abstracttranslation ADD CONSTRAINT FK_1E4E69EAF6DF2DC1 FOREIGN KEY (i18nparent) REFERENCES cdsrc_libraries_trsl_abstracttranslatable (persistence_object_identifier) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cdsrc_libraries_trsl_abstracttranslation DROP FOREIGN KEY FK_1E4E69EAF6DF2DC1');
        $this->addSql('ALTER TABLE cdsrc_libraries_trsl_abstracttranslation ADD CONSTRAINT FK_1E4E69EAF6DF2DC1 FOREIGN KEY (i18nparent) REFERENCES cdsrc_libraries_trsl_abstracttranslatable (persistence_object_identifier)');
    }
}