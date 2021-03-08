<?php

/**
 * Create the configuration options.
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
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
                $def->addProperty('alternate_name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('url', 'string');
                $def->addProperty('type_id', 'int');
                $def->addProperty('building_id', 'int');
                $def->addProperty('capacity', 'string');
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
                $def->addProperty('device_type', 'string');
                $def->addProperty('device_quantity', 'string');
                $def->addProperty('management_type', 'string');
                $def->addProperty('image_status', 'string');
                $def->addProperty('vintages', 'string');
                $def->addProperty('uniprint', 'string');
                $def->addProperty('uniprint_queue', 'string');
                $def->addProperty('release_station_ip', 'string');
                $def->addProperty('printer_model', 'string');
                $def->addProperty('printer_ip', 'string');
                $def->addProperty('printer_server', 'string');
                $def->addProperty('ad_bound', 'bool');
                $def->addProperty('count', 'int');
                $def->addProperty('is_bundle', 'bool');
                $def->addProperty('description', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                // $def->addIndex('location_id');
                $def->save();

                $def = $this->createEntityType('classroom_room_configurations_map');
                $def->addProperty('configuration_id', 'int', ['primaryKey' => true]);
                $def->addProperty('location_id', 'int', ['primaryKey' => true]);
                $def->save();

                $def = $this->createEntityType('classroom_room_configurations_software_licenses_map');
                $def->addProperty('configuration_id', 'int', ['primaryKey' => true]);
                $def->addProperty('license_id', 'int', ['primaryKey' => true]);
                // $def->addProperty('title_id', 'int');
                $def->save();

                $def = $this->createEntityType('classroom_room_tutorials');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('header_image_url', 'string');
                $def->addProperty('location_id', 'int');
                $def->addProperty('image_id', 'int');
                $def->addProperty('youtube_embed_code', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('location_id');
                $def->save();

                $now = new DateTime;
                $this->useDataSource('Classrooms_Room_Building');
                $groupIdMap = $this->insertRecords('classroom_room_buildings',
                    [
                        ['code' => 'BH', 'name' => 'Burk Hall', 'created_date' => $now],
                        ['code' => 'BUS', 'name' => 'Business', 'created_date' => $now],
                        ['code' => 'CA', 'name' => 'Creative Arts', 'created_date' => $now],
                        ['code' => 'DC', 'name' => 'Downtown Campus', 'created_date' => $now],
                        ['code' => 'EP', 'name' => 'Ethnic Studies & Psychology', 'created_date' => $now],
                        ['code' => 'FA', 'name' => 'Fine Arts', 'created_date' => $now],
                        ['code' => 'GYM', 'name' => 'Gymnasium', 'created_date' => $now],
                        ['code' => 'HH', 'name' => 'Hensill Hall', 'created_date' => $now],
                        ['code' => 'HSS', 'name' => 'Health & Social Services', 'created_date' => $now],
                        ['code' => 'HUM', 'name' => 'Humanities', 'created_date' => $now],
                        ['code' => 'SCI', 'name' => 'Science', 'created_date' => $now],
                        ['code' => 'TH', 'name' => 'Thornton Hall', 'created_date' => $now],
                        ['code' => 'T', 'name' => 'Trailers', 'created_date' => $now],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                $this->useDataSource('Classrooms_Room_Type');
                $groupIdMap = $this->insertRecords('classroom_room_types',
                    [
                        ['name' => 'Classroom', 'created_date' => $now],
                        ['name' => 'Lecture Hall', 'created_date' => $now],
                        ['name' => 'Lab', 'created_date' => $now],
                        ['name' => 'Auditorium', 'created_date' => $now],
                        ['name' => 'Meeting Room', 'created_date' => $now],
                        ['name' => 'Study Room', 'created_date' => $now],
                        ['name' => 'Theater', 'created_date' => $now],
                        ['name' => 'Collaborative Space', 'created_date' => $now],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                break;
         
        }
    }
}