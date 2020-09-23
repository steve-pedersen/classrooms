<?php

/**
 * Create the configuration options.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:

                $this->useDataSource($this->getApplication()->dataSourceManager->getDataSource('default'));

                $def = $this->createEntityType('classroom_room_buildings');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('code', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_room_types');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_room_locations');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('number', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('type_id', 'int');
                $def->addProperty('building_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('building_id');
                $def->addIndex('type_id');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_room_configurations');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('model', 'string');
                $def->addProperty('location', 'string');
                $def->addProperty('management_type', 'string');
                $def->addProperty('image_status', 'string');
                $def->addProperty('vintages', 'string');
                $def->addProperty('uniprint', 'string');
                $def->addProperty('uniprint_queue', 'string');
                $def->addProperty('release_station_ip', 'string');
                $def->addProperty('ad_bound', 'bool');
                $def->addProperty('count', 'int');
                $def->addProperty('room_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('room_id');
                $def->save();

                break;
        }
    }
}