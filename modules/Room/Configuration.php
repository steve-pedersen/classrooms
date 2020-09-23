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
            'roomId' => ['string', 'nativeName' => 'room_id'],
            'deleted' => 'bool',

            'room' => [ '1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => [ 'room_id' => 'id' ] ],

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

