<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_Title extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_titles',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
            'deleted' => 'bool',
            'developerId' => ['int', 'nativeName' => 'developer_id'],
            'categoryId' => ['int', 'nativeName' => 'category_id'],

            'category' => [ '1:1', 'to' => 'Classrooms_Software_Category', 'keyMap' => [ 'category_id' => 'id' ] ],
            'developer' => [ '1:1', 'to' => 'Classrooms_Software_Developer', 'keyMap' => [ 'developer_id' => 'id' ] ],
            'versions' => ['1:N', 
                'to' => 'Classrooms_Software_Version', 
                'reverseOf' => 'title', 
                'orderBy' => [ '+modifiedDate', '+createdDate' ]
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getRoomsUsedIn ($showDeleted = false)
    {
        $rooms = [];
        foreach ($this->versions as $version)
        {
            foreach ($version->licenses as $license)
            {
                foreach ($license->roomConfigurations as $configs)
                {
                    foreach ($configs->rooms as $room)
                    {
                        if (!$room->deleted || $showDeleted)
                        {
                            $rooms[$room->id] = $room;
                        }
                    }
                }
            }
        }

        return $rooms;
    }

    public function getVersions ()
    {
        return $this->_fetch('versions');
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
