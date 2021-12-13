<?php 

/**
 * Verify presentations in DB
 */
class MediasiteBackup_Mediasite_Verifier extends MediasiteBackup_Mediasite_Action
{
	public function execute($limit)
	{
		die('Do not run!!!');
		$schema = $this->getSchema('MediasiteBackup_Mediasite_Backup');
		$service = $this->getService('presentation');

		$backups = $schema->find($schema->presentationInfo->isNull(), [
			'limit' => $limit,
			'orderBy' => ['+creationDate', '+id']
		]);

		foreach ($backups as $backup)
		{
			if ($presentation = $service->getPresentation($backup->presentationId))
			{
				$backup->presentationInfo = $presentation;
			}
			else
			{
				$backup->presentationInfo = ['error' => 'Could not fetch presentation'];
			}

			$backup->save();
		}
	}
}