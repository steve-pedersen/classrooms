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
class Classrooms_Software_License extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_categories',
            '__pk' => ['id'],
            
            'id' => 'int',
            'number' => 'string',
            'description' => 'string',
            'seats' => 'int',
            'deleted' => 'bool',

            'version' => [ '1:1', 'to' => 'Classrooms_Software_Version', 'keyMap' => [ 'version_id' => 'id' ] ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }
}
