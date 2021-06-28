<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Configuration extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_configurations',
            '__pk' => ['id'],
            
            'id' => 'int',
            'model' => 'string',
            'location' => 'string',
            'deviceType' => ['string', 'nativeName' => 'device_type'],
            'deviceQuantity' => ['string', 'nativeName' => 'device_quantity'],
            'managementType' => ['string', 'nativeName' => 'management_type'],
            'imageStatus' => ['string', 'nativeName' => 'image_status'],
            'vintages' => 'string',
            'adBound' => ['bool', 'nativeName' => 'ad_bound'],
            'count' => 'int',
            'isBundle' => ['bool', 'nativeName' => 'is_bundle'],
            'description' => 'string',
            'deleted' => 'bool',

            // 'room' => [ '1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => [ 'location_id' => 'id' ] ],
            'rooms' => ['N:M', 
                'to' => 'Classrooms_Room_Location', 
                'via' => 'classroom_room_configurations_map', 
                'toPrefix' => 'location', 
                'fromPrefix' => 'configuration',
            ],
            'softwareLicenses' => ['N:M', 
                'to' => 'Classrooms_Software_License', 
                'via' => 'classroom_room_configurations_software_licenses_map', 
                'toPrefix' => 'license', 
                'fromPrefix' => 'configuration',
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return @$this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/rooms/' . $this->room->id . '/configurations/';
    }

    public function getNoteUrl ()
    {
        return 'configurations/' . $this->id;
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

        $existingLicenses = $this->softwareLicenses->asArray();
        $posted = isset($data['licenses']) ? $data['licenses'] : [];
        $existing = [];
        foreach ($existingLicenses as $l)
        {
            $existing[$l->id] = 'on';
        }
        
        if (array_diff_key($existing, $posted) || array_diff_key($posted, $existing))
        {
            $updated = true;
        }

        return $updated;
    }

    public function getDiff ($data)
    {
        $updated = ['old' => [], 'new' => []];
        $licenses = $this->getSchema('Classrooms_Software_License');
        
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

        $existingLicenses = $this->softwareLicenses->asArray();
        $posted = $data['licenses'];
        $existing = [];
        foreach ($existingLicenses as $l)
        {
            $existing[$l->id] = 'on';
        }
        
        if ($removed = array_diff_key($existing, $posted))
        {
            foreach ($removed as $key => $on)
            {
                $license = $licenses->get($key);
                $updated['old'][$license->summary] = 'checked';
                $updated['new'][$license->summary] = 'unchecked';
            }
        }

        if ($added = array_diff_key($posted, $existing))
        {
            foreach ($added as $key => $on)
            {
                $license = $licenses->get($key);
                $updated['old'][$license->summary] = 'unchecked';
                $updated['new'][$license->summary] = 'checked';
            }                       
        }

        return $updated;
    }
}

