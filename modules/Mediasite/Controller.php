<?php

/**
 */
class MediasiteBackup_Mediasite_Controller extends MediasiteBackup_Master_Controller
{
    public static function getRouteMap ()
    {
        return [
            'test' => ['callback' => 'test'],
            'backup' => ['callback' => 'backup'],
            'settings' => ['callback' => 'settings'],
            'status' => ['callback' => 'status'],
        ];
    }


    public function backup ()
    {
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'upload':
                    if ($file = $this->validateCSV('csv'))
                    {
                        $schema = $this->schema('MediasiteBackup_Mediasite_Backup');
                        $processed = [];

                        if ($handle = fopen($file->getLocalPath(), "r"))
                        {
                            while (($row = fgetcsv($handle, 1000)) !== false) 
                            {
                                if (count($row) < 1) break;

                                $id = trim($row[0]);
                                if (!isset($processed[$id]))
                                {
                                    $backup = $schema->createInstance();
                                    $backup->presentationId = $id;
                                    $backup->creationDate = new DateTime;
                                    $backup->save();
                                    $processed[$id] = true;
                                }
                            }

                            $siteSettings = $this->getApplication()->siteSettings;
                            $siteSettings->removeProperty('backup-migration-date');
                            $siteSettings->removeProperty('backup-deletion-date');

                            $numProcessed = count($processed);
                            $this->flash("$numProcessed added to system for processing. Migration date and deletion date have been reset.");
                            $this->response->redirect('/backup');
                        }
                    }
            } 
        }
    }


    public function settings ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {           
            if ($migrationDate = $this->request->getPostParameter('migration-date'))
            {
                $siteSettings->setProperty('backup-migration-date', $migrationDate);
            }

            if ($deletionDate = $this->request->getPostParameter('deletion-date'))
            {
                $siteSettings->setProperty('backup-deletion-date', $deletionDate);
            }

            $this->flash('The welcome text has been saved.');
            $this->response->redirect('settings');
        }
        
        $this->template->migrationDate = $siteSettings->getProperty('backup-migration-date');
        $this->template->deletionDate = $siteSettings->getProperty('backup-deletion-date');
    }


    public function test ()
    {
        $presentationIds = [
            '47e1b10931fa425990883b5db5515b361d',
            'c12ffaed20d54235a4cef1e0c8c01f2b1d'
        ];

        $presentationService = new MediasiteBackup_Mediasite_PresentationService($this->getApplication());
        $folderService = new MediasiteBackup_Mediasite_FolderService($this->getApplication());
        $siteSettings = $this->getApplication()->siteSettings;

        $archiveFolderId = $this->getApplication()->configuration->getProperty('mediasite.archivefolder');
        if ($archiveFolderId && 
           ($archiveFolder = $folderService->getFolder($archiveFolderId)))
        {
            $folders = $folderService->getChildFolders($archiveFolder);
            // $backups = $schema->find(
            //     $schema->allTrue(
            //         $schema->presentationInfo->isNotNull(),
            //         $schema->migrationDate->isNull()
            //     ),
            //     ['limit' => $limit]
            // );

            foreach ($presentationIds as $id)
            {
                try
                {
                    if ($presentation = $presentationService->getPresentation($id))
                    {
                        $owner = $presentation['Owner'];

                        if (!isset($folders[$owner]))
                        {
                            $folder = $folderService->createFolder($owner, $archiveFolder);
                            $folders[$owner] = $folder;
                        }

                        $folder = $folders[$owner];
                        $hashFragment = substr($presentation['Id'], 0, 9);
                        $presentationService->updatePresentation($presentation, [
                            'ParentFolderId' => $folder['Id'],
                            'Title' => "{$presentation['Owner']} {$hashFragment} {$presentation['Title']}"
                        ]);

                        // $backup->migrationDate = new DateTime;
                        // $backup->save();
                    }
                }
                catch (Exception $e)
                {
                    $this->app->log('error', "Could not migrate presentation {$presentation['Id']}");
                }
            }
        }
        // $service = new MediasiteBackup_Mediasite_Service($this->getApplication());

        // try {
        // $id = '47e1b10931fa425990883b5db5515b361d';
        // $response = $service->get("/Presentations('$id')", ['$select' => 'full']);
        // //$response = $service->get('', ['$select' => 'full']);

        // echo "<pre>";
        // print_r($response);
        // die;
        // } catch (Exception $e) {
        //     echo "cheese";
        //     echo "<pre>";
        //     print_r($e->getTraceAsString());
        //     die;
        // }
    }


    public function status ()
    {
        $page = $this->request->getQueryParameter('page', 1);
        $perPage = $this->request->getQueryParameter('perpage', 50);
        
        $offset = $perPage * ($page - 1);

        $schema = $this->schema('MediasiteBackup_Mediasite_Backup');

        $this->page = $page;
        $this->perPage = $perPage;
        $this->total = $schema->count();
        $this->template->backups = $schema->getAll([
            'limit' => $perPage, 
            'offset' => $offset, 
            'orderBy' => '-creationDate']
        );
    }


    private function validateCSV($fileName)
    {
        $valid = null;

        $file = $this->request->getFileUpload('csv');

        if ($file->isValid())
        {
            $pathInfo = pathinfo($file->getRemoteName());
            $valid = $pathInfo['extension'] === 'csv' ? $file : null;
        }

        return $valid;
    }
}