<?php

class Classrooms_Files_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:

                $def = $this->createEntityType('classroom_files', $this->getDataSource('Classrooms_Files_File'));
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('remote_name', 'string');
                $def->addProperty('local_name', 'string');
                $def->addProperty('content_type', 'string');
                $def->addProperty('content_length', 'int');
                $def->addProperty('hash', 'string');
                $def->addProperty('temporary', 'bool');
                $def->addProperty('title', 'string');
                $def->addProperty('uploaded_date', 'datetime');
                $def->addProperty('uploaded_by_id', 'int');
                $def->addProperty('location_id', 'int');
                $def->addForeignKey('bss_authn_accounts', array('uploaded_by_id' => 'id'));
                $def->save();
                
                break;

            case 2:

                $def = $this->alterEntityType('classroom_files', $this->getDataSource('Classrooms_Files_File'));
                $def->addProperty('tutorial_id', 'int');
                $def->save();

                break;
        }
    }
}