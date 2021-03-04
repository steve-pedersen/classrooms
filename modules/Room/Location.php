<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Location extends Bss_ActiveRecord_BaseWithAuthorization
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_locations',
            '__pk' => ['id'],
            '__azidPrefix' => 'at:classrooms:room/Location/',
            
            'id' => 'int',
            'number' => 'string',
            'alternateName' => ['string', 'nativeName' => 'alternate_name'],
            'description' => 'string',
            'capacity' => 'string',
            'facets' => 'string',
            'url' => 'string',
            'deleted' => 'bool',
            'typeId' => ['int', 'nativeName' => 'type_id'],
            'buildingId' => ['int', 'nativeName' => 'building_id'],

            'type' => [ '1:1', 'to' => 'Classrooms_Room_Type', 'keyMap' => [ 'type_id' => 'id' ] ],
            'building' => [ '1:1', 'to' => 'Classrooms_Room_Building', 'keyMap' => [ 'building_id' => 'id' ] ],
            'configurations' => ['N:M', 
                'to' => 'Classrooms_Room_Configuration', 
                'via' => 'classroom_room_configurations_map', 
                'toPrefix' => 'configuration', 
                'fromPrefix' => 'location',
            ],
            'images' => ['1:N', 
                'to' => 'Classrooms_Files_File', 
                'reverseOf' => 'room', 
                'orderBy' => [ '+uploadedDate', 'remoteName' ]
            ],
            
            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getCodeNumber ()
    {
        return $this->building->code . ' ' . $this->number;
    }

    public function getTutorial ()
    {
        $tuts = $this->getSchema('Classrooms_Room_Tutorial');
        return $tuts->findOne($tuts->locationId->equals($this->id));
    }

    public function getCustomConfigurations ()
    {
        $customConfigs = [];
        foreach ($this->configurations as $config)
        {
            if (!$config->isBundle)
            {
                $customConfigs[] = $config;
            }
        }

        return $customConfigs;
    }

    public function hasDiff ($data)
    {
        $updated = false;
        foreach ($this->getData() as $key => $value)
        {
            if ($updated) break;
            if (isset($data[$key]) && !is_object($value))
            {
                if ($key === 'facets')
                {   
                    $facets = unserialize($this->facets);
                    if (array_diff_key($facets, $data['facets']) || array_diff_key($data['facets'], $facets))
                    {
                        $updated = true;
                    }
                }
                elseif ($this->$key != $data[$key])
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
                if ($key === 'facets')
                {   
                    $facets = unserialize($this->facets);
                    if ($removed = array_diff_key($facets, $data['facets']))
                    {
                        foreach ($removed as $k => $on)
                        {
                            $updated['old'][$k] = 'checked';
                            $updated['new'][$k] = 'unchecked';
                        }
                    }

                    if ($added = array_diff_key($data['facets'], $facets))
                    {
                        foreach ($added as $k => $on)
                        {
                            $updated['old'][$k] = 'unchecked';
                            $updated['new'][$k] = 'checked';
                        }
                    }
                }
                elseif ($this->$key != $data[$key])
                {   
                    $updated['old'][$key] = $this->$key;
                    $updated['new'][$key] = $data[$key];
                }
            }
        }

        return $updated;
    }

    public function getNotePath ()
    {
        return $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/rooms/';
    }

    public function getNoteUrl ()
    {
        return 'room/' . $this->id;
    }
}
