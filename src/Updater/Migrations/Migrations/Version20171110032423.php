<?php

namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Package;
use Concrete\Core\Updater\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Concrete\Core\Entity\Attribute\Key\EventKey;
use Concrete\Core\Entity\Attribute\Value\EventValue;
use Concrete\Core\Entity\Calendar\Calendar;
use Concrete\Core\Entity\Calendar\CalendarEvent;
use Concrete\Core\Entity\Calendar\CalendarEventOccurrence;
use Concrete\Core\Entity\Calendar\CalendarEventRepetition;
use Concrete\Core\Entity\Calendar\CalendarEventVersion;
use Concrete\Core\Entity\Calendar\CalendarEventVersionOccurrence;
use Concrete\Core\Entity\Calendar\CalendarEventVersionRepetition;
use Concrete\Core\Entity\Calendar\CalendarEventWorkflowProgress;
use Concrete\Core\Entity\Calendar\CalendarPermissionAssignment;
use Concrete\Core\Entity\Calendar\CalendarRelatedEvent;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171110032423 extends AbstractMigration
{
    protected function addEarlyCalendarFunctionality()
    {
        $this->output(t('Installing calendar attribute category table...'));
        $this->refreshEntities([
            EventKey::class
        ]);
    }

    protected function addCalendarFunctionality()
    {
        $this->output(t('Installing calendar entities...'));
        $this->refreshEntities([
            EventValue::class,
            Calendar::class,
            CalendarEvent::class,
            CalendarEventOccurrence::class,
            CalendarEventRepetition::class,
            CalendarEventVersion::class,
            CalendarEventVersionOccurrence::class,
            CalendarEventVersionRepetition::class,
            CalendarEventWorkflowProgress::class,
            CalendarRelatedEvent::class,
            CalendarPermissionAssignment::class,
        ]);
        $this->output(t('Installing calendar XML...'));
        $importer = new ContentImporter();
        $importer->importContentFile(DIR_BASE_CORE . '/config/install/upgrade/calendar.xml');
    }

    protected function backupLegacyCalendar()
    {
        $this->output(t('Backing up legacy calendar...'));
        if (!$this->connection->tableExists('CalendarEventAttributeValues')) {
            $this->connection->Execute('alter table CalendarEventAttributeValues rename _CalendarEventAttributeValues');
        }
        if (!$this->connection->tableExists('CalendarEventOccurrences')) {
            $this->connection->Execute('alter table CalendarEventOccurrences rename _CalendarEventOccurrences');
        }
        if (!$this->connection->tableExists('CalendarEventRepetitions')) {
            $this->connection->Execute('alter table CalendarEventRepetitions rename _CalendarEventRepetitions');
        }
        if (!$this->connection->tableExists('CalendarEventSearchIndexAttributes')) {
            $this->connection->Execute('alter table CalendarEventSearchIndexAttributes rename _CalendarEventSearchIndexAttributes');
        }
        if (!$this->connection->tableExists('CalendarEvents')) {
            $this->connection->Execute('alter table CalendarEvents rename _CalendarEvents');
        }
        if (!$this->connection->tableExists('Calendars')) {
            $this->connection->Execute('alter table Calendars rename _Calendars');
        }
    }

    protected function uninstallLegacyCalendar(\Concrete\Core\Entity\Package $pkg)
    {
        $this->output('Uninstalling legacy calendar package...');
        $this->output('Removing pages...');
        $r = $this->connection->executeQuery('select cID from Pages where pkgID = ?', [$pkg->getPackageID()]);
        while ($row = $r->fetch()) {
            $page = Page::getByID($row['cID']);
            if ($page && !$page->isError()) {
                $page->delete();
            }
        }
        $this->output('Updating attribute categories...');
        $this->connection->executeQuery('delete from AttributeKeyCategories where pkgID = ?', [$pkg->getPackageID()]);
        $this->output('Updating block types...');
        $this->connection->executeQuery('delete from BlockTypes where pkgID = ?', [$pkg->getPackageID()]);
        $this->output(t('Uninstalling calendar package (ID %s)', $pkg->getPackageID()));
        $this->connection->executeQuery('delete from Packages where pkgID = ?', array($pkg->getPackageID()));
    }

    protected function updateAttributeKeys($pkg)
    {
        $this->output(t('Updating attribute keys from legacy to 8.3.'));
    }

    protected function migrateCalendars($pkg)
    {
        $this->output(t('Migrating calendar content into 8.3 calendar.'));
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addEarlyCalendarFunctionality();
        // first, let's see whether the concrete5 calendar is installed.
        $pkg = Package::getByHandle('calendar');
        if ($pkg) {
            $this->connection->Execute('set foreign_key_checks = 0');

            // let's uninstall the package.
            $this->uninstallLegacyCalendar($pkg);

            // Now, let's stash all the data from the legacy calendar add-on in somebackup tables.
            $this->backupLegacyCalendar();

            // now add the calendar functionality
            $this->addCalendarFunctionality();

            // now, take existing calendar attribute keys and turn them into 8.3 keys
            $this->updateAttributeKeys($pkg);

            // now update the calendars and contenet
            $this->migrateCalendars($pkg);

            // Now, let's migrate the data back.
            $this->connection->Execute('set foreign_key_checks = 1');
        } else {
            $this->addCalendarFunctionality();
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
