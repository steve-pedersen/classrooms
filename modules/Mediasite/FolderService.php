<?php 

/**
 * Mediasite presentation service
 */
class MediasiteBackup_Mediasite_FolderService extends MediasiteBackup_Mediasite_Service
{
	
	public function getFolder($folderId)
	{
		return $this->get("/Folders('$folderId')", ['$select' => 'full']);
	}


	public function getChildFolders($parent)
	{
		$folders = [];
		$next = null;

		do
		{
			$client = $this->getClient($next);

			if ($results = $this->validateResult($client->get(($next ? '' : '/Folders'))))
			{
				$next = $results['odata.nextLink'] ?? null;
				foreach ($results['value'] as $folder)
				{
					if ($folder['ParentFolderId'] === $parent['Id'])
					{
						$folders[$folder['Name']] = $folder;
					}
				}
			}
		} while ($next !== null);

		return $folders;
	}


	public function createFolder($name, $parent)
	{
		$folder = [
			'Name' => $name,
			'Description' => 'Archive Folder for ' . $name,
			'IsCopyDestination' => 'true',
			'ParentFolderId' => $parent['Id'],
		];

		return $this->post('/Folders', $folder);
	}


	public function updateFolder($presentation, $data)
	{
		return $this->patch("/Folders('{$presentation['Id']}')", $data);
	}


	public function deleteFolder($presentation)
	{
		return $this->delete("/Folders('{$presentation['Id']}')");
	}
}