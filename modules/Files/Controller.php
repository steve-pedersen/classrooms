<?php

class Classrooms_Files_Controller extends Classrooms_Master_Controller
{
    public static function GetRouteMap ()
    {
        return array(
            '/files/:id/download' => ['callback' => 'download', 'id' => '[0-9]+'],
            '/files/:fid/imagesrc' => ['callback' => 'imageSrc', 'fid' => '[0-9]+'],
            '/files/check'         => ['callback' => 'check'],
        );
    }

	public function check ()
	{
		$this->requirePermission('file download');
		$missing = array();
		$files = $this->schema('Classrooms_Files_File')->getAll();

		foreach ($files as $file)
		{
			if (!file_exists($file->localName))
			{
				$missing[$file->id] = $file;
			}
		}

		$this->template->missing = $missing;
	}

    public function imageSrc ()
    {
        $this->requireLogin();
        $fid = $this->getRouteVariable('fid');
        $file = $this->requireExists($this->schema('Classrooms_Files_File')->get($fid));
        $file->sendFile($this->response);
    }
  
    public function download ()
    {
        $account = $this->getAccount();
        
        $fid = $this->getRouteVariable('id');
        $file = $this->requireExists($this->schema('Classrooms_Files_File')->get($fid));
        $allowed = $this->getRouteVariable('allowed') ?? false;

        if ($file->uploadedBy && !$allowed && ($account->id != $file->uploadedBy->id))
        {
            if ($item = $this->getRouteVariable('item'))
            {
                $authZ = $this->getAuthorizationManager();
                $extension = $item->extension;
                
                if ($authZ->hasPermission($account, $extension->getItemViewTask(), $item))
                {
                    $file->sendFile($this->response);
                }
            }
            
            $this->requirePermission('file download');
        }
        
        $file->sendFile($this->response);
    }

}