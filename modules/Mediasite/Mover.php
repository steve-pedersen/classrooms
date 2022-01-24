<?php 

/**
 * Verify presentations in DB
 */
class MediasiteBackup_Mediasite_Mover extends MediasiteBackup_Mediasite_Action
{
	public function execute($limit)
	{
		die('Do not run!!!');
		$schema = $this->getSchema('MediasiteBackup_Mediasite_Backup');
		$presentationService = $this->getService('presentation');
		$folderService = $this->getService('folder');
		$siteSettings = $this->app->siteSettings;
		$migrationDateSetting = $siteSettings->getProperty('backup-migration-date') ?: '1969-01-01';

		$migrationDate = new DateTime($migrationDateSetting);

		$now = new DateTime;
		$before = new DateTime('now - 1 week');
		// echo "<pre>"; var_dump($now); echo "</pre>";
		// echo "<pre>"; var_dump($before); echo "</pre>";
		// echo "<pre>"; var_dump($migrationDate); echo "</pre>";
		// die;

		if ($now > $migrationDate && $before < $migrationDate)
		{
			$backups = $schema->find(
				$schema->allTrue(
					$schema->presentationInfo->isNotNull(),
					$schema->migrationDate->isNull()
				),
				[
					'limit' => $limit,
					'orderBy' => ['+creationDate', '+id'],
				]
			);

			if (!$backups)
			{
				return;
			}
			// $this->app->log('error', 'Should not get here');
			// die;
			$archiveFolderId = $this->app->configuration->getProperty('mediasite.archivefolder');
			if ($archiveFolderId && 
			   ($archiveFolder = $folderService->getFolder($archiveFolderId)))
			{
				$folders = $folderService->getChildFolders($archiveFolder);
				

				foreach ($backups as $backup)
				{
					try
					{
						if ($presentation = $presentationService->getPresentation($backup->presentationId))
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

							$backup->migrationDate = new DateTime;
							$backup->save();
						}
					}
					catch (Exception $e)
					{
						$this->app->log('error', "Could not migrate presentation {$presentation['Id']}");
					}
				}
			}
		}
	}
}