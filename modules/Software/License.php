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
            '__type' => 'classroom_software_licenses',
            '__pk' => ['id'],
            
            'id' => 'int',
            'number' => 'string',
            'description' => 'string',
            'seats' => 'string',
            'deleted' => 'bool',

            'version' => [ '1:1', 'to' => 'Classrooms_Software_Version', 'keyMap' => [ 'version_id' => 'id' ] ],
            'roomConfigurations' => ['N:M', 
                'to' => 'Classrooms_Room_Configuration', 
                'via' => 'classroom_room_configurations_software_licenses_map', 
                'toPrefix' => 'configuration', 
                'fromPrefix' => 'license',
                // 'properties' => ['title_id'],
                // 'orderBy' => ['+_map.title_id']
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
            'expirationDate' => [ 'datetime', 'nativeName' => 'expiration_date' ],
        ];
    }
}
