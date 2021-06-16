<?php

/**
 * The Rooms controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_Controller extends Classrooms_Master_Controller
{
	private static $Fields = array(
        'serialNumber',
        'tagNumber',
        'description',
        'department',
        'location',
        'assignee',
        'acquisitionCost',
        'acquisitionDate',
        'modifiedDate',
        'lastCheckedDate',
        'model',
        'modelNumber',
        'poNumber',
        'poNumber',
        'area',
        'status',
        'type',
        'pcType',
        'officialDescription',
        'officialSerialNumber',
        'officialStatus',
        'officialLocation',
        'manufacturer',
        'fleet',
        'ucorpTag',
    );

    public static $AllRoomAvEquipment = [
        'lcd_proj'=>'LCD Projector', 'lcd_tv'=>'LCD TV', 'vcr_dvd'=>'VCR/DVD', 'hdmi'=>'HDMI', 'vga'=>'VGA',
        'mic'=>'Mic', 'coursestream'=>'CourseStream', 'doc_cam'=>'Doc Cam', 'zoom'=>'Zoom Enabled'
    ];

    public static function getRouteMap ()
    {
        return [
            '/rooms' => ['callback' => 'listRooms'],
            '/rooms/autocomplete' => ['callback' => 'autoComplete'],
            '/rooms/:id' => ['callback' => 'view'],
            '/rooms/:id/edit' => ['callback' => 'editRoom', ':id' => '[0-9]+|new'],
            '/rooms/:roomid/tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            '/rooms/:id/configurations/:cid/edit' => ['callback' => 'editConfiguration'],
            '/buildings' => ['callback' => 'listBuildings'],
            '/buildings/:id/edit' => ['callback' => 'editBuilding', ':id' => '[0-9]+|new'],
            '/types' => ['callback' => 'listTypes'],
            '/types/:id/edit' => ['callback' => 'editType', ':id' => '[0-9]+|new'],
            '/rooms/:id/tutorials/upload' => ['callback' => 'uploadImages'],
            '/rooms/:id/files/:fileid/download' => ['callback' => 'downloadImage'],
            '/configurations' => ['callback' => 'listConfigurations'],
            '/configurations/:id' => ['callback' => 'viewConfigurationBundle'],
            '/configurations/:id/edit' => ['callback' => 'editConfigurationBundle'],
            '/schedules' => ['callback' => 'schedules'],
            '/schedules/autocomplete' => ['callback' => 'autoCompleteAccounts'],
        ];
    }

    public function view ()
    {
        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id');
    	$this->addBreadcrumb('rooms', 'List Rooms');
        
        $notes = $this->schema('Classrooms_Notes_Entry');
        
        $facilityId = $location->building->code . '^' . $location->number;
        $trackRoomUrlApi = 'https://track.sfsu.edu/property/rooms?facility=' .$facilityId. '&fields=' . implode(',', self::$Fields);
        
        $this->template->trackUrl = $trackRoomUrlApi;
        $this->template->mode = $this->request->getQueryParameter('mode', 'basic');
        // $this->template->pEdit = true ?? $this->hasPermission('edit room');
        $this->template->pViewDetails = $this->hasPermission('view schedules');
    	$this->template->room = $location;
    	$this->template->allAvEquipment = self::$AllRoomAvEquipment;
        $this->template->notes = $location->id ? $notes->find(
            $notes->path->like($location->getNotePath().'%'), ['orderBy' => '-createdDate']
        ) : [];
    }

    public function listRooms ()
    {
    	$viewer = $this->getAccount();
        
        $schedules = $this->schema('Classrooms_ClassData_CourseSchedule');
        $locations = $this->schema('Classrooms_Room_Location');
        $buildings = $this->schema('Classrooms_Room_Building');
        $buildings = $buildings->find(
            $buildings->deleted->isNull()->orIf($buildings->deleted->isFalse()),
            ['orderBy' => 'name']
        );
        $types = $this->schema('Classrooms_Room_Type');
        $types = $types->find(
            $types->deleted->isNull()->orIf($types->deleted->isFalse()),
            ['orderBy' => 'name']
        );
        $titles = $this->schema('Classrooms_Software_Title');

        $selectedBuildings = $this->request->getQueryParameter('buildings');
        $selectedTypes = $this->request->getQueryParameter('types');
        $selectedTitles = $this->request->getQueryParameter('titles');
        $selectedEquipment = $this->request->getQueryParameter('equipment');
        $capacity = $this->request->getQueryParameter('cap');
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

        $rooms = $locations->find($condition, ['orderBy' => ['building_id', '-number']]);

        $sortedRooms = [];
        foreach ($rooms as $room)
        {
            $sortedRooms[$room->building->code . $room->number] = $room;
        }
        ksort($sortedRooms, SORT_NATURAL);

        $this->template->selectedBuildings = $selectedBuildings;
        $this->template->selectedTypes = $selectedTypes;
        $this->template->selectedTitles = $selectedTitles;
        $this->template->selectedEquipment = $selectedEquipment;
        $this->template->capacity = $capacity;
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
        $restrictResults = $viewer && !$this->hasPermission('edit');
        // $this->requirePermission('edit');
        $scheduleSchema = $this->schema('Classrooms_ClassData_CourseSchedule');

        $semesters = $this->guessRelevantSemesters();
        $userId = $this->request->getQueryParameter('u');
        $termYear = $this->request->getQueryParameter('t', $semesters['curr']['code']);

        $condition = $scheduleSchema->allTrue(
            $scheduleSchema->termYear->equals($termYear),
            $scheduleSchema->userDeleted->isNull()->orIf(
                $scheduleSchema->userDeleted->isFalse()
            ),
            $scheduleSchema->room_id->isNotNull()
        );
        $onlineCourses = null;
        if ($restrictResults)
        {
            $condition = $condition->andIf($scheduleSchema->faculty_id->equals($viewer->username));
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

        $scheduledRooms = [];
        $result = $scheduleSchema->find($condition, ['orderBy' => 'room_id']);
        
        foreach ($result as $schedule)
        {
            $key1 = $schedule->room->building->code . $schedule->room->number;
            if (!isset($scheduledRooms[$key1]))
            {
                $scheduledRooms[$key1] = [
                    'room' => $schedule->room, 
                    'schedules' => []
                ];
            }

            $key2 = $schedule->faculty->lastName . $schedule->faculty->firstName;

            if (!isset($scheduledRooms[$key1]['schedules'][$key2]))
            {
                $scheduledRooms[$key1]['schedules'][$key2] = [];
            }
            $scheduledRooms[$key1]['schedules'][$key2][] = $schedule;
        }
        
        foreach ($scheduledRooms as $key => $sr)
        {
            ksort($sr['schedules'], SORT_NATURAL);
            $scheduledRooms[$key]['schedules'] = $sr['schedules'];
        }
        ksort($scheduledRooms, SORT_NATURAL);

        $this->template->selectedSemester = $this->codeToDisplay($termYear);
        $this->template->scheduledRooms = $scheduledRooms;
        $this->template->semesters = $semesters;
        $this->template->selectedTerm = $termYear;
        $this->template->selectedUser = $userId;
        $this->template->pFaculty = $restrictResults;
        $this->template->onlineCourses = $onlineCourses;
    }

    public function listConfigurations ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

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

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $configData = $this->request->getPostParameters();
                
                    $config->addNote(
                        'Configuration Bundle ' . ($config->id ? 'updated' : 'created'), 
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

        $softwareLicenses = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->id][] = $license;
        }

        $this->template->config = $config;
        $this->template->softwareLicenses = $softwareLicenses;
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
        $notes = $this->schema('Classrooms_Notes_Entry');
        
        $customConfigurations = $location->customConfigurations;
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
                    // echo "<pre>"; var_dump($data['config']); die;
                    
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
                    $location->number = $locationData['number'];
                    $location->description = $locationData['description'];
                    $location->capacity = $locationData['capacity'];
                    $location->scheduledBy = $locationData['scheduledBy'];
                    $location->supportedBy = $locationData['supportedBy'];
                    $location->description = $locationData['description'];
                    $location->url = $locationData['url'];
                    $location->avEquipment = isset($locationData['avEquipment']) ? serialize($locationData['avEquipment']) : '';
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
                        $internal->addNote('New internal note added', $viewer);
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

        $softwareLicenses = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->id][] = $license;
        }
        
        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->customConfigurations = $customConfigurations;
        $this->template->types = $types->getAll();
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomAvEquipment = $location->avEquipment ? unserialize($location->avEquipment) : [];
        $this->template->allAvEquipment = self::$AllRoomAvEquipment;
        $this->template->softwareLicenses = $softwareLicenses;
        $this->template->bundles = $configs->find($configs->isBundle->isTrue(), ['orderBy' => 'model']);
        $this->template->notes = $location->id ? $notes->find(
            $notes->path->like($location->getNotePath().'%'), ['orderBy' => '-createdDate']
        ) : [];
    }

    public function editTutorial () 
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

    	$location = $this->requireExists($this->schema('Classrooms_Room_Location')->get($this->getRouteVariable('roomid')));
    	$tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Tutorial', 'id', ['allowNew' => true]);
    	$notes = $this->schema('Classrooms_Notes_Entry');

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
    				$tutorial->location_id = $location->id;
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
    	$this->template->notes = $tutorial->id ? $notes->find(
            $notes->path->like($tutorial->getNotePath().'%'), ['orderBy' => '-createdDate']
        ) : [];
    }

    public function listBuildings ()
    {
        $this->requirePermission('edit');
        $schema = $this->schema('Classrooms_Room_Building');
        $this->template->buildings = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse())
        );
    }

    public function editBuilding () 
    {
        $this->requirePermission('edit');
        $building = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Building', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('buildings', 'List Buildings');

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

    public function listTypes ()
    {
        $this->requirePermission('edit');
        $schema = $this->schema('Classrooms_Room_Type');
        $this->template->types = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse())
        );
    }

    public function editType () 
    {
        $this->requirePermission('edit');
        $type = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Type', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('types', 'List types');

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    if ($this->processSubmission($type, ['name']))
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

    public function fetchInstructorsRooms ($terms='2213')
    {
        $service = new Classrooms_ClassData_Service($this->getApplication());
        $locations = $this->schema('Classrooms_Room_Location');
        $courseSections = $this->schema('Classrooms_ClassData_CourseSection');
        $locations = $locations->find(
            $locations->deleted->isNull()->orIf($locations->deleted->isFalse()),
            ['orderBy' => ['buildingId', 'number']]
        );

        $instructorsRooms = [];
        foreach ($locations as $location)
        {
            foreach (explode(',', $terms) as $term)
            {
                $instructorsRooms[$term] = [];
                $facilityId = $location->building->code . str_pad($location->number, 4, '0', STR_PAD_LEFT);
                $schedules = $service->getSchedules($term, $facilityId);
                
                foreach ($schedules['courseSchedules']['courses'] as $id => $courseSchedule)
                {
                    $courseSection = $courseSections->get($id);
                    $instructors = $courseSection->getInstructors();

                    foreach ($instructors as $instructor)
                    {
                        if (!isset($instructorsRooms[$term][$instructor->id]))
                        {
                            $instructorsRooms[$term][$instructor->id] = [];
                        }

                        foreach ($courseSchedule as $schedule)
                        {	
                            $instructorsRooms[$term][$instructor->id] = [
                                'course_section_id' => $id,
                                'location_id' => $location->id,
                                'facility_id' => $schedules['courseSchedules']['facility']['id'],
                                'schedule' => array_shift($schedule)
                            ];
                        }                        
                    }
                }
            }
        }

        return $instructorsRooms;
    }

    public function editConfiguration ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $rooms = $this->schema('Classrooms_Software_Title');
        $configs = $this->schema('Classrooms_Software_License');
        $notes = $this->schema('Classrooms_Notes_Entry');
        $room = $rooms->get($this->getRouteVariable('id'));
        $config = $configs->get($this->getRouteVariable('cid'));

        $this->addBreadcrumb('rooms', 'List Rooms');
        $this->addBreadcrumb('rooms/' . $room->id . '/edit', $room->building->name . ' ' . $room->number);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $config->addNote('Configuration updated', $viewer, $this->request->getPostParameters());
                    $config->absorbData($this->request->getPostParameters());
                    $config->save();

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

        $this->template->room = $room;
        $this->template->selectedConfiguration = $config;
        $this->template->notes = $notes->find($notes->path->like($config->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function downloadImage ()
    {   
    	$roomId = $this->getRouteVariable('id');
    	$fileId = $this->getRouteVariable('fileid');
    	$location = $this->schema('Classrooms_Room_Location')->get($roomId);
    	$this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function uploadImages ()
    {
        $viewer = $this->requireLogin();

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
            
                $results = [
                    'message' => 'Your file has been uploaded.',
                    'status' => 200,
                    'success' => true,
                    'file' => [
                        'url' => 'rooms/'.$roomId.'/files/' . $file->id . '/download',
                        'fullUrl' => $this->baseUrl('rooms/'.$roomId.'/files/' . $file->id . '/download'),
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

        $this->template->viewer = $viewer;
    }

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

        if ($role)
        {
            $roleRestrict = $this->schema('Classrooms_AuthN_Role')->get($role);
        }

        $roles = $this->schema('Classrooms_AuthN_Role');
        $adminRole = $roles->findOne($roles->name->equals('Administrator'));

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

            $candidates = $accounts->find($cond, array('orderBy' => array('+lastName', '+firstName'), 'arrayKey' => 'username'));

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
                $options[$candidate->id] = array(
                    'id' => $candidate->id,
                    'firstName' => $candidate->firstName,
                    'lastName' => $candidate->lastName,
                    'username' => $candidate->username,
                );
            }

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

    public function autoComplete ()
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

                $bldgCond = $bldgCond ? $buildings->orIf($attributes) : $attributes;
            }
            else
            {
                $attributes = $buildings->name->lower()->like($pattern)->orIf(
                    $buildings->code->lower()->like($pattern)
                );

                $bldgCond = $bldgCond ? $buildings->orIf($attributes) : $attributes;
            }
        }

        $buildingIds = [];
        if ($bldgCond)
        {
            $buildingIds = $buildings->findValues(['id' => 'id'], $bldgCond);
        }

        if (!empty($buildingIds))
        {
            $condition = $condition->orIf(
                $locations->buildingId->inList($buildingIds)
            );
        }

        $nonDeleted = $locations->deleted->isFalse()->orIf($locations->deleted->isNull());
        $condition = $condition ? $condition->andIf($nonDeleted) : $nonDeleted;
        $rooms = $locations->find($condition, ['orderBy' => 'number']);

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

        if ($month == 12 && $d > 15)
        {
            $year += 1;
            $prev = ($year - 1) . '7';
            $curr = $year . '1';
            $next1 = $year . '3';
            $next2 = $year . '5';
        }
        elseif ($month < 2)
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
