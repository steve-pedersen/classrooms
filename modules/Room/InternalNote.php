<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_InternalNote extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_internal_notes',
            '__pk' => ['id'],
            
            'id' => 'int',
            'message' => 'string',
            'addedBy' => [ '1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => [ 'added_by_id' => 'id' ] ],
            'location' => [ '1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => [ 'location_id' => 'id' ] ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
        ];
    }

    public function getNotePath ()
    {
        return $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/rooms/' . $this->location->id . '/notes/';
    }

    public function getNoteUrl ()
    {
        return 'note/' . $this->id;
    }
}
