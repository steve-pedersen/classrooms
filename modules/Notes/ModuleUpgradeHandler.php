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
                $def = $this->createEntityType('classroom_notes_entries');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('message', 'string');
                $def->addProperty('path', 'string');
                $def->addProperty('url', 'string');
                $def->addProperty('created_by', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex(['deleted', 'path', 'created_date']);
                $def->addIndex(['deleted', 'created_date']);
                $def->save();

                break;
        }
    }
}