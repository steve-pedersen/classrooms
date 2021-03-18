<?php

/**
 * Administrate departments and users
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Department_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            '/departments' => ['callback' => 'list'],
            '/departments/sync' => ['callback' => 'sync'],
            '/departments/:id' => ['callback' => 'view', ':id' => '[0-9]+'],
            '/departments/:id/edit' => ['callback' => 'edit', ':id' => '[0-9]+|new'],
        );
    }

	public function beforeCallback ($callback)
	{
		parent::beforeCallback($callback);
		$this->requirePermission('admin');
	}
    
    public function list ()
    {
    	$this->template->departments = $this->schema('Classrooms_Department_Department')->getAll(['orderBy' => 'name']);
    }

    public function view ()
    {
    	$this->addBreadcrumb('departments', 'List Departments');
    	$department = $this->helper('activeRecord')->fromRoute('Classrooms_Department_Department', 'id');
        $notes = $this->schema('Classrooms_Notes_Entry');
    	
    	$this->template->department = $department;
        $this->template->notes = $notes->find($notes->path->like($department->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }


    public function edit ()
    {
    	$this->requireLogin();
    	$department = $this->helper('activeRecord')->fromRoute('Classrooms_Department_Department', 'id');
    	
    	$this->addBreadcrumb('departments', 'List Departments');
    	$this->addBreadcrumb('departments/' . $department->id, 'List Departments');

    	if ($this->request->wasPostedByUser())
    	{
    		switch ($this->getPostCommand())
    		{
    			case 'save':
    				$department->name = $this->request->getPostParameter('name');
    				$department->code = $this->request->getPostParameter('code');
    				$department->save();

    				if ($this->request->getPostParameter('sfStateId'))
    				{
    					$user = $this->schema('Classrooms_Department_User')->createInstance();
    					$user->sfStateId = $this->request->getPostParameter('sfStateId');
    					$user->emailAddress = $this->request->getPostParameter('emailAddress');
    					$user->firstName = $this->request->getPostParameter('firstName');
    					$user->lastName = $this->request->getPostParameter('lastName');
    					$user->position = $this->request->getPostParameter('position');
    					$user->save();

    					$department->users->add($user);
    					$department->users->save();
    				}


    				$this->flash('Department updated.');
    				$this->response->redirect('departments/' . $department->id);
    				break;
    		}
    	}

    	$this->template->department = $department;
    }

    public function sync ()
    {
        $viewer = $this->requireLogin();
        $importer = new Classrooms_ClassData_Importer($this->getApplication());
        list($count, $added, $removed) = $importer->syncDepartments();

    	$this->flash($count.' departments synchronized. '.$added.' total personnel added. '. $removed.' total personnel removed.');
    	$this->response->redirect('departments');
    }
}
