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
class Classrooms_Room_Building extends Bss_ActiveRecord_BaseWith
{
    use Classrooms_Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_buildings',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'code' => 'string',
            'deleted' => 'bool',

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return 'room/building/' . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/building/';
    }

    public function getNoteUrl ()
    {
        return 'building/' . $this->id;
    }
}
