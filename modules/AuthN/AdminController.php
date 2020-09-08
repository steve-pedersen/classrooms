<?php

/**
 * Administrate accounts, roles, and access levels.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_AuthN_AdminController extends At_Admin_Controller
{
    public static function getRouteMap ()
    {
        return array(
            'admin/roles' => array('callback' => 'listRoles'),
			'admin/roles/all' => array('callback' => 'listRoles', 'showAll' => true),
            'admin/roles/:id' => array('callback' => 'editRole', ':id' => '([0-9]+|new)'),
            'admin/roles/:id/delete' => array('callback' => 'deleteRole', ':id' => '[0-9]+'),
            'admin/levels/:id' => array('callback' => 'editAccessLevel', ':id' => '([0-9]+|new)'),
			'admin/levels/:id/delete' => array('callback' => 'deleteAccessLevel', ':id' => '[0-9]+'),
        );
    }

	public function beforeCallback ($callback)
	{
		parent::beforeCallback($callback);
		$this->requirePermission('admin');
	}
    
    private function getQueryString ($merge = null)
    {
		$qsa = array(
            'page' => $this->request->getQueryParameter('page', 1),
            'limit' => $this->request->getQueryParameter('limit', 20),
            'sq' => $this->request->getQueryParameter('sq'),
            'sort' => $this->request->getQueryParameter('sort', 'name'),
            'dir' => $this->request->getQueryParameter('dir', 'asc'),
        );
		
		if ($merge)
		{
			foreach ($merge as $k => $v)
			{
				if ($v !== null)
				{
					$qsa[$k] = $v;
				}
				elseif (isset($qsa[$k]))
				{
					unset($qsa[$k]);
				}
			}
		}
		
		if (!empty($qsa))
		{
			$qsaString = '';
			$first = true;
			
			foreach ($qsa as $k => $v)
			{
				$qsaString .= ($first ? '?' : '&') . urlencode($k) . '=' . urlencode($v);
				$first = false;
			}
			
			return $qsaString;
		}
		
		return '';
    }
    
    private function getPagesAroundCurrent ($currentPage, $pageCount)
    {
		$pageList = array();
		
        if ($pageCount > 0)
        {
    		$minPage = max(1, $currentPage - 5);
    		$maxPage = min($pageCount, $currentPage + 5);
    		
    		if ($pageCount != 1)
    		{
    			$pageList[] = array(
    				'page' => $currentPage-1,
    				'display' => 'Previous',
    				'disabled' => ($currentPage == 1),
    				'href' => 'admin/accounts' . $this->getQueryString(array('page' => $currentPage-1)),
    			);
    		}
    		
    		if ($minPage > 1)
    		{
    			$pageList[] = array(
    				'page' => 1,
    				'display' => 'First',
    				'current' => false,
    				'href' => 'admin/accounts' . $this->getQueryString(array('page' => 1)),
    			);
    			
    			if ($minPage > 2)
    			{
    				$pageList[] = array('separator' => true);
    			}
    		}
    		
    		for ($page = $minPage; $page <= $maxPage; $page++)
    		{
    			$current = ($page == $currentPage);
    			
    			$pageList[] = array(
    				'page' => $page,
    				'display' => $page,
    				'current' => $current,
    				'href' => 'admin/accounts' . $this->getQueryString(array('page' => $page)),
    			);
    		}
    		
    		if ($maxPage < $pageCount)
    		{
    			if ($maxPage+1 < $pageCount)
    			{
    				$pageList[] = array('separator' => true);
    			}
    			
    			$pageList[] = array(
    				'page' => $pageCount,
    				'display' => 'Last',
    				'current' => false,
    				'href' => 'admin/accounts' . $this->getQueryString(array('page' => $pageCount)),
    			);
    		}
    		
    		if ($pageCount != 1)
    		{
    			$pageList[] = array(
    				'page' => $currentPage+1,
    				'display' => 'Next',
    				'disabled' => ($currentPage == $pageCount),
    				'href' => 'admin/accounts' . $this->getQueryString(array('page' => $currentPage+1)),
    			);
    		}
        }
		
		return $pageList;
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
