<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Tutorial extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_tutorials',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
            'locationId' => ['string', 'nativeName' => 'location_id'],
            'deleted' => 'bool',

            'room' => [ '1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => [ 'location_id' => 'id' ] ],
            // 'image' => [ '1:1', 'to' => 'Classrooms_Files_File', 'keyMap' => [ 'image_id' => 'id' ] ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return 'room/tutorial/' . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/tutorial/';
    }

    public function getNoteUrl ()
    {
        return 'tutorial/' . $this->id;
    }
}

