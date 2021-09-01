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
            'scheduledBy' => ['string', 'nativeName' => 'scheduled_by'],
            'supportedBy' => ['string', 'nativeName' => 'supported_by'],
            'avEquipment' => ['string', 'nativeName' => 'av_equipment'],
            'uniprint' => 'string',
            'uniprintQueue' => ['string', 'nativeName' => 'uniprint_queue'],
            'releaseStationIp' => ['string', 'nativeName' => 'release_station_ip'],
            'printerModel' => ['string', 'nativeName' => 'printer_model'],
            'printerIp' => ['string', 'nativeName' => 'printer_ip'],
            'printerServer' => ['string', 'nativeName' => 'printer_server'],
            'url' => 'string',
            'deleted' => 'bool',
            'configured' => 'bool',
            'typeId' => ['int', 'nativeName' => 'type_id'],
            'buildingId' => ['int', 'nativeName' => 'building_id'],

            'type' => [ '1:1', 'to' => 'Classrooms_Room_Type', 'keyMap' => [ 'type_id' => 'id' ] ],
            'building' => [ '1:1', 'to' => 'Classrooms_Room_Building', 'keyMap' => [ 'building_id' => 'id' ] ],
            'tutorial' => [ '1:1', 'to' => 'Classrooms_Tutorial_Page', 'keyMap' => [ 'tutorial_id' => 'id' ] ],
            'configurations' => ['N:M', 
                'to' => 'Classrooms_Room_Configuration', 
                'via' => 'classroom_room_configurations_map', 
                'toPrefix' => 'configuration', 
                'fromPrefix' => 'location',
            ],
            'internalNotes' => ['1:N', 
                'to' => 'Classrooms_Room_InternalNote', 
                'reverseOf' => 'location', 
                'orderBy' => [ '-createdDate' ]
            ],
            
            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getRoomUrl ()
    {
        // return $this->getApplication()->baseUrl('rooms/' . $this->id);
        return $this->getApplication()->baseUrl(
            'rooms/' . strtolower($this->building->code) . '/' . strtolower($this->number)
        );
    }

    public function getCodeNumber ()
    {
        return $this->building->code . ' ' . $this->number;
    }

    public function hasSoftwareOrHardware ()
    {
        $total = 0;
        foreach ($this->getAllConfigurations() as $config)
        {
            if (!$config->deleted)
            {
                $total++;
            }
        }

        return $total > 0;
    }

    // get all non-deleted configs
    public function getAllConfigurations ()
    {
        $configs = [];
        foreach ($this->_fetch('configurations') as $config)
        {
            if (!$config->deleted)
            {
                $configs[] = $config;
            }
        }

        return $configs;
    }

    public function getCustomConfigurations ()
    {
        $customConfigs = [];
        foreach ($this->configurations as $config)
        {
            if (!$config->isBundle && !$config->deleted)
            {
                $customConfigs[] = $config;
            }
        }

        return $customConfigs;
    }

    public function applyDefaults ($number, $building)
    {
        $siteSettings = $this->getApplication()->siteSettings;
        $this->number = $number;
        $this->configured = false;
        $this->building = $building;
        $this->description = $siteSettings->getProperty('default-room-description', 'This room has not been configured.');
        $this->supportedBy = 'Unknown';
        $this->save();

        return $this;
    }

    public function hasDiff ($data)
    {
        $updated = false;
        foreach ($this->getData() as $key => $value)
        {
            if ($updated) break;
            if (isset($data[$key]) && !is_object($value))
            {
                if ($key === 'avEquipment')
                {   
                    $avEquipment = $this->avEquipment ? unserialize($this->avEquipment) : [];
                    if (array_diff_key($avEquipment, $data['avEquipment']) || array_diff_key($data['avEquipment'], $avEquipment))
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
                if ($key === 'avEquipment')
                {   
                    $avEquipment = $this->avEquipment ? unserialize($this->avEquipment) : [];
                    if ($removed = array_diff_key($avEquipment, $data['avEquipment']))
                    {
                        foreach ($removed as $k => $on)
                        {
                            $updated['old'][$k] = 'checked';
                            $updated['new'][$k] = 'unchecked';
                        }
                    }

                    if ($added = array_diff_key($data['avEquipment'], $avEquipment))
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
