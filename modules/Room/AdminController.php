<?php

/**
 */
class Classrooms_Room_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return [
            '/admin/rooms/defaults' => ['callback' => 'setDefaults'],
            '/admin/rooms/import' => ['callback' => 'importRoomData'],
        ];
    }

    public function setDefaults ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {           
            if ($defaultRoomDescription = $this->request->getPostParameter('default-room-description'))
            {
                $siteSettings->setProperty('default-room-description', $defaultRoomDescription);
                $this->flash('The default room description has been saved.');
                $this->response->redirect('admin/rooms/defaults');
            }
        }
        
        if ($defaultRoomDescription = $siteSettings->getProperty('default-room-description'))
        {
            $this->template->defaultRoomDescription = $defaultRoomDescription;
        }
    }


    public function importRoomData ()
    {
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'upload':
                    if ($file = $this->request->getFileUpload('csv'))
                    {
                        if ($file->isValid() && ($pathInfo = pathinfo($file->getRemoteName())))
                        {
                            if ($pathInfo['extension'] === 'csv')
                            {

                                if ($handle = fopen($file->getLocalPath(), "r"))
                                {
                                    if ($headers = fgetcsv($handle))
                                    {
                                        $rowLength = count($headers);

                                        $viewer = $this->getAccount();

                                        $locationSchema = $this->schema('Classrooms_Room_Location');
                                        $buildingSchema = $this->schema('Classrooms_Room_Building');
                                        $roomTypeSchema = $this->schema('Classrooms_Room_Type');

                                        $allRoomTypes = $roomTypeSchema->findValues(['name' => 'id']);
                                        $allBuildings = $buildingSchema->getAll();
                                        
                                        $buildingRooms = array();

                                        foreach ($allBuildings as $building)
                                        {
                                            $buildingRooms[$building->name] = array(
                                                'building' => $building, 
                                                'rooms' => array()
                                            );

                                            foreach ($building->locations as $room)
                                            {
                                                $buildingRooms[$building->name]['rooms'][$room->number] = $room;
                                            }
                                        }

                                        $tx = $this->getDataSource()->createTransaction();

                                        try
                                        {
                                            while (($row = fgetcsv($handle, 10000)) !== false) 
                                            {
                                                $data = array();
                                                if (count($row) < $rowLength) break;

                                                for ($i = 0; $i < $rowLength; $i++)
                                                {
                                                    $data[$headers[$i]] = $row[$i];
                                                }

                                                $newLocation = false;

                                                $building = trim($data['Building']);
                                                $room = trim($data['Room']);
                                                $roomType = trim($data['Room Type']);
                                                $scheduledBy = trim($data['Scheduled By']);
                                                $supportedBy = trim($data['Supported By']);
                                                $capacity = trim($data['Capacity']);

                                                $avEquipment = [
                                                    'LCD Projector' => trim($data['LCD Projector']),
                                                    'LCD TV' => trim($data['LCD TV']),
                                                    'VCR/DVD' => trim($data['VCR/DVD']),
                                                    'Blu-ray' => trim($data['Blu-ray']),
                                                    'HDMI' => trim($data['HDMI']),
                                                    'VGA' => trim($data['VGA']),
                                                    'Mic' => trim($data['Mic']),
                                                    'CourseStream' => trim($data['CourseStream']),
                                                    'Doc Cam' => trim($data['Doc Cam']),
                                                    'Zoom Enabled' => trim($data['Zoom Enabled']),
                                                ];

                                                //$avEquipment = array_filter($avEquipment);

                                                if ($roomType)
                                                {
                                                    if (!isset($allRoomTypes[$roomType]))
                                                    {
                                                        $newType = $roomTypeSchema->createInstance();
                                                        $newType->name = $type;
                                                        $newType->deleted = false;
                                                        $newType->save($tx);
                                                        $allRoomTypes[$roomType] = $newType->id;
                                                    }
                                                }

                                                if ($building)
                                                {
                                                    if (!isset($buildingRooms[$building]))
                                                    {
                                                        $newBuilding = $buildingSchema->createInstance();
                                                        $newBuilding->name = $building;
                                                        $newBuilding->deleted = false;
                                                        $newBuilding->save($tx);
                                                        $buildingRooms[$building] = array(
                                                            'building' => $newBuilding,
                                                            'rooms' => array()
                                                        );
                                                    }

                                                    $building = $buildingRooms[$building]['building'];

                                                    if ($room)
                                                    {
                                                        if (!isset($buildingRooms[$building->name]['rooms'][$room]))
                                                        {
                                                            $newLocation = true;
                                                            $newRoom = $locationSchema->createInstance();
                                                            $newRoom->applyDefaults($room, $building);
                                                            $newRoom->save($tx);
                                                            $newRoom->addNote('New room created', $viewer);
                                                            $buildingRooms[$building]['rooms'][$room] = $newRoom;
                                                        }

                                                        $room = $buildingRooms[$building]['rooms'][$room];
                                                        $room->roomType = $allRoomTypes[$roomType];
                                                        $room->supportedBy = $supportedBy;
                                                        $room->scheduledBy = $scheduledBy;
                                                        $room->capacity = $capacity;
                                                        $room->avEquipment = serialize($avEquipment);
                                                        $room->save();

                                                        $internal = $this->schema('Classrooms_Room_InternalNote')->createInstance();
                                                        $internal->message = $message = $newLocation ?
                                                            'Added new location through import' :
                                                            'Updated location through import';
                                                        $internal->addedBy = $viewer;
                                                        $internal->createdDate = new DateTime;
                                                        $internal->location = $room;
                                                        $internal->save();
                                                        $internal->addNote("New internal note added: $message", $viewer);
                                                    }
                                                }
                                            }

                                            $tx->commit();
                                        }
                                        catch (Exception $e)
                                        {
                                            $tx->rollback();
                                        }
                                    } 
                                } 
                            }
                        }
                    }

                    break;
            }
        }
    }
}