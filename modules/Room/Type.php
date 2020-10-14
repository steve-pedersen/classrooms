<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Type extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_types',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'deleted' => 'bool',

            'locations' => ['1:N', 
                'to' => 'Classrooms_Room_Location', 
                'reverseOf' => 'type', 
                'orderBy' => [ '+modifiedDate', '+createdDate' ]
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/types/';
    }

    public function getNoteUrl ()
    {
        return 'type/' . $this->id;
    }
}
