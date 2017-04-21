<?php
namespace Concrete\Core\Updater\Migrations\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170408000000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->refreshBlockType('image');
    }

    public function down(Schema $schema)
    {
    }
}
