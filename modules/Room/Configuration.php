<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Configuration extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_configurations',
            '__pk' => ['id'],
            
            'id' => 'int',
            'model' => 'string',
            'location' => 'string',
            'managementType' => ['string', 'nativeName' => 'management_type'],
            'imageStatus' => ['string', 'nativeName' => 'image_status'],
            'vintages' => 'string',
            'uniprint' => 'string',
            'uniprintQueue' => ['string', 'nativeName' => 'uniprint_queue'],
            'releaseStationIp' => ['string', 'nativeName' => 'release_station_ip'],
            'adBound' => ['bool', 'nativeName' => 'ad_bound'],
            'count' => 'int',
            'locationId' => ['string', 'nativeName' => 'location_id'],
            'deleted' => 'bool',

            'room' => [ '1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => [ 'location_id' => 'id' ] ],
            'softwareLicenses' => ['N:M', 
                'to' => 'Classrooms_Software_License', 
                'via' => 'classroom_room_configurations_software_licenses_map', 
                'toPrefix' => 'license', 
                'fromPrefix' => 'configuration',
                // 'properties' => ['title_id'],
                // 'orderBy' => ['+_map.title_id']
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return 'room/configuration/' . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/configuration/';
    }

    public function getNoteUrl ()
    {
        return 'configuration/' . $this->id;
    }
}

