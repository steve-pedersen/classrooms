<?php

/**
 */
class Classrooms_Communication_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        switch ($fromVersion)
        {
            case 0:

                $def = $this->createEntityType('classroom_communication_communications', 'Classrooms_Communication_Communication');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('access', 'string');
                $def->addProperty('creation_date', 'datetime');
                $def->addProperty('room_master_template', 'string');
                $def->addProperty('lab_room', 'string');
                $def->addProperty('nonlab_room', 'string');
                $def->addProperty('unconfigured_room', 'string');
                $def->save();

                $def = $this->createEntityType('classroom_communication_events', 'Classrooms_Communication_Event');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('communication_id', 'int');
                $def->addProperty('term_year', 'string');
                $def->addProperty('creation_date', 'datetime');
                $def->addProperty('send_date', 'datetime');
                $def->addProperty('sent', 'bool');
                $def->addIndex('communication_id');
                $def->save();

                $def = $this->createEntityType('classroom_communication_logs', 'Classrooms_Communication_Log');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('communication_id', 'int');
                $def->addProperty('event_id', 'int');
                $def->addProperty('faculty_id', 'string');
                $def->addProperty('creation_date', 'datetime');
                $def->addProperty('email_address', 'string');
                $def->addIndex('faculty_id');
                $def->addIndex('event_id');
                $def->addIndex('communication_id');
                $def->save();

                $siteSettings->defineProperty('communications-title', 'Email title text', 'text');

                break;

            case 1:
                $def = $this->alterEntityType('classroom_communication_communications', 'Classrooms_Communication_Communication');
                $def->addProperty('no_room', 'string');
                $def->save();
                break;

            case 2:
                $def = $this->alterEntityType('classroom_communication_communications', 'Classrooms_Communication_Communication');
                $def->addProperty('type_id', 'int');
                $def->save();
                
                $def = $this->createEntityType('classroom_communication_types', 'Classrooms_Communication_Type');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('deleted', 'bool');
                $def->save();
                break;   

            case 3:
                $def = $this->createEntityType('classroom_communication_type_room_type_map', 'Classrooms_Communication_Type');
                $def->addProperty('communication_type_id', 'int', ['primaryKey' => true]);
                $def->addProperty('room_type_id', 'int', ['primaryKey' => true]);
                $def->save();
                break;    

            case 4:
                $def = $this->alterEntityType('classroom_communication_types', 'Classrooms_Communication_Type');
                $def->addProperty('include_courses_without_rooms', 'bool');
                $def->save();
                break;   

            case 5:
                $def = $this->alterEntityType('classroom_communication_types', 'Classrooms_Communication_Type');
                $def->addProperty('include_unconfigured_rooms', 'bool');
                $def->save();
                break;             
        }
    }
}