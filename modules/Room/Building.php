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
class Classrooms_Room_Building extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_buildings',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'code' => 'string',
            'deleted' => 'bool',

            'locations' => ['1:N', 
                'to' => 'Classrooms_Room_Location', 
                'reverseOf' => 'building', 
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
        return 'room/buildings/';
    }

    public function getNoteUrl ()
    {
        return 'building/' . $this->id;
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
