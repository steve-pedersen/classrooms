<?php

/**
 * Create the configuration options.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Notes_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                $this->useDataSource($this->getApplication()->dataSourceManager->getDataSource('default'));

                $def = $this->createEntityType('classroom_notes_entries');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('message', 'string');
                $def->addProperty('old_values', 'string');
                $def->addProperty('new_values', 'string');
                $def->addProperty('path', 'string');
                $def->addProperty('url', 'string');
                $def->addProperty('created_by_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->addIndex('path');
                $def->addIndex('created_date');
                $def->save();

                break;
        }
    }
}