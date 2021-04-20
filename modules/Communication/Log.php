<?php

/**
 *  Log a single communication send to a user, as part of a Communication's Event
 */
class Classrooms_Communication_Log extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_communication_logs',
            '__pk' => ['id'],
            
            'id' => 'int',
            
            'communication' => ['1:1', 'to' => 'Classrooms_Communication_Communication', 'keyMap' => ['communication_id' => 'id']],
            'event' => ['1:1', 'to' => 'Classrooms_Communication_Event', 'keyMap' => ['event_id' => 'id']],
            // 'faculty' => ['1:1', 'to' => 'Classrooms_ClassData_User', 'keyMap'=> ['faculty_id' => 'id']],
            'emailAddress' => ['string', 'nativeName' => 'email_address'],
            
            'creationDate' => ['datetime', 'nativeName' => 'creation_date'],
        ];
    }
}