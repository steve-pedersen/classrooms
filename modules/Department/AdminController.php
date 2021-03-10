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
    	$schema = $this->schema('Classrooms_Department_Department');
        $users = $this->schema('Classrooms_Department_User');
    	
        $service = new Classrooms_ClassData_Service($this->getApplication());
    	$departments = $service->getDepartments()[1]['departments'];
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

        $personnel = $service->getPersonnel()[1]['personnel'];
        ksort($personnel);
        $personnelChanges = [];
        
        foreach ($personnel['colleges'] as $cid => $college)
        {
            foreach ($college['departments'] as $dept)
            {   
                $department = $schema->findOne($schema->name->equals($dept['name']));
                $personnelChanges[$department->name] = ['add' => [], 'remove' => []];
                if (!empty($dept['people']))
                {
                    foreach ($dept['people'] as $id => $person)
                    {
                        if (!($user = $users->findOne($users->sfStateId->equals($id))))
                        {
                            $user = $users->createInstance();
                            $user->firstName = $person['firstName'];
                            $user->lastName = $person['lastName'];
                            $user->position = $person['role'];
                            $user->sfStateId = $id;
                            $user->createdDate = new DateTime;
                        }
                        $user->modifiedDate = $user->deleted && $user->modifiedDate ? new DateTime : $user->modifiedDate;
                        $user->deleted = false;
                        $user->save();

                        if (!$department->users->has($user))
                        {
                            $department->users->add($user);
                            $department->users->save();
                            $personnelChanges[$department->name]['add'][] = $user->sfStateId;
                            $user->addNote($user->sfStateId . ' added to ' . $department->name, $viewer);
                        }
                    }
                }

                // remove as necessary
                foreach ($department->users as $user)
                {   
                    if (!in_array($user->sfStateId, array_keys($dept['people'])))
                    {   
                        $department->users->remove($user);
                        $department->users->save();
                        $user->deleted = true;
                        $user->save();

                        $personnelChanges[$department->name]['remove'][] = $user->sfStateId;
                    }
                }
            }
        }

        $added = 0;
        $removed = 0;
        foreach ($personnelChanges as $departmentName => $changes)
        {
            $department = $schema->findOne($schema->name->equals($departmentName));
            if (!empty($changes['add']))
            {
                $department->addNote(count($changes['add']) . ' personnel added', $viewer, ['new' => $changes['add']]);
                $added += count($changes['add']);
            }
            if (!empty($changes['remove']))
            {
                $department->addNote(count($changes['remove']) . ' personnel removed', $viewer, ['new' => $changes['remove']]);
                $removed += count($changes['remove']);
            }
        }


    	$this->flash($count.' departments updated. '.$added.' total personnel added. '. $removed.' total personnel removed.');
    	$this->response->redirect('departments');
    }
}
