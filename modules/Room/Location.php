<?php

/**
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
            'capacity' => 'int',
            'facets' => 'string',
            'url' => 'string',
            'deleted' => 'bool',
            'typeId' => ['int', 'nativeName' => 'type_id'],
            'buildingId' => ['int', 'nativeName' => 'building_id'],

            'type' => [ '1:1', 'to' => 'Classrooms_Room_Type', 'keyMap' => [ 'type_id' => 'id' ] ],
            'building' => [ '1:1', 'to' => 'Classrooms_Room_Building', 'keyMap' => [ 'building_id' => 'id' ] ],
            'configurations' => ['1:N', 
                'to' => 'Classrooms_Room_Configuration', 
                'reverseOf' => 'room', 
                'orderBy' => [ '+modifiedDate', '+createdDate' ]
            ],
            
            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getCodeNumber ()
    {
        return $this->building->code . ' ' . $this->number;
    }

    public function getTutorial ()
    {
        $tuts = $this->getSchema('Classrooms_Room_Tutorial');
        return $tuts->findOne($tuts->locationId->equals($this->id));
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
