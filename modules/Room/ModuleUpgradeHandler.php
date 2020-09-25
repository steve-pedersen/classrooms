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
                $def->addProperty('url', 'string');
                $def->addProperty('type_id', 'int');
                $def->addProperty('building_id', 'int');
                $def->addProperty('capacity', 'int');
                $def->addProperty('facets', 'string');
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
                $def->addProperty('location_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('location_id');
                $def->save();

                $def = $this->createEntityType('classroom_room_tutorials');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('location_id', 'int');
                $def->addProperty('image_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('location_id');
                $def->save();


                $this->useDataSource('Classrooms_Room_Building');
                $groupIdMap = $this->insertRecords('classroom_room_buildings',
                    [
                        ['code' => 'BH', 'name' => 'Burk Hall'],
                        ['code' => 'BUS', 'name' => 'Business'],
                        ['code' => 'CA', 'name' => 'Creative Arts'],
                        ['code' => 'DC', 'name' => 'Downtown Campus'],
                        ['code' => 'EP', 'name' => 'Ethnic Studies & Psychology'],
                        ['code' => 'FA', 'name' => 'Fine Arts'],
                        ['code' => 'GYM', 'name' => 'Gymnasium'],
                        ['code' => 'HH', 'name' => 'Hensill Hall'],
                        ['code' => 'HSS', 'name' => 'Health & Social Services'],
                        ['code' => 'HUM', 'name' => 'Humanities'],
                        ['code' => 'SCI', 'name' => 'Science'],
                        ['code' => 'TH', 'name' => 'Thornton Hall'],
                        ['code' => 'T', 'name' => 'Trailers'],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                $this->useDataSource('Classrooms_Room_Type');
                $groupIdMap = $this->insertRecords('classroom_room_types',
                    [
                        ['name' => 'Classroom'],
                        ['name' => 'Lecture Hall'],
                        ['name' => 'Lab'],
                        ['name' => 'Auditorium'],
                        ['name' => 'Meeting Room'],
                        ['name' => 'Study Room'],
                        ['name' => 'Theater'],
                        ['name' => 'Collaborative Space'],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                break;
        }
    }
}