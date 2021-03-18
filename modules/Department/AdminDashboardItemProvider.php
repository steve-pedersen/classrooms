<?php

/**
 */
class Classrooms_Department_AdminDashboardItemProvider extends At_Admin_DashboardItemProvider
{
    public function getSections (Bss_Master_UserContext $userContext)
    {
        return array(
            'Services' => array(
                'order' => 1,
            ),
        );
    }
    
    public function getItems (Bss_Master_UserContext $userContext)
    {
        return array(
            'view-departments' => array(
                'section' => 'Services',
                'order' => 0,
                'href' => 'departments',
                'text' => 'Departments & Personnel',
            ),
        );
    }
}
