<?php

/**
 * The Rooms controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_Controller extends Classrooms_Master_Controller
{
    public static function getRouteMap ()
    {
        return [
            '/rooms' => ['callback' => 'listRooms'],
            '/rooms/:id' => ['callback' => 'view'],
            '/rooms/:id/edit' => ['callback' => 'editRoom', ':id' => '[0-9]+|new'],
            '/tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            '/buildings/:id/edit' => ['callback' => 'editBuilding', ':id' => '[0-9]+|new'],
            '/types/:id/edit' => ['callback' => 'editType', ':id' => '[0-9]+|new'],
        ];
    }
 
    public function editRoom ()
    {
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
                    // echo "<pre>"; var_dump($data); die;
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
            }
        }

        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->types = $types->getAll();
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomFacets = $location->facets ? unserialize($location->facets) : [];
        $this->template->allFacets = $location::AllRoomFacets();
    }

    public function editTutorial () {}
    public function editBuilding () {}
    public function editType () {}

    public function view ()
    {

    }

    public function listRooms ()
    {
        $locations = $this->schema('Classrooms_Room_Location');
        $buildings = $this->schema('Classrooms_Room_Building')->getAll(['orderBy' => 'name']);
        $types = $this->schema('Classrooms_Room_Type')->getAll(['orderBy' => 'name']);

        $selectedBuilding = $this->request->getQueryParameter('building');
        $selectedType = $this->request->getQueryParameter('type');
        // echo "<pre>"; var_dump($selectedBuilding); die;
        
        if ($selectedBuilding && $selectedType)
        {
            $rooms = $locations->find(
                $locations->buildingId->equals($selectedBuilding)->andIf($locations->typeId->equals($selectedType)), 
                ['orderBy' => 'number']
            );
        }
        elseif ($selectedBuilding)
        {
            $rooms = $locations->find($locations->buildingId->equals($selectedBuilding), ['orderBy' => 'number']);
        }
        elseif ($selectedType)
        {
            $rooms = $locations->find($locations->typeId->equals($selectedType), ['orderBy' => 'number']);
        }
        else
        {
            $rooms = $locations->getAll(['orderBy' => 'number']);
        }

        // echo "<pre>"; var_dump($selectedBuilding, $selectedType); die;
        // echo "<pre>"; var_dump($rooms); die;
        

        $this->template->selectedBuilding = $selectedBuilding;
        $this->template->selectedType = $selectedType;
        $this->template->buildings = $buildings;
        $this->template->types = $types;
        $this->template->rooms = $rooms;
        $this->template->allFacets = $locations->createInstance()::AllRoomFacets();
    }

}
