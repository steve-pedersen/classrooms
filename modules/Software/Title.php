<?php

/**
 * Extensible implementation of an account system.
 * 
 * Applications should extend bss:core:authN/accountExtensions with a class
 * extending Bss_AuthN_AccountExtension.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_Title extends Bss_ActiveRecord_Base
{
    use Classrooms_Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_titles',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'deleted' => 'bool',

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
