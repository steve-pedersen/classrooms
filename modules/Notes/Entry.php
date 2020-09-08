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
class Classrooms_Notes_Entry extends Bss_ActiveRecord_BaseWith
{
    
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'classroom_notes_entries',
            '__pk' => array('id'),
            
            'id' => 'int',
            'path' => 'string',
            'message' => 'string',
            'url' => 'string',
            'deleted' => 'bool',
            
            'createdDate' => array('datetime', 'nativeName' => 'created_date'),
            'createdBy' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('created_by_id' => 'id')),
        );
    }
}
