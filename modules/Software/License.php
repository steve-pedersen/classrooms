<?php

/**
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
                'orderBy' => 'model'
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
            'expirationDate' => [ 'datetime', 'nativeName' => 'expiration_date' ],
        ];
    }

    public function getSummary ()
    {
        return $this->version->title->name . ' v' . $this->version->number . ' - ' . $this->number;
    }

    public function hasDiff ($data)
    {
        $updated = false;
        foreach ($this->getData() as $key => $value)
        {
            if ($updated) break;
            if (isset($data[$key]) && !is_object($value))
            {
                if ($this->$key != $data[$key])
                {   
                    $updated = true;
                }
            }
        }

        return $updated;
    }

    public function getDiff ($data)
    {
        $updated = ['old' => [], 'new' => []];
        foreach ($this->getData() as $key => $value)
        {
            if (isset($data[$key]) && !is_object($value))
            {
                if ($this->$key != $data[$key])
                {   
                    $updated['old'][$key] = $this->$key;
                    $updated['new'][$key] = $data[$key];
                }
            }
        }

        return $updated;
    }
}
