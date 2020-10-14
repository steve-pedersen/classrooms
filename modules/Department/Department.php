<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Department_Department extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_department_departments',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'code' => 'string',
            'deleted' => 'bool',

            'users' => ['N:M', 
                'to' => 'Classrooms_Department_User', 
                'via' => 'classroom_department_users_map', 
                'toPrefix' => 'user', 
                'fromPrefix' => 'department',
                'orderBy' => ['+last_name', '+first_name', '+email_address']
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return 'department/department/' . $this->id;
    }

    public function getNoteBase ()
    {
        return 'department/department/';
    }

    public function getNoteUrl ()
    {
        return 'department/' . $this->id;
    }
}
