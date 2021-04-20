<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_AdminDashboardItemProvider extends At_Admin_DashboardItemProvider
{
    public function getSections (Bss_Master_UserContext $userContext)
    {
        return array(
            'Room Settings' => array('order' => 1),
        );
    }
    
    public function getItems (Bss_Master_UserContext $userContext)
    {
        return array(
            'room-default-text' => array(
                'section' => 'Room Settings',
                'order' => 1,
                'text' => 'Default room description',
                'href' => 'admin/rooms/defaults',
            ),
        );
    }
}
