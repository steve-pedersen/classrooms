<?php

/**
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Communication_AdminDashboardItemProvider extends At_Admin_DashboardItemProvider
{
    public function getSections (Bss_Master_UserContext $userContext)
    {
        return array(
            'Email Communications' => array('order' => 5),
        );
    }
    
    public function getItems (Bss_Master_UserContext $userContext)
    {
        return array(
            'communications' => array(
                'section' => 'Email Communications',
                'order' => 1,
                'text' => 'Manage Email Communications',
                'href' => 'admin/communications',
            ),
        );
    }
}
