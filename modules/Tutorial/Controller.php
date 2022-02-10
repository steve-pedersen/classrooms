<?php

/**
 * The Tuts controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Tutorial_Controller extends Classrooms_Master_Controller
{
    public static function getRouteMap ()
    {
        return [
            'tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            'tutorials/:id' => ['callback' => 'preview', ':id' => '[0-9]+'],
            'tutorials' => ['callback' => 'listTutorials'],
            'tutorials/upload' => ['callback' => 'uploadImages'],
            'tutorials/files/:fileid/preview' => ['callback' => 'previewImage'],
            'tutorials/:id/files/:fileid/download' => ['callback' => 'downloadImage'],
        ];

    }

    public function preview ()
    {
        $this->requirePermission('edit');
        $this->addBreadcrumb('tutorials', 'List Tutorials');
        $tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Tutorial_Page', 'id');
        $this->template->tutorial = $tutorial;
    }

    public function editTutorial () 
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        $this->addBreadcrumb('tutorials', 'List Tutorials');
    	$tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Tutorial_Page', 'id', ['allowNew' => true]);
    	$notes = $this->schema('Classrooms_Notes_Entry');

    	if ($this->request->wasPostedByUser())
    	{
    		switch ($this->getPostCommand())
    		{
    			case 'save':

                    $new = $tutorial->inDatasource;
                    if (!$new && $tutorial->hasDiff($this->request->getPostParameters()))
                    {
                        $tutorial->addNote('Tutorial updated', $viewer, $tutorial->getDiff($this->request->getPostParameters()));
                    }

    				$tutorial->name = $this->request->getPostParameter('name');
                    $tutorial->headerImageUrl = $this->request->getPostParameter('headerImageUrl');
                    $tutorial->youtubeEmbedCode = trim($this->request->getPostParameter('youtubeEmbedCode'));
    				$tutorial->description = trim($this->request->getPostParameter('description'));
    				$tutorial->createdDate = $tutorial->createdDate ?? new DateTime;
    				$tutorial->modifiedDate = new DateTime;
    				$tutorial->save();
                    if ($new)
                    {
                        $tutorial->addNote('Tutorial created', $viewer);
                    }

                    if ($newFiles = $this->request->getPostParameter('newfiles'))
                    {
                        $files = $this->schema('Classrooms_Files_File');
                        foreach ($newFiles as $fid)
                        {
                            $file = $files->get($fid);
                            $file->tutorial_id = $tutorial->id;
                            $file->moveToPermanentStorage();
                            $file->save();
                        }
                    }

    				$this->flash('Tutorial saved');
    				$this->response->redirect('tutorials');

    				break;

    			case 'delete':
                    $tutorial->deleted = true;
                    $tutorial->save();
                    $tutorial->addNote('Tutorial deleted', $viewer);

                    $this->flash('Tutorial deleted');
                    $this->response->redirect('tutorials');
    				break;
    		}
    	}
        
        $this->template->images = $tutorial->images;
    	$this->template->tutorial = $tutorial;
    	$this->template->notes = $tutorial->inDatasource ? $notes->find(
            $notes->path->like($tutorial->getNotePath().'%'), ['orderBy' => '-createdDate']
        ) : [];
    }

    public function listTutorials ()
    {
        $this->requirePermission('edit');
        $tuts = $this->schema('Classrooms_Tutorial_Page');
        $tutorials = $tuts->find($tuts->deleted->isFalse()->orIf($tuts->deleted->isNull()),['orderBy' => 'name']);

        $this->template->tutorials = $tutorials;
    }

    public function downloadImage ()
    {   
    	$tutorialId = $this->getRouteVariable('id');
    	$fileId = $this->getRouteVariable('fileid');
    	$tutorial = $this->schema('Classrooms_Tutorial_Page')->get($tutorialId);
    	$this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function previewImage ()
    {   
        // $this->requirePermission('edit');
        $fileId = $this->getRouteVariable('fileid');
        $this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function uploadImages ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');

        if ($this->request->wasPostedByUser())
        {
            $results = [
                'message' => 'Server error when uploading.',
                'status' => 500,
                'success' => false
            ];

            $files = $this->schema('Classrooms_Files_File');
            $file = $files->createInstance();
            $file->createFromRequest($this->request, 'file', false);
   
            if ($file->isValid())
            {
                $uploadedBy = (int)$this->request->getPostParameter('uploadedBy', $viewer->id);
                $file->uploaded_by_id = $uploadedBy;
                $file->save();

                $results = [
                    'message' => 'Your file has been uploaded.',
                    'status' => 200,
                    'success' => true,
                    'file' => [
                        'id' => $file->id,
                        'url' => 'tutorials/files/' . $file->id . '/preview',
                        'fullUrl' => $this->baseUrl('tutorials/files/' . $file->id . '/preview'),
                        'name' => $file->remoteName,
                    ],
                ];
            }
            else
            {
                $messages = 'Incorrect file type or file too large.';
                $results['status'] = $messages !== '' ? 400 : 422;
                $results['message'] = $messages;
            }

            echo json_encode($results);
            exit;  
        }    

        $this->template->viewer = $viewer;
    }


}
