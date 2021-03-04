<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_Version extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_versions',
            '__pk' => ['id'],
            
            'id' => 'int',
            'number' => 'string',
            'deleted' => 'bool',

            'title' => [ '1:1', 'to' => 'Classrooms_Software_Title', 'keyMap' => [ 'title_id' => 'id' ] ],
            'licenses' => ['1:N', 
                'to' => 'Classrooms_Software_License', 
                'reverseOf' => 'version', 
                'orderBy' => [ 'expirationDate', '+modifiedDate', '+createdDate' ]
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getLicenses ()
    {
        return $this->_fetch('licenses');
    }

    public function getNotePath ()
    {
        return @$this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return '/software/titles/' . $this->title->id . '/versions/';
    }

    public function getNoteUrl ()
    {
        return '/software/versions/' . $this->id;
    }
}
