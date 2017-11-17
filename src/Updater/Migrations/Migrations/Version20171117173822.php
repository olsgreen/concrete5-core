<?php

namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Updater\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171117173822 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$schema->getTable('Pages')->hasColumn('cIsDraft')) {
            $schema->getTable('Pages')->addColumn('cIsDraft', 'boolean', array(
                'notnull' => true, 'default' => 0
            ));
        }
    }

    public function postUp(Schema $schema)
    {
        $db = $this->connection;
        $config = $this->app->make("config");
        $r = $db->executeQuery('select cID from PagePaths where cPath = ?', [$config->get('concrete.paths.drafts')]);
        while ($row = $r->fetch()) {
            $r2 = $db->executeQuery('select cID from Pages where cParentID = ?', [$row['cID']]);
            while ($row2 = $r2->fetch()) {
                $db->update('Pages', ['cIsDraft' => 1], ['cID' => $row2['cID']]);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
