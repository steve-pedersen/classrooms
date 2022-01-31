<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Communication_Type extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_communication_types',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'deleted' => 'bool',
            'includeCoursesWithoutRooms' => ['bool', 'nativeName' => 'include_courses_without_rooms'], // e.g. online
            'includeUnconfiguredRooms' => ['bool', 'nativeName' => 'include_unconfigured_rooms'], // e.g. online

            'communications' => ['1:N', 
                'to' => 'Classrooms_Communication_Communication', 
                'reverseOf' => 'type', 
                'orderBy' => [ '+creationDate' ]
            ],

            'roomTypes' => ['N:M',
                'to' => 'Classrooms_Room_Type',
                'via' => 'classroom_communication_type_room_type_map',
                'fromPrefix' => 'communication_type',
                'toPrefix' => 'room_type',
            ],
        ];
    }
}
