<?php

/**
 * User ActiveRecord schema of ClassData/SIS course section data.
 *
 * @author Steve Pedersen (pedersen@sfsu.edu)
 */
class Classrooms_ClassData_User extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_classdata_users',
            '__pk' => ['id'],
            
            'id' => 'string',
            'firstName' => ['string', 'nativeName' => 'first_name'],
            'lastName' => ['string', 'nativeName' => 'last_name'],
            'emailAddress' => ['string', 'nativeName' => 'email_address'],
                 
            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],
            'deleted' => 'bool',
            
            'account' => ['1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => ['id' => 'username']],
            'enrollments' => ['N:M',
                'to' => 'Classrooms_ClassData_CourseSection',
                'via' => 'classroom_classdata_enrollments',
                'fromPrefix' => 'user',
                'toPrefix' => 'course_section',
                'properties' => [
                    'year_semester' => 'string', 
                    'role' => 'string', 
                    'deleted' => 'bool',
                    'created_date' => 'datetime',
                    'modified_date' => 'datetime',
                ],
                'orderBy' => ['-_map.year_semester', 'classNumber', 'sectionNumber'],
            ],
        ];
    }

    public function isFaculty ()
    {
        foreach ($this->enrollments as $course)
        {
            if ($this->enrollments->getProperty($course, 'role') === 'instructor')
            {
                return true;
            }
        }

        return false;
    }
}