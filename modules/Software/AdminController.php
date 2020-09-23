<?php

/**
 * Administrate accounts, roles, and access levels.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return [
            'admin/software/categories' 					=> ['callback' => 'listCategories'],
            'admin/software/categories/:id' 				=> ['callback' => 'editCategory', ':id' => '([0-9]+|new)'],
            'admin/software/categories/:id/delete' 			=> ['callback' => 'deleteCategory', ':id' => '([0-9]+|new)'],
            'admin/software/developers' 					=> ['callback' => 'listDevelopers'],
            'admin/software/developers/:id' 				=> ['callback' => 'editDeveloper', ':id' => '([0-9]+|new)'],
            'admin/software/developers/:id/delete' 			=> ['callback' => 'deleteDeveloper', ':id' => '([0-9]+|new)'],
            'admin/software/titles' 						=> ['callback' => 'listTitles'],
            'admin/software/titles/:id' 					=> ['callback' => 'editTitle', ':id' => '([0-9]+|new)'],
            'admin/software/titles/:id/delete' 				=> ['callback' => 'deleteTitle', ':id' => '[0-9]+'],
            'admin/software/titles/:tid/versions' 			=> ['callback' => 'listVersions'],
            'admin/software/titles/:tid/versions/:vid' 		=> ['callback' => 'editVersion', ':tid' => '[0-9]+', ':vid' => '([0-9]+|new)'],
            'admin/software/titles/:tid/versions/:vid/delete' => ['callback' => 'deleteVersion', ':tid' => '[0-9]+', ':vid' => '[0-9]+'],
        ];
    }

	public function beforeCallback ($callback)
	{
		parent::beforeCallback($callback);
		$this->requirePermission('admin');
	}
    
    
    /**
     */
    public function listRoles ()
    {
		$showAll = $this->getRouteVariable('showAll');
		
		$roles = $this->schema('Classrooms_AuthN_Role');
		$accessLevels = $this->schema('Classrooms_AuthN_AccessLevel');
		
		if ($showAll)
		{
			$this->template->showAll = true;
			$this->template->roleList = $roles->getAll(array('orderBy' => array('+name', '+id')));
		}
		else
		{
			$this->template->roleList = $roles->find($roles->isSystemRole->equals(true), array('orderBy' => array('+name', '+id')));
		}
        
		$this->setPageTitle('Roles and access levels');
        $this->template->accessLevelList = $accessLevels->getAll(array('orderBy' => array('+name', '+id')));
    }

	/**
	 */
	public function editRole ()
	{
		$id = $this->getRouteVariable('id');
		$roles = $this->schema('Classrooms_AuthN_Role');
		
		if ($id == 'new')
		{
			$role = $roles->createInstance();
			$this->setPageTitle('Add new role');
		}
		else
		{
			$role = $roles->get($id);
			
			if ($role == null)
			{
				$this->notFound(array(
					array('href' => 'admin/roles', 'text' => 'Roles and access levels'),
					array('href' => 'admin', 'text' => 'Admin dashboard'),
				));
			}
			
			$this->setPageTitle('Edit role &ldquo;' . htmlspecialchars($role->name) . '&rdquo;');
		}
		
		$authZ = $this->getAuthorizationManager();
		$accessLevels = $this->schema('Classrooms_AuthN_AccessLevel');
		$this->template->accessLevelList = $accessLevelList = $accessLevels->getAll(array('orderBy' => array('+name', '+id')));
		$this->template->taskDefinitionMap = $authZ->getDefinedTasks();
		$this->template->systemAzid = Bss_AuthZ_Manager::SYSTEM_ENTITY;
		
		if (($postCommand = $this->getPostCommand()))
		{
			// Either save or apply.
			$successful = $this->processSubmission($role, array('name', 'description', 'isSystemRole'));
			$role->save();
			$hash = null;
			
			// Add a task.
			$addTask = $this->request->getPostParameter('addTask');
			$addTarget = $this->request->getPostParameter('addTarget');
			
			if ($addTask && $addTarget)
			{
				if ($addTarget != 'system')
				{
					$addTarget = 'at:classrooms:authN/AccessLevel/' . $addTarget;
				}
				$authZ->grantPermission($role, $addTask, $addTarget);
				$hash = 'perms';
			}
			
			// Remove selected tasks.
			$selTaskMap = (array) $this->request->getPostParameter('task');
			
			foreach ($selTaskMap as $task => $entitySet)
			{
				if (is_array($entitySet))
				{
					foreach ($entitySet as $entityId => $nonce)
					{
						if ($entityId != 'system')
						{
							$entityId = 'at:classrooms:authN/AccessLevel/' . $entityId;
						}
                        
						$authZ->revokePermission($role, $task, $entityId);
						$hash = 'perms';
					}
				}
			}
			
			// TODO: IP assignments.
			
			if ($postCommand == 'apply' && ($id == 'new' || $hash))
			{
				$this->response->redirect('admin/roles/' . $role->id . ($hash ? '#' . $hash : ''));
			}
			elseif ($postCommand == 'save')
			{
				$this->response->redirect('admin/roles');
			}
		}
		
		if ($role->inDataSource)
		{
			$entityList = array(
				array('id' => 'system', 'name' => 'System', 'permissionList' => $authZ->getPermissions($role, Bss_AuthZ_Manager::SYSTEM_ENTITY)),
			);
		
			foreach ($accessLevelList as $accessLevel)
			{
				$entityList[] = array(
					'id' => $accessLevel->id,
					'name' => $accessLevel->name . ' access',
					'permissionList' => $authZ->getPermissions($role, $accessLevel),
				);
			}
			
			$this->template->entityList = $entityList;
			$this->template->authZ = $authZ;
		}
		
		$this->template->role = $role;
	}
    
    public function deleteRole ()
    {
        $id = $this->getRouteVariable('id');
        $roles = $this->schema('Classrooms_AuthN_Role');
        $role = $roles->get($id);
        
        if ($this->getPostCommand() && $this->request->wasPostedByUser())
        {
            // Delete the role from users -- we do this without loading the accounts.
            $role->getSchema()->accounts->remove($role);
			$this->getAuthorizationManager()->deprovision($role);
            
            // TODO: Allow reassigning users in this role to a new role.
            
            $role->delete();
            $this->response->redirect('admin/roles');
        }
        
        $this->template->role = $role;
    }

	public function editAccessLevel ()
	{
		$accessLevel = $this->helper('activeRecord')->fromRoute('Classrooms_AuthN_AccessLevel', 'id', array('allowNew' => true));
		$create = !$accessLevel->inDataSource;

		if ($create)
		{
			$this->setPageTitle('Add access level');
		}
		else
		{
			$this->setPageTitle('Edit access level &ldquo;' . htmlspecialchars($accessLevel->name) . '&rdquo;');

		}

		if ($this->request->wasPostedByUser())
		{
			switch ($this->getPostCommand()) {
				case 'save':

					$this->processSubmission($accessLevel, array('name', 'description'));
					$accessLevel->save();
					$this->flash('Access level ' . ($create ? 'added' : 'saved'));
					$this->response->redirect('admin/roles');
					break;
			}
		}
		
		$this->template->accessLevel = $accessLevel;
	}
	
	public function deleteAccessLevel ()
	{
		$accessLevel = $this->helper('activeRecord')->fromRoute('Classrooms_AuthN_AccessLevel', 'id');
		
		if ($accessLevel == null)
		{
			$this->notFound(array(
				array('href' => 'admin/roles', 'text' => 'Roles and access levels'),
				array('href' => 'admin', 'text' => 'Admin dashboard'),
			));
		}
		
		$this->setPageTitle('Delete access level &ldquo;' . htmlspecialchars($accessLevel->name) . '&rdquo;?');
		
		if ($this->getPostCommand() && $this->request->wasPostedByUser())
		{
			$this->getAuthorizationManager()->deprovision($accessLevel);
			$accessLevel->delete();
			$this->response->redirect('admin/roles');
		}
		
		$this->template->accessLevel = $accessLevel;
	}
}
