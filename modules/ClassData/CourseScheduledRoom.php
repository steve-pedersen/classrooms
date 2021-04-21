<?php

/**
 * Mapping object for faculty that are scheduled to teach a course section in a room.
 *
 * @author Steve Pedersen (pedersen@sfsu.edu)
 */
class Classrooms_ClassData_CourseScheduledRoom extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_classdata_course_scheduled_rooms',
            '__pk' => ['id'],
            
            'id' => 'string',
            'termYear' => ['string', 'nativeName' => 'term_year'], 
            'roomExternalId' => ['string', 'nativeName' => 'room_external_id'], 
            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],
            'userDeleted' => ['bool', 'nativeName' => 'user_deleted'],

            'faculty' => ['1:1', 'to' => 'Classrooms_ClassData_User', 'keyMap' => ['faculty_id' => 'id']],
            'account' => ['1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => ['account_id' => 'id']],
            'room' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['room_id' => 'id']],
            'course' => ['1:1', 'to' => 'Classrooms_ClassData_CourseSection', 'keyMap' => ['course_section_id' => 'id']],
        ];
    }

}
