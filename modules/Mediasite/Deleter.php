<?php 

/**
 * Verify presentations in DB
 */
class MediasiteBackup_Mediasite_Deleter extends MediasiteBackup_Mediasite_Action
{
	public function execute($limit)
	{
		die('Do not run!!!');
		$schema = $this->getSchema('MediasiteBackup_Mediasite_Backup');
		$service = $this->getService('presentation');
		$siteSettings = $this->app->siteSettings;
		$deletionDateSetting = $siteSettings->getProperty('backup-deletion-date') ?: '1969-01-01';

		$deletionDate = new DateTime($deletionDateSetting);

		$now = new DateTime;
		$before = new DateTime('now - 1 week');
		// echo "<pre>"; var_dump($now); echo "</pre>";
		// echo "<pre>"; var_dump($before); echo "</pre>";
		// echo "<pre>"; var_dump($migrationDate); echo "</pre>";
		// die;

		if ($now > $deletionDate && $before < $deletionDate)
		{
			$backups = $schema->find(
				$schema->allTrue(
					$schema->presentationInfo->isNotNull(),
					$schema->migrationDate->isNotNull(),
					$schema->deletionDate->isNull()
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

			foreach ($backups as $backup)
			{
				try
				{
					if ($presentation = $service->getPresentation($backup->presentationId))
					{
						//$service->deletePresentation($presentation);

						$backup->deletionDate = new DateTime;
						$backup->save();
					}
					else
					{
						$this->app->log('error', "Could not fetch presentation {$presentation['Id']} for deletion.");
					}
				}
				catch (Exception $e)
				{
					$this->app->log('error', "Could not delete presentation {$presentation['Id']}");
				}
			}
		}
	}
}