<?php
/**
 * @copyright Copyright (c) 2018 Code-Source
 */
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename table and recreate needed constraints
 */
class Version20151212051456 extends AbstractMigration
{

    protected array $tablesMapping = array(
        'cdsrc_libraries_translatable_domain_model_abstracttranslatable' => 'cdsrc_libraries_trsl_abstracttranslatable',
        'cdsrc_libraries_translatable_domain_model_abstracttranslation' => 'cdsrc_libraries_trsl_abstracttranslation',
        'cdsrc_libraries_translatable_domain_model_generictranslat_606f0' => 'cdsrc_libraries_trsl_generictranslationfield',
        'cdsrc_libraries_translatable_domain_model_generictranslation' => 'cdsrc_libraries_trsl_generictranslation',
    );

    /**
     * @param Schema $schema
     *
     * @return void
     *
     * @throws DriverException
     * @throws Exception
     */
	public function up(Schema $schema): void
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->renameTablesBasedOnTableMapping($this->tablesMapping);
    }

    /**
     * @param Schema $schema
     * @return void
     *
     * @throws DriverException
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->renameTablesBasedOnTableMapping(array_flip($this->tablesMapping));
    }

    /**
     * Do migration for table mapping
     *
     * @param $tablesMapping
     *
     * @throws DriverException
     * @throws Exception
     */
    protected function renameTablesBasedOnTableMapping($tablesMapping): void
    {

        $foreignKeysConstraints = $this->findExistingForeignKeyConstraintsForTableName();

        // Drop existing foreign keys
        foreach($foreignKeysConstraints as $constraint){
            if(isset($tablesMapping[$constraint['TABLE_NAME']])) {
                $this->addSql('ALTER TABLE ' . $constraint['TABLE_NAME'] . ' DROP FOREIGN KEY ' . $constraint['CONSTRAINT_NAME']);
            }
        }

        // Rename tables
        foreach($tablesMapping as $oldName => $newName){
            $this->addSql('RENAME TABLE ' . $oldName . ' TO ' . $newName);
        }

        // Create new foreign keys
        foreach($foreignKeysConstraints as $constraint){
            if(isset($tablesMapping[$constraint['TABLE_NAME']])){
                $tableName = $tablesMapping[$constraint['TABLE_NAME']];
                $suffix = '';
                if($constraint['UPDATE_RULE'] !== 'RESTRICT'){
                    $suffix .= ' ON UPDATE ' . $constraint['UPDATE_RULE'];
                }
                if($constraint['DELETE_RULE'] !== 'RESTRICT'){
                    $suffix .= ' ON DELETE ' . $constraint['DELETE_RULE'];
                }
                $this->addSql('ALTER TABLE `' . $tableName . '` ADD CONSTRAINT ' . $constraint['CONSTRAINT_NAME'] . ' FOREIGN KEY (`'.$constraint['COLUMN_NAME'].'`) REFERENCES ' . $tablesMapping[$constraint['REFERENCED_TABLE_NAME']] . '('.$constraint['REFERENCED_COLUMN_NAME'].')' . $suffix);
            }
        }
    }

    /**
     * Find existing foreign key
     *
     * @return array
     *
     * @throws Exception
     * @throws DriverException
     */
	protected function findExistingForeignKeyConstraintsForTableName(): array
    {
        return $queryBuilderTemplate = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('information_schema.KEY_COLUMN_USAGE', 'u')
            ->join('u', 'information_schema.REFERENTIAL_CONSTRAINTS', 'r', 'u.CONSTRAINT_NAME = r.CONSTRAINT_NAME')
            ->where("u.TABLE_SCHEMA = '". $this->connection->getDatabase()."'")->execute()->fetchAllAssociative();
    }
}
