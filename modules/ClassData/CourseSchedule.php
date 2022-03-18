<?php

/**
 * Faculty scheduled courses and their physical/virtual meeting location.
 *
 * @author Steve Pedersen (pedersen@sfsu.edu)
 */
class Classrooms_ClassData_CourseSchedule extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_classdata_course_schedules',
            '__pk' => ['id'],
            
            'id' => 'string',
            'termYear' => ['string', 'nativeName' => 'term_year'], 
            'courseType' => ['string', 'nativeName' => 'course_type'], //sync, async, hybrid, etc.
            'userDeleted' => ['bool', 'nativeName' => 'user_deleted'],
            'schedules' => 'string',

            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],

            'faculty' => ['1:1', 'to' => 'Classrooms_ClassData_User', 'keyMap' => ['faculty_id' => 'id']],
            'user' => ['1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => ['faculty_id' => 'username']],
            'account' => ['1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => ['account_id' => 'id']],
            'room' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['room_id' => 'id']],
            'course' => ['1:1', 'to' => 'Classrooms_ClassData_CourseSection', 'keyMap' => ['course_section_id' => 'id']],
        ];
    }

    public function getNotePath ()
    {
        return $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'classdata/courseschedules/';
    }

    public function getNoteUrl ()
    {
        return 'courseschedules/' . $this->id;
    }
}
