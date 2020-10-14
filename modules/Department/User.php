<?php

/**
 * Department users, e.g. Chairs
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Department_User extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_department_users',
            '__pk' => ['id'],
            
            'id' => 'int',
            'firstName' => ['string', 'nativeName' => 'first_name'],
            'lastName' => ['string', 'nativeName' => 'last_name'],
            'emailAddress' => ['string', 'nativeName' => 'email_address'],
            'sfStateId' => ['string', 'nativeName' => 'sf_state_id'],
            'position' => 'string',
            'deleted' => 'bool',

            'departments' => ['N:M', 
                'to' => 'Classrooms_Department_Department', 
                'via' => 'classroom_department_users_map', 
                'toPrefix' => 'department', 
                'fromPrefix' => 'user',
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return 'department/user/' . $this->id;
    }

    public function getNoteBase ()
    {
        return 'department/user/';
    }

    public function getNoteUrl ()
    {
        return 'user/' . $this->id;
    }
}
