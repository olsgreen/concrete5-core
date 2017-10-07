<?php
namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Updater\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170926000000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeQuery("UPDATE btSearch SET postTo_cID = NULL WHERE postTo_cID = ''");
        $this->refreshBlockType('search');
    }

    public function down(Schema $schema)
    {
    }
}
