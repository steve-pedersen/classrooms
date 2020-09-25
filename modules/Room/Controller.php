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
        ];
    }
 
    public function editRoom ()
    {
    	$this->addBreadcrumb('rooms', 'List Rooms');

        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id', ['allowNew' => true]);
        $configs = $this->schema('Classrooms_Room_Configuration');
        $types = $this->schema('Classrooms_Room_Type');
        $buildings = $this->schema('Classrooms_Room_Building');

        $selectedConfiguration = $location->id ? 
            $this->request->getQueryParameter('configuration', $location->configurations->index(0)) : $configs->createInstance();
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $data = $this->request->getPostParameters();

                    $locationData = $data['room'];
                    $location->building_id = $locationData['building'];
                    $location->type_id = $locationData['type'];
                    $location->number = $locationData['number'];
                    $location->description = $locationData['description'];
                    $location->capacity = $locationData['capacity'];
                    $location->description = $locationData['description'];
                    $location->url = $locationData['url'];
                    $location->facets = serialize($locationData['facets']);
                    $location->createdDate = $location->createdDate ?? new DateTime;
                    $location->modifiedDate = new DateTime;
                    $location->save();

                    $configData = $data['config'];
                    $selectedConfiguration->absorbData($configData);
                    $selectedConfiguration->location_id = $location->id;
                    $selectedConfiguration->location = $configData['configLocation'];
                    $selectedConfiguration->adBound = isset($configData['adBound']);
                    $selectedConfiguration->save();
                    
                    $this->flash('Room saved.');
                    $this->response->redirect('rooms');

                    break;

    			case 'delete':

    				break;
            }
        }

        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->types = $types->getAll();
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomFacets = $location->facets ? unserialize($location->facets) : [];
        $this->template->allFacets = self::$AllRoomFacets;
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
