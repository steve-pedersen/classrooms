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
            '/software/:id' => ['callback' => 'view'],
            '/software/:id/edit' => ['callback' => 'editSoftware', ':id' => '[0-9]+|new'],
            '/developers/:id/edit' => ['callback' => 'editDeveloper', ':id' => '[0-9]+|new'],
            '/categories/:id/edit' => ['callback' => 'editCategory', ':id' => '[0-9]+|new'],
        ];
    }
 
    public function editSoftware ()
    {
    	$this->addBreadcrumb('software', 'List Software Titles');

        $title = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Title', 'id', ['allowNew' => true]);
        $versions = $this->schema('Classrooms_Software_Version');
        $licenses = $this->schema('Classrooms_Software_License');
        $developers = $this->schema('Classrooms_Software_Developer');
        $categories = $this->schema('Classrooms_Software_Category');
        
        $selectedVersion = $title->id ? 
            $title->versions->index($title->versions->count() - 1) : $versions->createInstance();        
        $selectedLicense = $selectedVersion->id ? 
            $selectedVersion->licenses->index($selectedVersion->licenses->count() - 1) : $versions->createInstance();
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $data = $this->request->getPostParameters();
                    // echo "<pre>"; var_dump($data); die;
                    
                    if (isset($data['developer']['new']) && $data['developer']['new'] !== '')
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

                    if (isset($data['category']['new']) && $data['category']['new'] !== '')
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
                     
                    $title->developer_id = $developer->id;
                    $title->category_id = $category->id;
                    $title->name = $data['title']['name'];
                    $title->description = $data['title']['description'];
                    $title->createdDate = $title->createdDate ?? new DateTime;
                    $title->modifiedDate = new DateTime;
                    $title->save();

                    if (isset($data['version']['new']) && $data['version']['new'] !== '')
                    {
                        $version = $versions->createInstance();
                        $version->number = $data['version']['new'];
                    }
                    else
                    {
                        $version = $versions->get($data['version']['existing']);
                    }
                    $version->title_id = $title->id;
                    $version->save();

                    if (isset($data['license']['new']) && isset($data['license']['new']['number']) && $data['license']['new']['number'] !== '')
                    {
                        $license = $licenses->createInstance();
                        $license->number = $data['license']['new']['number'];
                        $license->description = $data['license']['new']['description'];
                        $license->seats = $data['license']['new']['seats'];
                        $license->expirationDate = $data['license']['new']['expirationDate'];
                    }
                    else
                    {
                        $license = $licenses->get($data['license']['existing']);
                    }
                    $license->version_id = $version->id;
                    $license->createdDate = $license->createdDate ?? new DateTime;
                    $license->modifiedDate = new DateTime;
                    $license->save();                   
                    
                    $this->flash('Software saved.');
                    $this->response->redirect('software/' . $title->id);

                    break;

    			case 'delete':

    				break;
            }
        }

        $this->template->title = $title;
        $this->template->selectedVersion = $selectedVersion;
        $this->template->selectedLicense = $selectedLicense;
        $this->template->categories = $categories->getAll();
        $this->template->developers = $developers->getAll();
    }

    public function editDeveloper () {}
    public function editCategory () {}

    public function view ()
    {
    	$this->addBreadcrumb('software', 'List Software Titles');
    	$title = $this->helper('activeRecord')->fromRoute('Classrooms_Software_Title', 'id');
        
    	$this->template->title = $title;
    }

    public function listSoftware ()
    {
        $categories = $this->schema('Classrooms_Software_Category');
        $developers = $this->schema('Classrooms_Software_Developer');
        $titles = $this->schema('Classrooms_Software_Title');

        $selectedCategory = $this->request->getQueryParameter('category');
        $selectedDeveloper = $this->request->getQueryParameter('developer');

		$condition = null;        
        if ($selectedCategory && $selectedDeveloper)
        {
        	$condition = $titles->categoryId->equals($selectedDeveloper)->andIf(
                $titles->developerId->equals($selectedDeveloper)
            );
        }
        elseif ($selectedCategory)
        {
            $condition = $titles->categoryId->equals($selectedCategory);
        }
        elseif ($selectedDeveloper)
        {
        	$condition = $titles->developerId->equals($selectedDeveloper);
        }

        $software = $titles->find($condition, ['orderBy' => 'createdDate']);

        $this->template->selectedCategory = $selectedCategory;
        $this->template->selectedDeveloper = $selectedDeveloper;
        $this->template->developers = $developers->getAll();
        $this->template->categories = $categories->getAll();
        $this->template->titles = $software;
        $this->template->hasFilters = $condition;
    }

}
