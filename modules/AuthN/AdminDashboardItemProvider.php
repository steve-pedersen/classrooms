<?php

/**
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_AuthN_AdminDashboardItemProvider extends At_Admin_DashboardItemProvider
{
    public function getSections (Bss_Master_UserContext $userContext)
    {
        return array();
    }
    
    public function getItems (Bss_Master_UserContext $userContext)
    {
        return array(
            'roles' => array(
                'section' => 'Accounts',
                'order' => 5,
                'text' => 'Manage Roles and Access Levels',
                'href' => 'admin/roles',
            ),
            
        );
    }
}
