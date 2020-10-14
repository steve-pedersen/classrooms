<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Notes_Entry extends Bss_ActiveRecord_Base
{
    
    public static function SchemaInfo ()
    {
        return array(
            '__type' => 'classroom_notes_entries',
            '__pk' => array('id'),
            
            'id' => 'int',
            'path' => 'string',
            'message' => 'string',
            'oldValues' => ['string', 'nativeName' => 'old_values'],
            'newValues' => ['string', 'nativeName' => 'new_values'],
            'url' => 'string',
            'deleted' => 'bool',
            
            'createdDate' => array('datetime', 'nativeName' => 'created_date'),
            'createdBy' => array('1:1', 'to' => 'Bss_AuthN_Account', 'keyMap' => array('created_by_id' => 'id')),
        );
    }

    public function getOldValues ()
    {
        return unserialize($this->_fetch('oldValues'));
    }

    public function getNewValues ()
    {
        return unserialize($this->_fetch('newValues'));
    }

    public function getFullUrl ()
    {
        return $this->application->baseUrl($this->url);
    }
}
