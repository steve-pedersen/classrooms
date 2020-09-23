<?php

/**
 * Extensible implementation of an account system.
 * 
 * Applications should extend bss:core:authN/accountExtensions with a class
 * extending Bss_AuthN_AccountExtension.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Location extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_locations',
            '__pk' => ['id'],
            
            'id' => 'int',
            'number' => 'string',
            'description' => 'string',
            'url' => 'string',
            'deleted' => 'bool',

            'type' => [ '1:1', 'to' => 'Classrooms_Room_Type', 'keyMap' => [ 'type_id' => 'id' ] ],
            'building' => [ '1:1', 'to' => 'Classrooms_Room_Building', 'keyMap' => [ 'building_id' => 'id' ] ],
            
            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return $this->building->getNotePath() . $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return '/room/';
    }

    public function getNoteUrl ()
    {
        return '/room/' . $this->id;
    }
}
