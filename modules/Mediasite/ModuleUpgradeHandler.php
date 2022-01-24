<?php

/**
 */
class MediasiteBackup_Mediasite_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:
                $siteSettings->defineProperty('backup-migration-date', 'Migration Date', 'datetime');
                $siteSettings->defineProperty('backup-deletion-date', 'Deletion Date', 'datetime');
                
                $def = $this->createEntityType('mediasite_backup_backups', 'MediasiteBackup_Mediasite_Backup');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('presentation_id', 'string');
                $def->addProperty('presentation_info', 'string');
                $def->addProperty('creation_date', 'datetime');
                $def->addProperty('migration_date', 'datetime');
                $def->addProperty('deletion_date', 'datetime');
                $def->addIndex('presentation_id');
                $def->addIndex('migration_date');
                $def->addIndex('deletion_date');
                $def->save();
                
                break;
        }
    }
}
