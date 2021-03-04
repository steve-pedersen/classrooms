<?php

/**
 */
class Classrooms_ClassData_Department extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_classdata_departments',
            '__pk' => ['id'],
            // '__azidPrefix' => 'at:classroom:classdata/Department/',
            
            'id'    => 'int',
            'name'  => 'string',
            'abbreviation'  => 'string',
            'description'   => 'string',
            'displayName'   => ['string', 'nativeName' => 'display_name'],
            'externalKey'   => ['string', 'nativeName' => 'external_key'],        
            'createdDate'   => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate'  => ['datetime', 'nativeName' => 'modified_date'],
        ];
    }

    public function setAbbreviation ($abbrev)
    {
        if (($pos = strrpos($abbrev, ' - ')) !== false)
        {
            $abbrev = substr($abbrev, $pos+3);
        }
        $this->_assign('abbreviation', $abbrev);
    }

}