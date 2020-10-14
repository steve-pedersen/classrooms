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
    	
    	$this->template->department = $department;
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
    	$schema = $this->schema('Classrooms_Department_Department');
    	$service = new At_ClassData_Service($this->getApplication());
    	$departments = $service->getDepartments()['departments'];
    	ksort($departments);

    	$count = 0;
    	foreach ($departments as $department => $temp)
    	{
    		if (!$schema->findOne($schema->name->equals($department)))
    		{
	    		$dept = $schema->createInstance();
	    		$dept->name = $department;
	    		$dept->createdDate = new DateTime;
	    		$dept->save();
	    		$count++;
    		}
    	}

    	$this->flash($count . ' departments updated');
    	$this->response->redirect('departments');
    }
}
