<?php 

/**
 * Abstract class for bulk action handlers
 */
abstract class MediasiteBackup_Mediasite_Action
{
	protected $app;

	function __construct(Bss_Core_Application $application)
	{
		$this->app = $application;
	}

	protected function getService($name)
	{
		switch ($name)
		{
			case 'presentation':
				return new MediasiteBackup_Mediasite_PresentationService($this->app);
			case 'folder':
				return new MediasiteBackup_Mediasite_FolderService($this->app);
			default:
				return false;
		}
	}

	protected function getSchema($schemaName)
	{
		return $this->app->schemaManager->getSchema($schemaName);
	}
}