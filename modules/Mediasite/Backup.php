<?php

/**
 * A presentation packup.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class MediasiteBackup_Mediasite_Backup extends Bss_ActiveRecord_Base
{
	public static function SchemaInfo ()
    {
        return [
            '__type' => 'mediasite_backup_backups',
            '__pk' => array('id'),
            
            'id' => 'int',
            'presentationId' => array('string', 'nativeName' => 'presentation_id'),
            'presentationInfo' => array('string', 'nativeName' => 'presentation_info'),

            'creationDate' => array('datetime', 'nativeName' => 'creation_date'),
            'migrationDate' => array('datetime', 'nativeName' => 'migration_date'),
            'deletionDate' => array('datetime', 'nativeName' => 'deletion_date'),
        ];
    }
    

    public function getPresentationInfo ()
    {
        $retVal = [];

        if ($json = $this->_fetch('presentationInfo'))
        {
            $retVal = json_decode($json); 
        }

        return $retVal;
    }


    public function setPresentationInfo($json)
    {
        if ($string = json_encode($json))
        {
            $this->_assign('presentationInfo', $string);
        }
    }
}