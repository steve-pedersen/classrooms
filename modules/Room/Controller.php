<?php

/**
 * The Rooms controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_Controller extends Classrooms_Master_Controller
{
    public static $AllRoomFacets = [
        'lcd_proj'=>'LCD Proj', 'lcd_tv'=>'LCD TV', 'vcr_dvd'=>'VCR/DVD', 'hdmi'=>'HDMI', 'vga'=>'VGA', 'data'=>'Data',
        'scr'=>'Scr', 'mic'=>'Mic', 'coursestream'=>'CourseStream', 'doc_cam'=>'Doc Cam'
    ];

    public static function getRouteMap ()
    {
        return [
            '/rooms' => ['callback' => 'listRooms'],
            '/rooms/:id' => ['callback' => 'view'],
            '/rooms/:id/edit' => ['callback' => 'editRoom', ':id' => '[0-9]+|new'],
            '/rooms/:roomid/tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            '/buildings/:id/edit' => ['callback' => 'editBuilding', ':id' => '[0-9]+|new'],
            '/types/:id/edit' => ['callback' => 'editType', ':id' => '[0-9]+|new'],
            '/rooms/:id/tutorials/upload' => ['callback' => 'uploadImages'],
        ];
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
                        'url' => 'files/' . $file->id . '/download',
                        'fullUrl' => $this->baseUrl('files/' . $file->id . '/download'),
                        'name' => $file->remoteName,
                    ],
                    'fileSrc' => 'files/' . $file->id . '/download',
                    'fileName' => $file->remoteName,
                    'fid' => $file->id,
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
 
    public function editRoom ()
    {
    	$this->addBreadcrumb('rooms', 'List Rooms');

        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id', ['allowNew' => true]);
        $configs = $this->schema('Classrooms_Room_Configuration');
        $types = $this->schema('Classrooms_Room_Type');
        $buildings = $this->schema('Classrooms_Room_Building');
        $licenses = $this->schema('Classrooms_Software_License');

        $selectedConfiguration = $location->id ? 
            $this->request->getQueryParameter('configuration', $location->configurations->index(0)) : $configs->createInstance();
        
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

                    $locationData = $data['room'];
                    $location->building_id = $locationData['building'];
                    $location->type_id = $locationData['type'];
                    $location->number = $locationData['number'];
                    $location->description = $locationData['description'];
                    $location->capacity = $locationData['capacity'];
                    $location->description = $locationData['description'];
                    $location->url = $locationData['url'];
                    $location->facets = isset($locationData['facets']) ? serialize($locationData['facets']) : '';
                    $location->createdDate = $location->createdDate ?? new DateTime;
                    $location->modifiedDate = new DateTime;
                    $location->save();

                    if (isset($data['config']['new']['model']) && $data['config']['new']['model'] !== '')
                    {
                        $configData = $data['config']['new'];
                        $config = $configs->createInstance();
                    }
                    else
                    {
                        $configData = $data['config']['existing'];
                        $config = $selectedConfiguration;
                    }
                    $config->absorbData($configData);
                    $config->location = $configData['location'];
                    $config->adBound = isset($configData['adBound']);
                    $config->location_id = $location->id;
                    $config->createdDate = $config->createdDate ?? new DateTime;
                    $config->modifiedDate = new DateTime;
                    $config->save();
                    
                    foreach ($configData['licenses'] as $licenseId => $on)
                    {   
                        $license = $licenses->get($licenseId);
                        // $license->roomConfigurations->add($config);
                        // $license->roomConfigurations->setProperty($config, 'title_id', $license->version->title_id);
                        // $license->roomConfigurations->save();
                        // $license->save();

                        $config->softwareLicenses->add($license);
                        // $config->softwareLicenses->setProperty($license, 'title_id', $license->version->title_id);
                        $config->softwareLicenses->save();
                        $config->save();
                    }

                    // echo "<pre>"; var_dump($licenseData); die;
                    
                    
                    $this->flash('Room saved.');
                    $this->response->redirect('rooms/' . $location->id);

                    break;

    			case 'delete':

    				break;
            }
        }

        $softwareTitles = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareTitles[$license->version->title->id]))
            {
                $softwareTitles[$license->version->title->id] = [];
            }
            $softwareTitles[$license->version->title->id][] = $license;
        }


        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->types = $types->getAll();
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomFacets = $location->facets ? unserialize($location->facets) : [];
        $this->template->allFacets = self::$AllRoomFacets;
        $this->template->softwareTitles = $softwareTitles;
    }

    public function editTutorial () 
    {
    	$location = $this->requireExists($this->schema('Classrooms_Room_Location')->get($this->getRouteVariable('roomid')));
    	$tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Tutorial', 'id', ['allowNew' => true]);

    	if ($this->request->wasPostedByUser())
    	{
    		switch ($this->getPostCommand())
    		{
    			case 'save':
    				$tutorial->location_id = $location->id;
    				$tutorial->name = $this->request->getPostParameter('name');
                    $tutorial->headerImageUrl = $this->request->getPostParameter('headerImageUrl');
    				$tutorial->description = $this->request->getPostParameter('description');
    				$tutorial->createdDate = $tutorial->createdDate ?? new DateTime;
    				$tutorial->modifiedDate = new DateTime;
    				$tutorial->save();

    				$this->flash('Tutorial saved for room '. $location->codeName);
    				$this->response->redirect('rooms/' . $location->id);

    				break;

    			case 'delete':

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
    }

    public function editBuilding () {}
    public function editType () {}

    public function view ()
    {
    	$this->addBreadcrumb('rooms', 'List Rooms');

    	$location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id');
    	
    	$this->template->room = $location;
    	$this->template->allFacets = self::$AllRoomFacets;
    }

    public function listRooms ()
    {
        $locations = $this->schema('Classrooms_Room_Location');
        $buildings = $this->schema('Classrooms_Room_Building')->getAll(['orderBy' => 'name']);
        $types = $this->schema('Classrooms_Room_Type')->getAll(['orderBy' => 'name']);

        $selectedBuilding = $this->request->getQueryParameter('building');
        $selectedType = $this->request->getQueryParameter('type');

		$condition = null;        
        if ($selectedBuilding && $selectedType)
        {
        	$condition = $locations->buildingId->equals($selectedBuilding)->andIf($locations->typeId->equals($selectedType));
        }
        elseif ($selectedBuilding)
        {
        	$condition = $locations->buildingId->equals($selectedBuilding);
        }
        elseif ($selectedType)
        {
        	$condition = $locations->typeId->equals($selectedType);
        }

        $rooms = $locations->find($condition, ['orderBy' => 'number']);


        $this->template->selectedBuilding = $selectedBuilding;
        $this->template->selectedType = $selectedType;
        $this->template->buildings = $buildings;
        $this->template->types = $types;
        $this->template->rooms = $rooms;
        $this->template->allFacets = self::$AllRoomFacets;
        $this->template->hasFilters = $condition;
    }

}
