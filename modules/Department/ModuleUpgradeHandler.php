<?php

/**
 * Create the departments.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Department_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:

                $this->useDataSource($this->getApplication()->dataSourceManager->getDataSource('default'));

                $def = $this->createEntityType('classroom_department_departments');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('code', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_department_users');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('first_name', 'string');
                $def->addProperty('last_name', 'string');
                $def->addProperty('email_address', 'string');
                $def->addProperty('sf_state_id', 'string');
                $def->addProperty('position', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_department_users_map');
                $def->addProperty('department_id', 'int', ['primaryKey' => true]);
                $def->addProperty('user_id', 'int', ['primaryKey' => true]);
                $def->save();

                break;
        }
    }
}