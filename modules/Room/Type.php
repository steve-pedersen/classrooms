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
            'isLab' => ['bool', 'nativeName' => 'is_lab'],
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

    public static function GetAllLabTypes ()
    {
        return ['Teaching Lab', 'Self Inst Comp Lab', 'Lab'];
    }

    public function getLocations ($includeDeleted = false)
    {
        if (!$includeDeleted)
        {
            $locations = [];
            foreach ($this->_fetch('locations') as $room)
            {
                if (!$room->deleted)
                {
                    $locations[$room->id] = $room;
                }
            }
        }
        else
        {
            $locations = $this->_fetch('locations');
        }

        return $locations;
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
