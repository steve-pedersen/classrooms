    <?php

/**
 * The Rooms controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_Controller extends Classrooms_Master_Controller
{
	private static $Fields = [
        'serialNumber', 'tagNumber', 'description', 'department', 'location', 'assignee', 'acquisitionCost', 'acquisitionDate','modifiedDate', 'lastCheckedDate', 'model', 'modelNumber', 'poNumber', 'area', 'status', 'type', 'pcType', 'officialDescription', 'officialSerialNumber', 'officialStatus', 'officialLocation', 'manufacturer', 'fleet', 'ucorpTag'
    ];

    public static $AllRoomAvEquipment = [
        'lcd_proj'=>'LCD Projector', 'lcd_tv'=>'LCD TV', 'vcr_dvd'=>'VCR/DVD', 'hdmi'=>'HDMI', 'vga'=>'VGA',
        'mic'=>'Mic', 'coursestream'=>'CourseStream', 'doc_cam'=>'Doc Cam', 'zoom'=>'Zoom Enabled', 'blu_ray' => 'Blu-ray'
    ];

    public static function getRouteMap ()
    {
        return [
            '/rooms' => ['callback' => 'listRooms'],
            '/rooms/autocomplete' => ['callback' => 'autoComplete'],
            '/rooms/metadata' => ['callback' => 'roomMetadata'],
            '/rooms/unconfigured' => ['callback' => 'listUnconfiguredRooms'],
            '/rooms/upgrades' => ['callback' => 'listUpgrades'],
            '/rooms/:id' => ['callback' => 'view', ':id' => '[0-9]+'],
            '/rooms/:id/edit' => ['callback' => 'editRoom', ':id' => '[0-9]+|new'],
            '/rooms/:id/upload' => ['callback' => 'uploadImages'],
            '/rooms/:id/files/:fileid/download' => ['callback' => 'downloadImage'],
            '/rooms/files/:fileid/preview' => ['callback' => 'previewImage'],
            '/rooms/:building/:number' => ['callback' => 'parseUrl'],
            '/rooms/:roomid/tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            '/rooms/:id/configurations/:cid/edit' => ['callback' => 'editConfiguration'],
            '/rooms/:id/upgrades/:uid/edit' => ['callback' => 'editUpgrade'],
            '/buildings' => ['callback' => 'listBuildings'],
            '/buildings/:id/edit' => ['callback' => 'editBuilding', ':id' => '[0-9]+|new'],
            '/types' => ['callback' => 'listTypes'],
            '/types/:id/edit' => ['callback' => 'editType', ':id' => '[0-9]+|new'],
            '/configurations' => ['callback' => 'listConfigurations'],
            '/configurations/:id' => ['callback' => 'viewConfigurationBundle'],
            '/configurations/:id/edit' => ['callback' => 'editConfigurationBundle'],
            '/schedules' => ['callback' => 'schedules'],
            '/schedules/autocomplete' => ['callback' => 'autoCompleteAccounts'],
            '/report' => ['callback' => 'report'],
        ];
    }

    public function view ()
    {
        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id');
    	$this->addBreadcrumb('rooms', 'List Rooms');
        $this->setPageTitle('View room ' . $location->codeNumber);

        $notes = $this->schema('Classrooms_Notes_Entry');
        $upgrades = $this->schema('Classrooms_Room_Upgrade');
        $siteSettings = $this->getApplication()->siteSettings;
        
        $facilityId = $location->building->code . '^' . $location->number;
        $trackRoomUrlApi = 'https://track.sfsu.edu/property/rooms?facility=' .$facilityId. '&fields=' . implode(',', self::$Fields);

        $supportText = unserialize($siteSettings->getProperty('supported-by-text'));
        if (isset($supportText[$location->supportedBy]) && trim($supportText[$location->supportedBy]) !== '')
        {
            $this->template->supportText = $supportText[$location->supportedBy];
        }

        $schedules = $this->schema('Classrooms_ClassData_CourseSchedule');
        // $currentSemester = $this->guessRelevantSemesters()['curr']['code'];
        $this->template->roomSchedules = $schedules->find(
            $schedules->room_id->equals($location->id)
            // $schedules->termYear->equals($currentSemester)->andIf($schedules->room_id->equals($location->id))
        );
        
        $upgrade = $upgrades->findOne(
            $upgrades->room_id->equals($location->id)->andIf(
                $upgrades->isComplete->isNull()->orIf($upgrades->isComplete->isFalse())
            )
        );

        $this->template->upgrade = $upgrade;
        $this->template->trackUrl = $trackRoomUrlApi;
        $this->template->mode = $this->request->getQueryParameter('mode', 'basic');
    	$this->template->room = $location;
    	$this->template->allAvEquipment = self::$AllRoomAvEquipment;
        $this->template->avEquipmentNotes = @unserialize($siteSettings->getProperty('av-equipment-notes')) ?? [];
        $notesCondition = $notes->anyTrue(
            $notes->path->equals($location->getNotePath()),
            $notes->path->like($location->getNotePath().'/%')
        );
        $this->template->notes = $location->inDatasource ? $notes->find(
            $notesCondition, ['orderBy' => '-createdDate']
        ) : [];

    }

    public function parseUrl ()
    {
        $code = strtolower($this->getRouteVariable('building', ''));
        $number = strtolower($this->getRouteVariable('number', ''));

        $buildings = $this->schema('Classrooms_Room_Building');
        $rooms = $this->schema('Classrooms_Room_Location');

        $building = $this->requireExists($buildings->findOne($buildings->code->lower()->equals($code)));
        $room = $this->requireExists($rooms->findOne(
            $rooms->number->lower()->equals($number)->andIf($rooms->buildingId->equals($building->id))
        ));

        $this->forward('rooms/' . $room->id);
    }

    private function buildUnselectQueries ($selectedBuildings, $selectedTypes, $selectedEquipment, $capacity)
    {
        $unselectQueries = [
            'buildings' => [],
            'types' => [],
            'equipment' => [],
            'cap' => ''
        ];

        foreach ($selectedBuildings as $selected)
        {
            $unselected = array_filter($selectedBuildings, function ($s) use ($selected) {
                return $s !== $selected;
            });

            $unselectQueries['buildings'][$selected] = http_build_query([
                'buildings' => $unselected,
                'types' => $selectedTypes,
                'equipment' => $selectedEquipment,
                'cap' => $capacity
            ]);
        }

        foreach ($selectedTypes as $selected)
        {
            $unselected = array_filter($selectedTypes, function ($s) use ($selected) {
                return $s !== $selected;
            });

            $unselectQueries['types'][$selected] = http_build_query([
                'buildings' => $selectedBuildings,
                'types' => $unselected,
                'equipment' => $selectedEquipment,
                'cap' => $capacity
            ]);
        }   

        foreach ($selectedEquipment as $selected)
        {
            $unselected = array_filter($selectedEquipment, function ($s) use ($selected) {
                return $s !== $selected;
            });

            $unselectQueries['equipment'][$selected] = http_build_query([
                'buildings' => $selectedBuildings,
                'types' => $selectedTypes,
                'equipment' => $unselected,
                'cap' => $capacity
            ]);
        }  

        $unselectQueries['cap'] = http_build_query([
            'buildings' => $selectedBuildings,
            'types' => $selectedTypes,
            'equipment' => $selectedEquipment,
            'cap' => null
        ]);

        return $unselectQueries;
    }

    public function listRooms ()
    {
    	$viewer = $this->getAccount();
        $this->setPageTitle('List Classrooms');
        
        if ($this->hasPermission('edit') || $this->hasPermission('view schedules'))
        {
            $this->addBreadcrumb('schedules', 'View Scheduled Rooms');
        }

        if (!$viewer)
        {
            $this->template->loginToViewOwnRooms = true;
        }

        $schedules = $this->schema('Classrooms_ClassData_CourseSchedule');
        $locations = $this->schema('Classrooms_Room_Location');
        $buildingSchema = $this->schema('Classrooms_Room_Building');
        $buildings = $buildingSchema->find(
            $buildingSchema->deleted->isNull()->orIf($buildingSchema->deleted->isFalse()),
            ['orderBy' => 'name']
        );
        $typeSchema = $this->schema('Classrooms_Room_Type');
        $types = $typeSchema->find(
            $typeSchema->deleted->isNull()->orIf($typeSchema->deleted->isFalse()),
            ['orderBy' => 'name']
        );
        $titles = $this->schema('Classrooms_Software_Title');

        $selectedBuildings = $this->request->getQueryParameter('buildings', []);
        $selectedTypes = $this->request->getQueryParameter('types', []);
        $selectedTitles = $this->request->getQueryParameter('titles');
        $selectedEquipment = $this->request->getQueryParameter('equipment', []);
        $capacity = $this->request->getQueryParameter('cap');
        $allLabsSelected = false;
        $s = $this->request->getQueryParameter('s');

        $condition = $locations->deleted->isFalse()->orIf($locations->deleted->isNull());
        
        $userRooms = [];
        if ($this->request->getQueryParameter('display'))
        {
            $userRooms = $schedules->findValues('room_id', 
                $schedules->room_id->isNotNull()->andIf($schedules->faculty_id->equals($viewer->username))
            );
            $condition = $condition->andIf($locations->id->inList($userRooms));
        }

        if ($selectedBuildings)
        {
            $building = null;
            foreach ($selectedBuildings as $selected)
            {
                $query = $locations->buildingId->equals($selected);
                $building = $building ? $building->orIf($query) : $query;
            }
            $condition = $building ? $condition->andIf($building) : $condition;
        }

        if ($selectedTypes)
        {
            $index = array_search('allLabs', $selectedTypes);
            if ($index !== false)
            {
                $allLabs = $typeSchema->findValues('id',
                    $typeSchema->allTrue(
                        $typeSchema->deleted->isFalse()->orIf($typeSchema->deleted->isNull()),
                        $typeSchema->isLab->isTrue()
                    )
                );
                unset($selectedTypes[$index]);

                foreach ($allLabs as $typeId)
                {
                    if (!in_array($typeId, $selectedTypes))
                    {
                        $selectedTypes[] = $typeId;
                    }
                }
                $allLabsSelected = true;
            }

            $type = null;
            foreach ($selectedTypes as $selected)
            {
                $query = $locations->typeId->equals($selected);
                $type = $type ? $type->orIf($query) : $query;
            }
            $condition = $type ? $condition->andIf($type) : $condition;
        }
        if ($selectedTitles)
        {
            foreach ($selectedTitles as $selected)
            {
                $title = $titles->get($selected);
                $query = $locations->id->inList(array_keys($title->getRoomsUsedIn()));
                $condition = $condition ? $condition->andIf($query) : $query;
            }
        }
        if ($selectedEquipment)
        {
            $equip = null;
            foreach ($selectedEquipment as $selected)
            {
                $query = $locations->avEquipment->like('%:"' . $selected . '";%');
                $equip = $equip ? $equip->andIf($query) : $query;
            }
            $condition = $condition ? $condition->andIf($equip) : $condition;
        }
        if ($capacity)
        {
            $query = $locations->capacity->greaterThanOrEquals($capacity);
            $condition = $condition ? $condition->andIf($query) : $query;
        }

        $unselectQueries = $this->buildUnselectQueries($selectedBuildings, $selectedTypes, $selectedEquipment, $capacity);
        
        $rooms = $locations->find($condition, ['orderBy' => ['building_id', '-number']]);

        if ($s)
        {
            $rooms = $this->autoComplete(true);
        }

        $sortedRooms = [];
        foreach ($rooms as $room)
        {
            $sortedRooms[$room->building->code . $room->number] = $room;
        }
        ksort($sortedRooms, SORT_NATURAL);

        $this->template->selectedBuildings = $buildingSchema->findValues(['code' => 'id'], 
            $buildingSchema->id->inList($selectedBuildings)
        );
        $this->template->selectedTypes = $typeSchema->findValues(['name' => 'id'], 
            $typeSchema->id->inList($selectedTypes)
        );
        $this->template->allLabsSelected = $allLabsSelected;
        $this->template->selectedTitles = $selectedTitles;
        $this->template->selectedEquipment = $selectedEquipment;
        $this->template->searchQuery = $s;
        $this->template->capacity = $capacity;
        $this->template->unselectQueries = $unselectQueries;
        $this->template->buildings = $buildings;
        $this->template->types = $types;
        $this->template->rooms = $sortedRooms;
        $this->template->titles = $titles->getAll(['orderBy' => 'name']);
        $this->template->allAvEquipment = self::$AllRoomAvEquipment;
        $this->template->defaultRoomText = $this->getApplication()->siteSettings->getProperty('default-room-text', 'There is no detailed information currently available for this room.');
    }

    public function schedules ()
    {
        $viewer = $this->requireLogin();
        $restrictResults = $viewer && !$this->hasPermission('edit') && !$this->hasPermission('view schedules');
        $scheduleSchema = $this->schema('Classrooms_ClassData_CourseSchedule');
        $locationSchema = $this->schema('Classrooms_Room_Location');

        $this->setPageTitle('Room schedules');
        $this->addBreadcrumb('rooms', 'View All Rooms');

        $semesters = $this->guessRelevantSemesters();
        $showAll = $this->hasPermission('view schedules') ? $this->request->getQueryParameter('showAll', false) : false;
        $userId = $this->request->getQueryParameter('u', $this->request->getQueryParameter('auto'));
        $roomQuery = $this->request->getQueryParameter('s');
        $exactRoom = (int) $this->request->getQueryParameter('room');
        $showEmptyRooms = $this->request->getQueryParameter('showEmptyRooms');
        $termYear = $this->request->getQueryParameter('t', $semesters['curr']['code']);
        $windowQuery = $this->request->getQueryParameter('window', null);
        $windowQuery = ($windowQuery === null || $windowQuery === '') ? null : $windowQuery;

        if ($windowQuery !== null)
        {
            $meetingWindowStart = date("H:i", strtotime("now"));
            $meetingWindowEnd = date("H:i", strtotime("+{$windowQuery} hours"));
            $meetingDOW = lcfirst((new DateTime)->format('l'));
            
            // if ($showEmptyRooms !== null)
            // {
            //     $temp = $meetingWindowStart;
            //     $meetingWindowStart = $meetingWindowEnd;
            //     $meetingWindowEnd = $temp;
            // }
        }

        $hasSearch = ($this->hasPermission('view schedules') || $this->hasPermission('edit')) && ($exactRoom || $userId || $roomQuery || $windowQuery !== null);
        
        $condition = null;
        if ($hasSearch || $restrictResults || $showAll)
        {
            $condition = $scheduleSchema->allTrue(
                $scheduleSchema->termYear->equals($termYear),
                $scheduleSchema->userDeleted->isNull()->orIf(
                    $scheduleSchema->userDeleted->isFalse()
                ),
                $scheduleSchema->room_id->isNotNull()
            );
        }

        $onlineCourses = null;
        if ($restrictResults)
        {
            $temp = $scheduleSchema->faculty_id->equals($viewer->username);
            $condition = $condition ? $condition->andIf($temp) : $temp;
            $condition2 = $scheduleSchema->allTrue(
                $scheduleSchema->faculty_id->equals($viewer->username),
                $scheduleSchema->termYear->equals($termYear),
                $scheduleSchema->userDeleted->isNull()->orIf(
                    $scheduleSchema->userDeleted->isFalse()
                ),
                $scheduleSchema->room_id->isNull()
            );
            $onlineCourses = $scheduleSchema->find($condition2);
        }

        $user = null;
        if ($userId)
        {   
            $user = $this->schema('Classrooms_ClassData_User')->get($userId);
            $condition = $condition->andIf($scheduleSchema->faculty_id->equals($userId));
        }

        if ($exactRoom)
        {
            $room = $locationSchema->get($exactRoom);
            $condition = $condition->andIf($scheduleSchema->room_id->equals($room->id));
            $this->addBreadcrumb($room->roomUrl, $room->codeNumber);
            $roomQuery = $room->codeNumber;
        }
        elseif ($roomQuery || $showAll)
        {
            $query = $showAll ? '%' : $roomQuery;
            $rooms = $this->autoComplete($query);
            $roomIds = [];
            foreach ($rooms as $room)
            {
                $roomIds[$room->id] = $room->id;
            }
            $condition = $condition->andIf($scheduleSchema->room_id->inList($roomIds));
        }
        
        $allScheduledRooms = [];
        $scheduledRooms = [];
        $result = $condition ? $scheduleSchema->find($condition, ['orderBy' => 'room_id']) : [];
        $order = ['M'=>[], 'MW'=>[], 'MWF'=>[], 'T'=>[], 'TR'=>[], 'W'=>[], 'R'=>[], 'F'=>[], 'S'=>[]];

        foreach ($result as $courseSchedule)
        {
            $allScheduledRooms[$courseSchedule->room->id] = $courseSchedule->room->id;

            if ($windowQuery !== null)
            {
                set_time_limit(0);
                ini_set('memory_limit', '-1'); 
                $withinWindow = false;
                $savedScheduleInfo = unserialize($courseSchedule->schedules);
                
                if (!empty($savedScheduleInfo))
                {
                    foreach ($savedScheduleInfo as $sched)
                    {
                        if ($sched['info'][$meetingDOW])
                        {
                            if (($sched['info']['start_time'] <= $meetingWindowStart || 
                                 $sched['info']['start_time'] <= $meetingWindowEnd) 
                                &&
                                ($sched['info']['end_time'] >= $meetingWindowStart ||
                                 $sched['info']['end_time'] >= $meetingWindowEnd))
                            {
                                $withinWindow = true;
                                break;
                            }
                        }
                    }
                }
                else
                {
                    // asynchronous course
                }
            }

            // skip if meeting time is not within specified time window
            if ($windowQuery !== null && !$withinWindow && !$showEmptyRooms) { continue; }

            // skip if meeting time IS within window (i.e. room is not empty during window)
            if ($windowQuery !== null && $withinWindow && $showEmptyRooms) { continue; }

            // group by building and room for alpha-numeric sort (e.g. BUS104)
            $keyRoom = $courseSchedule->room->getCodeNumber('');
            if (!isset($scheduledRooms[$keyRoom]))
            {
                $scheduledRooms[$keyRoom] = [
                    'room' => $courseSchedule->room, 
                    'schedules' => []
                ];
            }

            // order by first occurring scheduled meeting during the week (e.g. W & M results in M)
            $scheduleInfo = unserialize($courseSchedule->schedules);
            usort($scheduleInfo, function ($a, $b) use ($order) {
                $posA = array_search($a['info']['stnd_mtg_pat'], array_keys($order));
                $posB = array_search($b['info']['stnd_mtg_pat'], array_keys($order));
                return $posA - $posB;
            });
            
            // combine multiple meetings into a single day-of-week key (e.g. M & W become MW)
            $keySched = '';
            foreach ($scheduleInfo as $sched)
            {
                if ($sched['info']['stnd_mtg_pat'] !== $keySched)
                {
                    $keySched .= $sched['info']['stnd_mtg_pat'];
                }
            }
            
            // add course schedule to the day of week indexed array
            if (!isset($scheduledRooms[$keyRoom]['schedules'][$keySched]))
            {
                $scheduledRooms[$keyRoom]['schedules'][$keySched] = [];
            }
            $scheduledRooms[$keyRoom]['schedules'][$keySched][] = $courseSchedule;
        }
        
        // add the room and course schedule info to the order defined by $order
        foreach ($scheduledRooms as $key => $sr)
        {
            $sorted = $order;
            foreach ($sr['schedules'] as $dow => $scheds)
            {
                // sort course schedules that meet on the same days by meeting time
                $timeOrder = [];
                foreach ($scheds as $sched)
                {
                    $scheduleInfo = unserialize($sched->schedules);
                    foreach ($scheduleInfo as $details)
                    {
                        $timeOrder[$details['info']['start_time'] . $sched->id] = $sched;
                        break;
                    }
                }
                ksort($timeOrder, SORT_NATURAL);
                $scheds = $timeOrder;

                // add time sorted to day of week array
                foreach ($scheds as $sched)
                {
                    $sorted[$dow][] = $sched;
                }
            }

            $scheduledRooms[$key]['schedules'] = $sorted;
        }

        // add any remaining unscheduled rooms that fit the search criteria.
        // don't do this if searching by instructor   
        if ($showEmptyRooms && !$userId)
        {
            $unscheduledCondition = $locationSchema->allTrue(
                $locationSchema->deleted->isFalse()->orIf($locationSchema->deleted->isNull()),
                $locationSchema->id->notInList($allScheduledRooms)
            );
            $unscheduledCondition = $roomQuery ? $unscheduledCondition->andIf($locationSchema->id->inList($roomIds)) : $unscheduledCondition;
            
            $unscheduledLocations = $locationSchema->find($unscheduledCondition);

            foreach ($unscheduledLocations as $room)
            {
                $scheduledRooms[$room->getCodeNumber('')] = ['room' => $room, 'schedules' => []];
            }
        }

        // overall sort by building and room (e.g. BUS104)
        ksort($scheduledRooms, SORT_NATURAL);

        $this->template->selectedSemester = $this->codeToDisplay($termYear);
        $this->template->scheduledRooms = $scheduledRooms;
        $this->template->semesters = $semesters;
        $this->template->selectedTerm = $termYear;
        $this->template->selectedUser = $userId;
        $this->template->roomQuery = $roomQuery;
        $this->template->showAll = $showAll;
        $this->template->pFaculty = $restrictResults;
        $this->template->onlineCourses = $onlineCourses;
        $this->template->selectedWindow = $windowQuery;
        $this->template->showEmptyRooms = $showEmptyRooms;
        $this->template->windows = [
            ['hours' => '0', 'text' => 'Now'],
            ['hours' => '1', 'text' => 'Now +1 hour'],
            ['hours' => '2', 'text' => 'Now +2 hours'],
            ['hours' => '3', 'text' => 'Now +3 hours'],
            ['hours' => '4', 'text' => 'Now +4 hours'],
        ];
    }

    public function listUpgrades ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        
        $this->addBreadcrumb($this->baseUrl(''), 'Home');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');

        $status = $this->request->getQueryParameter('status', 'pending');
        $_upgrades = $this->schema('Classrooms_Room_Upgrade');

        switch ($status)
        {
            case 'complete':
                $condition = $_upgrades->isComplete->isTrue();
                break;
            case 'pending':
            default:
                $condition = $_upgrades->isComplete->isFalse()->orIf($_upgrades->isComplete->isNull());
                break;
        }
        
        $this->template->upgrades = $_upgrades->find($condition, ['orderBy' => 'upgradeDate']);
        $this->template->status = $status;
    }

    public function editUpgrade ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $_locations = $this->schema('Classrooms_Room_Location');
        $upgrades = $this->schema('Classrooms_Room_Upgrade');
        $room = $this->requireExists($this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id'));
        $uid = $this->getRouteVariable('uid');
        $upgrade = $uid === 'new' ? $upgrades->createInstance() : $upgrades->get($uid);
        
        $this->addBreadcrumb('rooms', 'List Rooms');
        $this->addBreadcrumb('rooms/' . $room->id . '/edit', 'Edit ' . $room->codeNumber);
        $this->setPageTitle('Upgrade room ' . $room->codeNumber);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'update':
                    $existing = $upgrade->inDatasource ? clone $upgrade : null;
                case 'save':
                    $upgrade->room = $room;
                    $upgrade->semester = (int) $this->request->getPostParameter('semester');
                    $upgrade->relocated_to = (int) $this->request->getPostParameter('relocatedTo');
                    $upgrade->upgradeDate = new DateTime($this->request->getPostParameter('upgradeDate'));
                    $upgrade->isComplete = $this->request->getPostParameter('isComplete', false);
                    $upgrade->notificationSent = $this->request->getPostParameter('notificationSent', false);
                    $upgrade->createdDate = $upgrade->inDatasource ? $upgrade->createdDate : new DateTime;
                    $upgrade->save();

                    $relocatedTo = $upgrade->relocated_to ? $_locations->get($upgrade->relocated_to) : null;
                    $message = 'Room '.$room->codeNumber.' scheduled for upgrade on '. $upgrade->upgradeDate->format('m/d/Y').'.'; 
                    $message .= $relocatedTo ? ' Classes relocated to '.$relocatedTo->codeNumber.'.' : '';

                    if ($existing && $upgrade->hasDiff($existing))
                    {
                        $message = '[Updated] ' . $message;
                        $upgrade->modifiedDate = new DateTime;
                        $upgrade->save();
                        $upgrade->addNote($message, $viewer, $upgrade->getDiff($existing));
                    }
                    elseif (!$existing)
                    {
                        $upgrade->addNote($message, $viewer);
                    }
                    else
                    {
                        $this->flash('No changes were applied', 'danger');
                        break;
                    }

                    if ($this->request->getPostParameter('sendNotification'))
                    {
                        $manager = new Classrooms_Communication_Manager($this->getApplication(), $this);
                        if ($logs = $manager->processRoomUpgradeCommunication($upgrade))
                        {
                            if (count($logs) > 0)
                            {
                                $upgrade->addNote('Upgrade email notification sent to ' . count($logs) . ' instructors.', $viewer);
                                $message .= ' Email sent to ' . count($logs) . ' instructors.';
                            }
                            else
                            {
                                $message .= ' No instructors are scheduled in this room.';
                            }
                        }
                    }
                    else
                    {
                        $message .= ' No email notifications sent.';
                    }

                    $this->flash($message);
                    break;

                case 'delete':
                    $message = 'The upgrade scheduled for Room '.$room->codeNumber.' on '. $upgrade->upgradeDate->format('m/d/Y').' was deleted.'; 
                    $upgrade->addNote($message, $viewer);
                    $upgrade->delete();
                    $this->flash($message);
                    break;
            }

            $this->response->redirect($room->roomUrl);
        }

        $locations = [];
        $results = $_locations->find(
            $_locations->deleted->isNull()->orIf($_locations->deleted->isFalse()),
            ['orderBy' => ['building_id', 'number']]
        );
        foreach ($results as $result)
        {
            $locations[$result->codeNumber] = $result;
        }
        ksort($locations, SORT_NATURAL);

        $this->template->room = $room;
        $this->template->upgrade = $upgrade;
        $this->template->viewer = $viewer;
        $this->template->locations = $locations;
        $this->template->semesters = [1 => 'Winter', 3 => 'Spring', 5 => 'Summer', 7 => 'Fall'];
    }


    public function listConfigurations ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $this->setPageTitle('List configurations');

        $configs = $this->schema('Classrooms_Room_Configuration');
        $this->template->configurations = $configs->find(
        	$configs->isBundle->isTrue()->andIf(
        		$configs->deleted->isNull()->orIf($configs->deleted->isFalse())
            ), ['orderBy' => 'model']
        );
    }

    public function viewConfigurationBundle ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        $config = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Configuration', 'id');

        $this->setPageTitle('View configuration '. $config->model);
        $this->addBreadcrumb('configurations', 'List Configurations');
        $this->addBreadcrumb('configurations/' . $config->id . '/edit', 'Edit');

        $this->template->config = $config;
    }

    public function editConfigurationBundle ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        $this->addBreadcrumb('configurations', 'List Configurations');

        $titles = $this->schema('Classrooms_Software_Title');
        $licenses = $this->schema('Classrooms_Software_License');
        $configs = $this->schema('Classrooms_Room_Configuration');

        $config = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Configuration', 'id', ['allowNew' => true]);

        if ($config->inDatasource)
        {
            $this->setPageTitle('Edit configuration '. $config->model);
        }
        else
        {
            $this->setPageTitle('Edit new configuration');
        }

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $configData = $this->request->getPostParameters();
                
                    $config->addNote(
                        'Configuration Bundle ' . ($config->inDatasource ? 'updated' : 'created'), 
                        $viewer, 
                        $this->request->getPostParameters()
                    );
                    $config->absorbData($configData);
                    $config->isBundle = true;
                    $config->adBound = isset($configData['adBound']);
                    $config->createdDate = $config->createdDate ?? new DateTime;
                    $config->modifiedDate = new DateTime;
                    $config->save();

                    $this->saveConfigurationLicenses($config, $configData);

                    $this->flash('Configuration bundle saved');
                    break;

                case 'delete':
                    $config->deleted = true;
                    $config->save();
                    $config->addNote('Configuration deleted', $viewer);

                    $this->flash('Deleted');
                    break;
            }

            $this->response->redirect('configurations/' . $config->id);
        }

        // $softwareTitles = [];
        // $available = $titles->find(
        //     $titles->deleted->isFalse()->orIf($titles->deleted->isNull()),
        //     ['orderBy' => ['-modifiedDate', '-createdDate']]
        // );
        // foreach ($available as $title)
        // {
        //     $softwareTitles[$title->name.$title->id] = $title;        
        // }
        // ksort($softwareTitles, SORT_NATURAL);

        $softwareLicenses = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->name.$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->name.$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->name.$license->version->title->id][] = $license;
        }
        ksort($softwareLicenses, SORT_NATURAL);

        $this->template->config = $config;
        $this->template->softwareLicenses = $softwareLicenses;
        // $this->template->softwareTitles = $softwareTitles;
    }

    public function editRoom ()
    {
    	$this->addBreadcrumb('rooms', 'List Rooms');
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id', ['allowNew' => true]);
        $configs = $this->schema('Classrooms_Room_Configuration');
        $types = $this->schema('Classrooms_Room_Type');
        $buildings = $this->schema('Classrooms_Room_Building');
        $licenses = $this->schema('Classrooms_Software_License');
        $titles = $this->schema('Classrooms_Software_Title');
        $notes = $this->schema('Classrooms_Notes_Entry');
        $upgrades = $this->schema('Classrooms_Room_Upgrade');
        $siteSettings = $this->getApplication()->siteSettings;

        if ($location->inDatasource)
        {
            $this->setPageTitle('Edit room ' . $location->codeNumber);
        }
        else
        {
            $this->setPageTitle('Edit new room');
        }

        $upgrade = $upgrades->findOne(
            $upgrades->room_id->equals($location->id)->andIf(
                $upgrades->isComplete->isNull()->orIf($upgrades->isComplete->isFalse())
            )
        );

        $customConfigurations = [];
        foreach ($location->customConfigurations as $custom)
        {
            if (!$custom->deleted)
            {
                $customConfigurations[] = $custom;
            }
        }

        if ($cid = $this->request->getQueryParameter('configuration', null))
        {
            $selectedConfiguration = $configs->get($cid);
        }
        else
        {
            $selectedConfiguration = !empty($customConfigurations) ? $customConfigurations[0] : $configs->createInstance();
        }
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $data = $this->request->getPostParameters();
                    
                    if (!isset($data['room']['number']) || $data['room']['number'] === '')
                    {
                        $this->flash('Room NOT saved. Please specify a room number', 'danger');
                        $this->response->redirect('rooms/new/edit');
                    }

                    $new = (bool) !$location->id;
                    $locationData = $data['room'];                                       

                    if ($location->hasDiff($locationData))
                    {
                        $location->addNote('Room details updated', $viewer, $location->getDiff($locationData));
                    }
                    $location->building_id = $locationData['building'] ? $locationData['building'] : null;
                    $location->type_id = $locationData['type'] ? $locationData['type'] : null;
                    $location->tutorial_id = $locationData['tutorial'] ? $locationData['tutorial'] : null;
                    $location->number = $locationData['number'];
                    $location->description = $locationData['description'];
                    $location->capacity = $locationData['capacity'];
                    $location->scheduledBy = $locationData['scheduledBy'];
                    $location->supportedBy = $locationData['supportedBy'];
                    $location->description = $locationData['description'];
                    // $location->url = $locationData['url'];
                    $location->avEquipment = isset($locationData['avEquipment']) ? serialize($locationData['avEquipment']) : '';
                    $location->uniprintQueue = $locationData['uniprintQueue'];
                    $location->releaseStationIp = $locationData['releaseStationIp'];
                    $location->printerModel = $locationData['printerModel'];
                    $location->printerIp = $locationData['printerIp'];
                    $location->printerServer = $locationData['printerServer'];
                    $location->createdDate = $location->createdDate ?? new DateTime;
                    $location->modifiedDate = new DateTime;
                    $location->configured = true;
                    $location->save();
                    if ($new)
                    {
                        $location->addNote('New room created', $viewer);
                    }

                    if (isset($data['internalNote']) && $data['internalNote'])
                    {
                        $internal = $this->schema('Classrooms_Room_InternalNote')->createInstance();
                        $internal->message = $data['internalNote'];
                        $internal->addedBy = $viewer;
                        $internal->createdDate = new DateTime;
                        $internal->location = $location;
                        $internal->save();
                        $internal->addNote('New internal note added: '.$data['internalNote'], $viewer);
                    }
                    
                    if ((isset($data['config']['new']['model']) && $data['config']['new']['model'] !== '') || 
                        isset($data['config']['existing']))
                    {
                        $new = false;
                        if (isset($data['config']['new']['model']) && $data['config']['new']['model'] !== '')
                        {
                            $configData = $data['config']['new'];
                            $config = $configs->createInstance();
                            $new = true;
                        }
                        else
                        {
                            $configData = $data['config']['existing'];
                            $config = $selectedConfiguration;
                            if ($config->hasDiff($configData))
                            {
                                $config->addNote('Custom configuration updated: '. $config->model, $viewer, $config->getDiff($configData));
                            }
                        }
                        $config->absorbData($configData);
                        $config->location = $configData['location'];
                        $config->adBound = isset($configData['adBound']);
                        $config->createdDate = $config->createdDate ?? new DateTime;
                        $config->modifiedDate = new DateTime;
                        $config->save();
                        
                        if ($new)
                        {
                            $location->configurations->add($config);
                            $location->configurations->save();
                            $config->addNote('New custom configuration created: '. $config->model, $viewer);
                        }

                        $licenseChanges = $this->saveConfigurationLicenses($config, $configData);
                    }

                    $bundleChanges = $this->saveRoomBundles($location, $this->request->getPostParameters());

                    if ($upgrade && !$upgrade->isComplete && isset($data['completeUpgrade']))
                    {
                        $upgrade->isComplete = true;
                        $upgrade->save();
                        $upgrade->addNote('Room upgrade complete', $viewer);
                    }

                    $this->flash('Room saved.');
                    $this->response->redirect('rooms/' . $location->id);

                    break;

    			case 'delete':
                    $location->deleted = true;
                    $location->save();
                    $location->configurations->removeAll();
                    $location->addNote('Room deleted', $viewer);

                    $this->flash('Room deleted');
                    $this->response->redirect('rooms/' . $location->id);
    				break;
            }
        }

        // $softwareTitles = [];
        // $available = $titles->find(
        //     $titles->deleted->isFalse()->orIf($titles->deleted->isNull()),
        //     ['orderBy' => ['-modifiedDate', '-createdDate']]
        // );
        // foreach ($available as $title)
        // {
        //     $softwareTitles[$title->name.$title->id] = $title;        
        // }
        // ksort($softwareTitles, SORT_NATURAL);


        $softwareLicenses = [];
        $available = $licenses->find(
            $licenses->deleted->isFalse()->orIf($licenses->deleted->isNull()),
            ['orderBy' => ['-modifiedDate', '-createdDate']]
        );
        foreach ($available as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->name.$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->name.$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->name.$license->version->title->id][] = $license;
        }
        ksort($softwareLicenses, SORT_NATURAL);


 
        $tuts = $this->schema('Classrooms_Tutorial_Page');
        $tutorials = $tuts->find($tuts->deleted->isFalse()->orIf($tuts->deleted->isNull()),['orderBy' => ['name', 'id']]);

        $scheduledBy = unserialize($siteSettings->getProperty('scheduled-by'));
        $supportedBy = unserialize($siteSettings->getProperty('supported-by'));
        $supportedByText = unserialize($siteSettings->getProperty('supported-by-text'));
        sort($scheduledBy);
        sort($supportedBy);
        ksort($supportedByText);

        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->customConfigurations = $customConfigurations;
        $this->template->upgrade = $upgrade;
        $this->template->types = $types->getAll(['orderBy' => 'name']);
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomAvEquipment = $location->avEquipment ? unserialize($location->avEquipment) : [];
        $this->template->allAvEquipment = self::$AllRoomAvEquipment;
        $this->template->softwareLicenses = $softwareLicenses;
        // $this->template->softwareTitles = $softwareTitles;
        $this->template->bundles = $configs->find($configs->isBundle->isTrue(), ['orderBy' => 'model']);
        $notesCondition = $notes->anyTrue(
            $notes->path->equals($location->getNotePath()),
            $notes->path->like($location->getNotePath().'/%')
        );
        $this->template->notes = $location->inDatasource ? $notes->find(
            $notesCondition, ['orderBy' => '-createdDate']
        ) : [];
        $this->template->scheduledBy = $scheduledBy;
        $this->template->supportedBy = $supportedBy;
        $this->template->supportedByText = $supportedByText;
        $this->template->tutorials = $tutorials;
    }

    public function report ()
    {
        $viewer = $this->requireLogin();
        $scheduleSchema = $this->schema('Classrooms_ClassData_CourseSchedule');
        $locationSchema = $this->schema('Classrooms_Room_Location');

        $this->setPageTitle('Room report');
        $this->addBreadcrumb('rooms', 'View All Rooms');

        $semesters = $this->guessRelevantSemesters();
        $termYear = $this->request->getQueryParameter('t', $semesters['curr']['code']);
        $selectedDow = $this->request->getQueryParameter('dow');
        $includeOnline = $this->request->getQueryParameter('includeOnline', false);
        
        // Classes in a room
        $condition = $scheduleSchema->allTrue(
            $scheduleSchema->termYear->equals($termYear),
            $scheduleSchema->userDeleted->isNull()->orIf(
                $scheduleSchema->userDeleted->isFalse()
            )
        );

        if (!$includeOnline)
        {
            $condition = $condition->andIf($scheduleSchema->room_id->isNotNull());
        }

        $result = $scheduleSchema->find($condition);

        $courses = [];
        $courseCount = 0;
        $dowCount = ['monday'=>0, 'tuesday'=>0, 'wednesday'=>0, 'thursday'=>0, 'friday'=>0, 'saturday'=>0, 'sunday'=>0];
        $hoursCount = ['07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0,'13'=>0,'14'=>0,'15'=>0,'17'=>0,'18'=>0,'19'=>0,'20'=>0,'21'=>0,'22'=>0,'23'=>0,'24'=>0];

        foreach ($result as $courseSchedule)
        {
            set_time_limit(0);
            ini_set('memory_limit', '-1'); 

            $savedScheduleInfo = unserialize($courseSchedule->schedules);
            
            if (!empty($savedScheduleInfo))
            {
                foreach ($savedScheduleInfo as $sched)
                {
                    if ($selectedDow && !$sched['info'][$selectedDow]) continue;

                    if (!isset($courses[$courseSchedule->course->shortName]))
                    {
                        $courseCount++;
                        $courses[$courseSchedule->course->shortName] = [
                            'course' => $courseSchedule->course, 'schedules' => []
                        ];
                    }
                    $info = $sched['info']['stnd_mtg_pat'] .' '. $sched['info']['start_time'] .' – '. $sched['info']['end_time'];
                    if (!in_array($info, $courses[$courseSchedule->course->shortName]['schedules']))
                    {
                        $courses[$courseSchedule->course->shortName]['schedules'][] = $info;    
                    }

                    if (!$selectedDow)
                    {
                        foreach ($dowCount as $day => $count)
                        {
                            $dowCount[$day] += $sched['info'][$day] ? 1 : 0;
                        }
                    }

                    if (!isset($sched['info']['start_time']) && !isset($sched['info']['end_time'])) continue;

                    $startHour = $sched['info']['start_time'][0] . $sched['info']['start_time'][1];
                    $endHour = $sched['info']['end_time'][0] . $sched['info']['end_time'][1];

                    foreach ($hoursCount as $hour => $count)
                    {
                        if ($startHour === $endHour && $startHour === $hour)
                        {
                            $hoursCount[$hour] += 1;
                            break;
                        }
                        elseif ($hour === $startHour)
                        {
                            $hoursCount[$hour]++;
                        }
                        elseif ($hour > $startHour && $hour <= $endHour)
                        {
                            $hoursCount[$hour]++;
                        }
                    }
                }
            }
        }

        ksort($courses, SORT_NATURAL);

        $this->template->selectedSemester = $this->codeToDisplay($termYear);
        $this->template->selectedDow = $selectedDow;
        $this->template->includeOnline = $includeOnline;
        $this->template->semesters = $semesters;
        $this->template->selectedTerm = $termYear;
        $this->template->dowCount = $dowCount;
        $this->template->hoursCount = $hoursCount;
        $this->template->courseCount = $courseCount;
        $this->template->courses = $courses;
    }

    public function roomMetadata ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $this->setPageTitle('Edit room settings');

        $siteSettings = $this->getApplication()->siteSettings;

        if ($this->request->wasPostedByUser())
        {
            $data = $this->request->getPostParameters();
            
            if ($data['scheduledBy']['new'] !== '')
            {
                $data['scheduledBy'][trim($data['scheduledBy']['new'])] = trim($data['scheduledBy']['new']);
            }
            unset($data['scheduledBy']['new']);

            if ($data['supportedBy']['new'] !== '')
            {
                $data['supportedBy'][trim($data['supportedBy']['new'])] = trim($data['supportedBy']['new']);
            }
            unset($data['supportedBy']['new']);

            $siteSettings->setProperty('scheduled-by', serialize($data['scheduledBy']));
            $siteSettings->setProperty('supported-by', serialize($data['supportedBy']));
            $siteSettings->setProperty('supported-by-text', serialize($data['supportedByText']));
            $siteSettings->setProperty('av-equipment-notes', serialize($data['avEquipmentNotes']));
            $this->flash('Room metadata updated.');
            $this->response->redirect('rooms/metadata');
        }

        $scheduledBy = unserialize($siteSettings->getProperty('scheduled-by'));
        $supportedBy = unserialize($siteSettings->getProperty('supported-by'));
        $supportedByText = unserialize($siteSettings->getProperty('supported-by-text'));
        $avEquipmentNotes = unserialize($siteSettings->getProperty('av-equipment-notes'));
        sort($scheduledBy);
        sort($supportedBy);
        ksort($supportedByText);
        ksort($avEquipmentNotes);

        $this->template->scheduledBy = $scheduledBy;
        $this->template->supportedBy = $supportedBy;
        $this->template->supportedByText = $supportedByText;
        $this->template->avEquipment = self::$AllRoomAvEquipment;
        $this->template->avEquipmentNotes = $avEquipmentNotes;
    }

    public function editTutorial () 
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

    	$location = $this->requireExists($this->schema('Classrooms_Room_Location')->get($this->getRouteVariable('roomid')));
    	$tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Tutorial', 'id', ['allowNew' => true]);
    	$notes = $this->schema('Classrooms_Notes_Entry');

        if ($tutorial->inDatasource)
        {
            $this->setPageTitle('Edit tutorial ' . $tutorial->name);
        }
        else
        {
            $this->setPageTitle('Edit new tutorial');
        }

    	if ($this->request->wasPostedByUser())
    	{
    		switch ($this->getPostCommand())
    		{
    			case 'save':
                    $new = (bool) !$tutorial->id;
                    if (!$new && $tutorial->hasDiff($this->request->getPostParameters()))
                    {
                        $tutorial->addNote('Tutorial updated', $viewer, $tutorial->getDiff($this->request->getPostParameters()));
                    }
    				// $tutorial->location_id = $location->id;
    				$tutorial->name = $this->request->getPostParameter('name');
                    $tutorial->headerImageUrl = $this->request->getPostParameter('headerImageUrl');
                    $tutorial->youtubeEmbedCode = $this->request->getPostParameter('youtubeEmbedCode');
    				$tutorial->description = $this->request->getPostParameter('description');
    				$tutorial->createdDate = $tutorial->createdDate ?? new DateTime;
    				$tutorial->modifiedDate = new DateTime;
    				$tutorial->save();
                    if ($new)
                    {
                        $tutorial->addNote('Tutorial created', $viewer);
                    }

    				$this->flash('Tutorial saved for room '. $location->codeName);
    				$this->response->redirect('rooms/' . $location->id);

    				break;

    			case 'delete':
                    $tutorial->deleted = true;
                    $tutorial->save();
                    $tutorial->addNote('Tutorial deleted', $viewer);

                    $this->flash('Tutorial deleted');
                    $this->response->redirect('rooms/' . $location->id);
    				break;
    		}
    	}

        foreach ($location->images as $image)
        {   
            $image->fullUrl = $this->baseUrl($image->imageSrc);
        }

        $this->template->images = $location->images;
    	$this->template->room = $location;
    	$this->template->tutorial = $tutorial;
        $this->template->tutorials = $this->schema('Classrooms_Room_Tutorial')->getAll(['orderBy' => 'name']);
        $notesCondition = $notes->anyTrue(
            $notes->path->equals($location->getNotePath()),
            $notes->path->like($location->getNotePath().'/%')
        );
        $this->template->notes = $tutorial->inDatasource ? $notes->find(
            $notesCondition, ['orderBy' => '-createdDate']
        ) : [];
    }

    public function listBuildings ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');
        $this->setPageTitle('List buildings');

        $schema = $this->schema('Classrooms_Room_Building');
        $this->template->buildings = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse()),
            ['orderBy' => 'name']
        );
    }

    public function editBuilding () 
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');
        $this->addBreadcrumb('buildings', 'Buildings');

        $building = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Building', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('buildings', 'List Buildings');

        if ($building->inDatasource)
        {
            $this->setPageTitle('Edit building ' . $building->name);
        }
        else
        {
            $this->setPageTitle('Edit new building');
        }

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    if ($this->processSubmission($building, ['name', 'code']))
                    {
                        $building->save();
                        $this->flash('Building saved.');
                    }
                    break;

                case 'delete':
                    if ($building->inDatasource)
                    {
                        $building->deleted = true;
                        $building->save();
                    }
                    $this->flash('Building deleted');
                    break;
            }
            $this->response->redirect('buildings');
        }

        $this->template->building = $building;
    }

    public function listUnconfiguredRooms ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');

        $this->setPageTitle('List unconfigured rooms');

        $schema = $this->schema('Classrooms_Room_Location');
        $condition = $schema->allTrue(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse()),
            $schema->type_id->isNull()->orIf(
                $schema->configured->isFalse()->orIf($schema->configured->isNull())
            )
        );
        $this->template->rooms = $schema->find(
            $condition,
            ['orderBy' => ['building_id', 'number']]
        );
    }

    public function listTypes ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');

        $this->setPageTitle('List room types');

        $schema = $this->schema('Classrooms_Room_Type');
        $this->template->types = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse()),
            ['orderBy' => 'name']
        );
    }

    public function editType () 
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('rooms/metadata', 'Room Settings');
        $this->addBreadcrumb('types', 'Types');
        
        $type = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Type', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('types', 'List types');

        if ($type->inDatasource)
        {
            $this->setPageTitle('Edit room type ' . $type->name);
        }
        else
        {
            $this->setPageTitle('Edit new room type');
        }

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    if ($this->processSubmission($type, ['name', 'description', 'isLab']))
                    {
                        $type->save();
                        $this->flash('Room type saved.');
                    }
                    break;

                case 'delete':
                    if ($type->inDatasource)
                    {
                        $type->deleted = true;
                        $type->save();
                    }
                    $this->flash('Room type deleted');
                    break;
            }
            $this->response->redirect('types');
        }

        $this->template->type = $type;
    }

    public function editConfiguration ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $rooms = $this->schema('Classrooms_Room_Location');
        $configs = $this->schema('Classrooms_Room_Configuration');
        $notes = $this->schema('Classrooms_Notes_Entry');
        $licenses = $this->schema('Classrooms_Software_License');
        $room = $location = $rooms->get($this->getRouteVariable('id'));
        $config = $configs->get($this->getRouteVariable('cid'));
        
        $this->addBreadcrumb('rooms', 'List Rooms');
        $this->addBreadcrumb('rooms/' . $room->id . '/edit', $room->building->name . ' ' . $room->number);

        $this->setPageTitle('Edit configuration ' . $config->model);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $config->addNote('Configuration updated', $viewer, $this->request->getPostParameters());
                    $config->absorbData($this->request->getPostParameters());
                    $config->save();

                    $licenseChanges = $this->saveConfigurationLicenses($config, $this->request->getPostParameters());

                    $this->flash('Updated');
                    break;

                case 'delete':
                    $config->deleted = true;
                    $config->save();
                    $config->addNote('Configuration deleted', $viewer);

                    $this->flash('Deleted');
                    break;
            }

            $this->response->redirect('rooms/' . $room->id . '/edit');
        }

        $softwareLicenses = [];
        foreach ($licenses->getAll(['orderBy' => ['-modifiedDate', '-createdDate']]) as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->name.$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->name.$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->name.$license->version->title->id][] = $license;
        }
        ksort($softwareLicenses, SORT_NATURAL);

        $this->template->room = $location;
        $this->template->config = $config;
        $this->template->softwareLicenses = $softwareLicenses;
        $notesCondition = $notes->anyTrue(
            $notes->path->equals($location->getNotePath()),
            $notes->path->like($location->getNotePath().'/%')
        );
        $this->template->notes = $location->inDatasource ? $notes->find(
            $notesCondition, ['orderBy' => '-createdDate']
        ) : [];
    }

    public function downloadImage ()
    {   
    	$roomId = $this->getRouteVariable('id');
    	$fileId = $this->getRouteVariable('fileid');
    	$location = $this->schema('Classrooms_Room_Location')->get($roomId);
    	$this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function previewImage ()
    {   
        // $this->requirePermission('edit');
        $fileId = $this->getRouteVariable('fileid');
        $this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function uploadImages ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        if ($this->request->wasPostedByUser())
        {
            $results = [
                'message' => 'Server error when uploading.',
                'status' => 500,
                'success' => false
            ];

            $files = $this->schema('Classrooms_Files_File');
            $file = $files->createInstance();
            $file->createFromRequest($this->request, 'file', false);
   
            if ($file->isValid())
            {
                $uploadedBy = (int)$this->request->getPostParameter('uploadedBy', $viewer->id);
                $roomId = (int)$this->request->getPostParameter('roomId', $this->getRouteVariable('id'));
                $file->uploaded_by_id = $uploadedBy;
                $file->location_id = $roomId;
                $file->moveToPermanentStorage();
                $file->save();

                // $location = $this->schema('Classrooms_Room_Location')->get($roomId);
                // $location->addNote('Image added to room.', $viewer, 
                //     ['old' => ['image' => null], 'new' => ['image' => $file->getDownloadLink()]]
                // );

                $results = [
                    'message' => 'Your file has been uploaded.',
                    'status' => 200,
                    'success' => true,
                    'roomId' => $roomId,
                    'file' => [
                        'id' => $file->id,
                        'url' => 'rooms/files/' . $file->id . '/preview',
                        'fullUrl' => $this->baseUrl('rooms/files/' . $file->id . '/preview'),
                        'name' => $file->remoteName,
                    ],
                ];
            }
            else
            {
                $messages = 'Incorrect file type or file too large.';
                $results['status'] = $messages !== '' ? 400 : 422;
                $results['message'] = $messages;
            }

            echo json_encode($results);
            exit;  
        }    

        $this->template->roomId = $this->getRouteVariable('id');
        $this->template->viewer = $viewer;
    }

    // public function uploadImages ()
    // {
    //     $viewer = $this->requireLogin();

    //     if ($this->request->wasPostedByUser())
    //     {
    //         $results = [
    //             'message' => 'Server error when uploading.',
    //             'status' => 500,
    //             'success' => false
    //         ];

    //         $files = $this->schema('Classrooms_Files_File');
    //         $file = $files->createInstance();
    //         $file->createFromRequest($this->request, 'file', false);
    
    //         if ($file->isValid())
    //         {
    //             $uploadedBy = (int)$this->request->getPostParameter('uploadedBy', $viewer->id);
    //             $roomId = (int)$this->request->getPostParameter('roomId', $this->getRouteVariable('id'));
    //             $file->uploaded_by_id = $uploadedBy;
    //             $file->location_id = $roomId;
    //             $file->moveToPermanentStorage();
    //             $file->save();
            
    //             $results = [
    //                 'message' => 'Your file has been uploaded.',
    //                 'status' => 200,
    //                 'success' => true,
    //                 'file' => [
    //                     'url' => 'rooms/'.$roomId.'/files/' . $file->id . '/download',
    //                     'fullUrl' => $this->baseUrl('rooms/'.$roomId.'/files/' . $file->id . '/download'),
    //                     'name' => $file->remoteName,
    //                 ],
    //             ];
    //         }
    //         else
    //         {
    //             $messages = 'Incorrect file type or file too large.';
    //             $results['status'] = $messages !== '' ? 400 : 422;
    //             $results['message'] = $messages;
    //         }

    //         echo json_encode($results);
    //         exit;  
    //     }    

    //     $this->template->viewer = $viewer;
    // }

    protected function saveRoomBundles ($room, $data)
    {
        $bundles = $this->schema('Classrooms_Room_Configuration');
        $posted = isset($data['bundles']) ? $data['bundles'] : [];
        $existing = $existingBundles = $removed = $added = [];
        
        foreach ($room->configurations->asArray() as $config)
        {
            if ($config->isBundle) $existingBundles[] = $config;
        }

        foreach ($existingBundles as $bundle)
        {
            $existing[$bundle->id] = 'on';
        }

        if ($removed = array_diff_key($existing, $posted))
        {
            foreach ($removed as $key => $on)
            {
                $bundle = $bundles->get($key);
                $room->configurations->remove($bundle);
                $room->configurations->save();
            }
        }

        if ($added = array_diff_key($posted, $existing))
        {
            foreach ($added as $key => $on)
            {
                $bundle = $bundles->get($key);
                $room->configurations->add($bundle);
                $room->configurations->save();
            }          
        }

        $room->save();

        return [$removed, $added];
    }

    protected function saveConfigurationLicenses ($config, $data)
    {
        $licenses = $this->schema('Classrooms_Software_License');
        $existingLicenses = $config->softwareLicenses->asArray();
        $posted = isset($data['licenses']) ? $data['licenses'] : [];
        $existing = $removed = $added = [];
        
        foreach ($existingLicenses as $l)
        {
            $existing[$l->id] = 'on';
        }
        
        if ($removed = array_diff_key($existing, $posted))
        {
            foreach ($removed as $key => $on)
            {
                $license = $licenses->get($key);
                $config->softwareLicenses->remove($license);
                $config->softwareLicenses->save();
            }
        }

        if ($added = array_diff_key($posted, $existing))
        {
            foreach ($added as $key => $on)
            {
                $license = $licenses->get($key);
                $config->softwareLicenses->add($license);
                $config->softwareLicenses->save();
            }                       
        }

        $config->save();

        return [$removed, $added];
    }

    public function autoCompleteAccounts ()
    {
        $role = $this->request->getQueryParameter('role');
        $roleRestrict = null;
        $limit = 20;

        if ($role)
        {
            $roleRestrict = $this->schema('Classrooms_AuthN_Role')->get($role);
        }

        $roles = $this->schema('Classrooms_AuthN_Role');
        $adminRole = $roles->findOne($roles->name->equals('Administrator'));

        $instructors = [];
        // if ($semester = $this->request->getQueryParameter('sem'))
        // {
        //     $rs = pg_query("
        //         SELECT DISTINCT(faculty_id) FROM classroom_classdata_course_schedules 
        //         WHERE term_year = '{$semester}' AND room_id IS NOT NULL
        //     ");                

        //     while (($row = pg_fetch_row($rs)))
        //     {
        //         $instructors[$row[0]] = $row[0];
        //     }
        // }

        $query = $this->request->getQueryParameter('s');
        $queryParts = explode(' ', $query);

        $accounts = $this->schema('Bss_AuthN_Account');

        $conds = array();

        foreach ($queryParts as $part)
        {
            $search = '%' . $part . '%';
            $conds[] = $accounts->anyTrue(
                $accounts->firstName->lower()->like(strtolower($search)),
                $accounts->lastName->lower()->like(strtolower($search)),
                $accounts->emailAddress->lower()->like(strtolower($search)),
                $accounts->username->lower()->like(strtolower($search))
            );
        }

        $candidates = array();

        if (!empty($conds))
        {
            $cond = array_shift($conds);

            foreach ($conds as $c)
            {
                $cond = $cond->orIf($c);
            }

            // if (!empty($instructors))
            // {
            //     $cond = $cond->andIf($accounts->username->inList($instructors));
            // }

            $candidates = $accounts->find($cond, ['limit' => $limit, 'orderBy' => ['+lastName', '+firstName'], 'arrayKey' => 'username']);

            $authZ = $this->getAuthorizationManager();
            foreach ($candidates as $i => $candidate)
            {
                if ($candidate->roles->has($adminRole) || $authZ->hasPermission($candidate, 'admin') || 
                    strlen($candidate->username) !== 9)
                {
                    unset($candidates[$i]);
                }
            }
        }

        if ($candidates)
        {
            $options = array();
            foreach ($candidates as $candidate)
            {
                $options[$candidate->lastName.$candidate->id] = array(
                    'id' => $candidate->id,
                    'firstName' => $candidate->firstName,
                    'lastName' => $candidate->lastName,
                    'username' => $candidate->username,
                );
            }
            ksort($options, SORT_NATURAL);

            $results = array(
                'message' => 'Candidates found.',
                'status' => 'success',
                'data' => $options
            );
        }
        else
        {
            $results = array(
                'message' => 'No candidates found.',
                'status' => 'error',
                'data' => ''
            );
        }

        echo json_encode($results);
        exit;
    }

    public function autoComplete ($nonJsonResponse = false)
    {
        $locations = $this->schema('Classrooms_Room_Location');
        $buildings = $this->schema('Classrooms_Room_Building');

        $query = $this->request->getQueryParameter('s');
        $queryParts = explode(' ', $query);

        $condition = null;
        $bldgCond = null;
        $userResults = [];

        // location match
        foreach ($queryParts as $i => $part)
        {
            $pattern = '%' . strtolower($query) . '%';
            if (strpos($pattern, ' ') !== false)
            {
                $patternParts = explode(' ', $pattern);

                $attributes = $locations->number->lower()->like($patternParts[$i])->orIf(
                    $locations->alternateName->lower()->like($patternParts[$i])
                );
                $condition = $condition ? $condition->orIf($attributes) : $attributes;
                
            }
            else
            {
                $attributes = $locations->anyTrue(
                    $locations->number->lower()->like($pattern),
                    $locations->alternateName->lower()->like($pattern)
                );

                $condition = $condition ? $condition->orIf($attributes) : $attributes;
            }
        }

        // building match
        foreach ($queryParts as $i => $part)
        {
            $pattern = '%' . strtolower($query) . '%';
            if (strpos($pattern, ' ') !== false)
            {
                $patternParts = explode(' ', $pattern);

                $attributes = $buildings->name->lower()->like($patternParts[$i])->orIf(
                    $buildings->code->lower()->like($patternParts[$i])
                );

                $bldgCond = $bldgCond ? $bldgCond->orIf($attributes) : $attributes;
            }
            else
            {
                $attributes = $buildings->name->lower()->like($pattern)->orIf(
                    $buildings->code->lower()->like($pattern)
                );

                $bldgCond = $bldgCond ? $bldgCond->orIf($attributes) : $attributes;
            }
        }

        $buildingIds = [];
        if ($bldgCond)
        {
            $buildingIds = $buildings->findValues(['id' => 'id'], $bldgCond);
        }

        if (!empty($buildingIds) && !$condition)
        {
            $condition = $condition->orIf(
                $locations->buildingId->inList($buildingIds)
            );
        }
        else if (!empty($buildingIds) && $condition && count($queryParts) > 1)
        {
            $condition = $condition->andIf(
                $locations->buildingId->inList($buildingIds)
            );
        }
        else if (!empty($buildingIds) && $condition && count($queryParts) === 1)
        {
            $condition = $condition->orIf(
                $locations->buildingId->inList($buildingIds)
            );
        }
        
        $nonDeleted = $locations->deleted->isFalse()->orIf($locations->deleted->isNull());
        $condition = $condition ? $condition->andIf($nonDeleted) : $nonDeleted;
        $rooms = $locations->find($condition, ['orderBy' => 'number']);

        if ($nonJsonResponse)
        {
            return $rooms;
        }

        if ($rooms)
        {
            $options = [];
            foreach ($rooms as $room)
            {
                $options[$room->id] = [
                    'id' => $room->id
                ];
            }

            $results = array(
                'message' => 'Candidates found.',
                'status' => 'success',
                'data' => $options,
            );
        }
        else
        {
            $results = array(
                'message' => 'No candidates found.',
                'status' => 'error',
                'data' => [],
            );
        }

        echo json_encode($results);
        exit;
    }

    protected function guessRelevantSemesters ()
    {
        $year = '2' . date('y');
        $month = date('n');
        $day = date('d');
        $prev = $curr = $next1 = $next2 = '';

        if ($month == 12 && $day > 15)
        {
            $year += 1;
            $prev = ($year - 1) . '7';
            $curr = $year . '1';
            $next1 = $year . '3';
            $next2 = $year . '5';
        }
        elseif ($month < 2 && $day < 10)
        {
            $prev = ($year - 1) . '7';
            $curr = $year . '1';
            $next1 = $year . '3';
            $next2 = $year . '5';           
        }
        elseif ($month < 5)
        {
            $prev = $year . '1';
            $curr = $year . '3';
            $next1 = $year . '5';
            $next2 = $year . '7';    
        }
        elseif ($month < 8)
        {
            $prev = $year . '3';
            $curr = $year . '5';
            $next1 = $year . '7';
            $next2 = ($year + 1) . '1';  
        }
        else
        {
            $prev = $year . '5';
            $curr = $year . '7';
            $next1 = ($year + 1) . '1';
            $next2 = ($year + 1) . '3';    
        }

        $semesters = [
            'prev' => ['code' => $prev, 'disp' => $this->codeToDisplay($prev)],
            'curr' => ['code' => $curr, 'disp' => $this->codeToDisplay($curr)],
            'next1' => ['code' => $next1, 'disp' => $this->codeToDisplay($next1)],
            'next2' => ['code' => $next2, 'disp' => $this->codeToDisplay($next2)],
        ];

        return $semesters;
    }

    public function codeToDisplay ($code)
    {
        $year = $code[0] . '0' . $code[1] . $code[2];
        switch ($code[3])
        {
            case '1':
                return 'Winter ' . $year;
            case '3':
                return 'Spring ' . $year;
            case '5':
                return 'Summer ' . $year;
            case '7':
                return 'Fall ' . $year;
        }

        return $code;
    }
}
