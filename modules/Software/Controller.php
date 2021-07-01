<?php

/**
 * The Software controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Software_Controller extends Classrooms_Master_Controller
{
    public static function getRouteMap ()
    {
        return [
            '/software' => ['callback' => 'listSoftware'],
            '/software/settings' => ['callback' => 'softwareSettings'],
            '/software/:id' => ['callback' => 'view'],
            '/software/:id/edit' => ['callback' => 'editSoftware', ':id' => '[0-9]+|new'],
            '/software/:id/licenses/:lid/edit' => ['callback' => 'editLicense', ':id' => '[0-9]+'],
            '/developers' => ['callback' => 'listDevelopers'],
            '/developers/:id/edit' => ['callback' => 'editDeveloper', ':id' => '[0-9]+|new'],
            '/categories' => ['callback' => 'listCategories'],
            '/categories/:id/edit' => ['callback' => 'editCategory', ':id' => '[0-9]+|new'],
        ];
    }

    public function editSoftware ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $title = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Title', 'id', ['allowNew' => true]);
        $versions = $this->schema('Classrooms_Software_Version');
        $licenses = $this->schema('Classrooms_Software_License');
        $developers = $this->schema('Classrooms_Software_Developer');
        $categories = $this->schema('Classrooms_Software_Category');
        $notes = $this->schema('Classrooms_Notes_Entry');
        
        $this->addBreadcrumb('software', 'List Software Titles');
        $this->addBreadcrumb('software/' . $title->id, 'View');

        $selectedVersion = $title->inDatasource ? 
            $title->versions->index($title->versions->count() - 1) : $versions->createInstance();        
        $selectedLicense = $selectedVersion->inDatasource ? 
            $selectedVersion->licenses->index($selectedVersion->licenses->count() - 1) : $versions->createInstance();
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $data = $this->request->getPostParameters();
                    
                    if (isset($data['developer']['new']) && $developers->findOne($developers->name->equals($data['developer']['new'])))
                    {
                        $developer = $developers->findOne($developers->name->equals($data['developer']['new']));
                    }
                    elseif (isset($data['developer']['new']) && $data['developer']['new'] !== '')
                    {
                        $developer = $developers->createInstance();
                        $developer->name = $data['developer']['new'];
                    }
                    else
                    {
                        $developer = $developers->get($data['developer']['existing']);
                    }
                    $developer->createdDate = $developer->createdDate ?? new DateTime;
                    $developer->modifiedDate = new DateTime;
                    $developer->save();

                    if (isset($data['category']['new']) && $categories->findOne($categories->name->equals($data['category']['new'])))
                    {
                        $category = $categories->findOne($categories->name->equals($data['category']['new']));
                    }
                    elseif (isset($data['category']['new']) && $data['category']['new'] !== '')
                    {
                        $category = $categories->createInstance();
                        $category->name = $data['category']['new'];
                    }
                    else
                    {
                        $category = $categories->get($data['category']['existing']);
                    }
                    // $category->parent_category_id = $data['category']['parent'];
                    $category->createdDate = $category->createdDate ?? new DateTime;
                    $category->modifiedDate = new DateTime;
                    $category->save();
                     
                    $new = (bool) !$title->id;
                    if (!$new && $title->hasDiff($data['title']))
                    {
                        $title->addNote('Software title updated', $viewer, $title->getDiff($data['title']));
                    }
                    $title->developer_id = $developer->id;
                    $title->category_id = $category->id;
                    $title->name = $data['title']['name'];
                    $title->description = $data['title']['description'];
                    $title->createdDate = $title->createdDate ?? new DateTime;
                    $title->modifiedDate = new DateTime;
                    $title->save();
                    if ($new)
                    {
                        $title->addNote('Software title created', $viewer);
                    }
                    
                    $new = false;
                    if (isset($data['version']['new']) && $data['version']['new'] !== '')
                    {
                        $version = $versions->createInstance();
                        $version->number = $data['version']['new'];
                        $version->addNote('Version created for ' . $version->title->name, $viewer);
                    }
                    elseif (isset($data['version']['existing']) && $data['version']['existing'])
                    {
                        $version = $versions->get($data['version']['existing']);
                    }
                    else
                    {
                        $version = $versions->createInstance();
                        $version->number = 'N/A';
                    }
                    $version->title_id = $title->id;
                    $version->save();

                    if (isset($data['license']['new']) && isset($data['license']['new']['number']) && $data['license']['new']['number'] !== '')
                    {
                        $license = $licenses->createInstance();
                        $license->number = $data['license']['new']['number'];
                        $license->description = $data['license']['new']['description'];
                        $license->seats = $data['license']['new']['seats'];
                        $license->expirationDate = isset($data['license']['new']['expirationDate']) ? 
                            new DateTime($data['license']['new']['expirationDate']) : '';
                        $version->addNote('License #'. $license->number .' added to version ' . $version->number, $viewer);
                    }
                    elseif (isset($data['license']['existing']) && $data['license']['existing'])
                    {
                        $license = $licenses->get($data['license']['existing']);
                    }
                    else
                    {
                        $license = $licenses->createInstance();
                        $license->number = 'N/A';
                        $license->expirationDate = null;
                    }
                    $license->version_id = $version->id;
                    $license->createdDate = $license->createdDate ?? new DateTime;
                    $license->modifiedDate = new DateTime;
                    $license->save();                   
                    
                    $this->flash('Software saved.');
                    $this->response->redirect('software/' . $title->id);

                    break;

    			case 'delete':
                    foreach ($title->versions as $version)
                    {
                        foreach ($version->licenses as $license)
                        {
                            $license->deleted = true;
                            $license->save();
                        }
                        $version->deleted = true;
                        $version->save();
                    }
                    $title->deleted = true;
                    $title->save();

                    $this->flash('Software title deleted.');
                    $this->response->redirect('software');
    				break;
            }
        }

        $this->template->title = $title;
        $this->template->selectedVersion = $selectedVersion;
        $this->template->selectedLicense = $selectedLicense;
        $this->template->categories = $categories->getAll(['orderBy' => 'name']);
        $this->template->developers = $developers->getAll(['orderBy' => 'name']);
        $this->template->notes = $title->id ? $notes->find(
            $notes->path->like($title->getNotePath().'%'), ['orderBy' => '-createdDate']
        ) : [];
    }

    public function listDevelopers ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('software/settings', 'Software Settings');
        $schema = $this->schema('Classrooms_Software_Developer');
        $this->template->developers = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse())
        );
    }
    public function editDeveloper () 
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('software/settings', 'Software Settings');
        $this->addBreadcrumb('developers', 'Developers');

        $developer = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Developer', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('developers', 'List developers');

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    if ($this->processSubmission($developer, ['name']))
                    {
                        $developer->save();
                        $this->flash('Developer saved.');
                    }
                    break;

                case 'delete':
                    if ($developer->inDatasource)
                    {
                        $developer->deleted = true;
                        $developer->save();
                    }
                    $this->flash('Developer deleted');
                    break;
            }
            $this->response->redirect('developers');
        }

        $this->template->developer = $developer;
    }

    public function softwareSettings ()
    {
        $this->requirePermission('edit');
    }

    public function listCategories ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('software/settings', 'Software Settings');

        $schema = $this->schema('Classrooms_Software_Category');
        $this->template->categories = $schema->find(
            $schema->deleted->isNull()->orIf($schema->deleted->isFalse())
        );
    }
    public function editCategory () 
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('software/settings', 'Software Settings');
        $this->addBreadcrumb('categories', 'Categories');

        $category = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Category', 'id', ['allowNew' => true]);
        $this->addBreadcrumb('categories', 'List categories');

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    if ($this->processSubmission($category, ['name']))
                    {
                        $category->save();
                        $this->flash('Category saved.');
                    }
                    break;

                case 'delete':
                    if ($category->inDatasource)
                    {
                        $category->deleted = true;
                        $category->save();
                    }
                    $this->flash('Category deleted');
                    break;
            }
            $this->response->redirect('categories');
        }

        $this->template->category = $category;
    }

    public function view ()
    {
        $this->requirePermission('edit');
    	$this->addBreadcrumb('software', 'List Software Titles');
    	$title = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Title', 'id');
        $notes = $this->schema('Classrooms_Notes_Entry');
        
        // $this->template->pEdit = true ?? $this->hasPermission('edit software');
    	$this->template->title = $title;
        $this->template->notes = $notes->find($notes->path->like($title->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function listSoftware ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        $categorySchema = $this->schema('Classrooms_Software_Category');
        $developerSchema = $this->schema('Classrooms_Software_Developer');
        $titles = $this->schema('Classrooms_Software_Title');
        $licenses = $this->schema('Classrooms_Software_License');

        $selectedCategories = $this->request->getQueryParameter('categories', []);
        $selectedDevelopers = $this->request->getQueryParameter('developers', []);

		$condition = $titles->deleted->isFalse()->orIf($titles->deleted->isNull());
        if ($selectedCategories)
        {
            foreach ($selectedCategories as $selected)
            {
                $condition = $condition->andIf($titles->categoryId->equals($selected));
            }
        }
        if ($selectedDevelopers)
        {
        	foreach ($selectedDevelopers as $selected)
            {
                $condition = $condition->andIf($titles->developerId->equals($selected));
            }
        }

        $software = $titles->find($condition, ['orderBy' => ['name', 'createdDate']]);
        if ($expiration = $this->request->getQueryParameter('expiration'))
        {   
            $sorted = [];
            foreach ($software as $software)
            {
                foreach ($software->versions as $version)
                {
                    foreach ($version->licenses as $license)
                    {
                        if ($license->expirationDate <= new DateTime('+' . $expiration))
                        {
                            // $sorted[$license->expirationDate->getTimeStamp()] = $software;
                            $sorted[$software->id] = $license->expirationDate->getTimeStamp();
                        }
                    }
                }
            }
            asort($sorted);
            
            $sortedTitles = [];
            foreach ($sorted as $id => $expiry)
            {
                $sortedTitles[] = $titles->get($id);
            }

            $software = $sortedTitles;
            $condition = $condition ?? true;
        }

        $developers = $developerSchema->find($developerSchema->deleted->isFalse()->orIf($developerSchema->deleted->isNull()), ['orderBy' => 'name']);
        $categories = $categorySchema->find($categorySchema->deleted->isFalse()->orIf($categorySchema->deleted->isNull()), ['orderBy' => 'name']);

        $this->template->selectedCategories = $categorySchema->findValues(['name' => 'id'], 
            $categorySchema->id->inList($selectedCategories)
        );
        $this->template->selectedDevelopers = $developerSchema->findValues(['name' => 'id'], 
            $developerSchema->id->inList($selectedDevelopers)
        );
        $this->template->developers = $developers;
        $this->template->categories = $categories;
        $this->template->titles = $software;
        $this->template->hasFilters = $condition;
        $this->template->expiration = $expiration;
    }

    public function editLicense ()
   	{
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        
   		$software = $this->schema('Classrooms_Software_Title');
   		$licenses = $this->schema('Classrooms_Software_License');
   		$title = $software->get($this->getRouteVariable('id'));
   		$license = $licenses->get($this->getRouteVariable('lid'));
        $notes = $this->schema('Classrooms_Notes_Entry');

   		$this->addBreadcrumb('software', 'List Software Titles');
   		$this->addBreadcrumb('software/' . $title->id . '/edit', $title->developer->name . ' ' . $title->name);

   		if ($this->request->wasPostedByUser())
   		{
   			switch ($this->getPostCommand())
   			{
   				case 'save':
                    if ($license->hasDiff($this->request->getPostParameters()))
                    {
                        $license->version->addNote(
                            'License #'. $license->number .' updated for version '. $license->version->number, 
                            $viewer, 
                            $license->getDiff($this->request->getPostParameters())
                        );
                    }
   					$license->absorbData($this->request->getPostParameters());
   					$license->save();
   					$this->flash('Updated');
   					break;

   				case 'delete':
   					$license->deleted = true;
   					$license->save();
                    $license->version->addNote('License #'. $license->number .' deleted from version '. $license->version->number, $viewer);

   					$this->flash('Deleted');
   					break;
   			}

   			$this->response->redirect('software/' . $title->id . '/edit');
   		}

   		$this->template->title = $title;
   		$this->template->license = $license;
        $this->template->notes = $notes->find($notes->path->like($license->version->getNotePath().'%'), ['orderBy' => '-createdDate']);
   	}
}
