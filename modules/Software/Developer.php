<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_Developer extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_developers',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'deleted' => 'bool',

            'titles' => ['1:N', 
                'to' => 'Classrooms_Software_Title', 
                'reverseOf' => 'developer', 
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
        return '/software/titles/';
    }

    public function getNoteUrl ()
    {
        return '/software/titles/' . $this->id;
    }
}
