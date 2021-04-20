<?php

/**
 */
class Classrooms_Room_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return [
            '/admin/rooms/defaults' => ['callback' => 'setDefaults'],
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
}