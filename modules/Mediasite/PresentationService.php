<?php 

/**
 * Mediasite presentation service
 */
class MediasiteBackup_Mediasite_PresentationService extends MediasiteBackup_Mediasite_Service
{
	
	public function getPresentation($presentationId)
	{
		return $this->get("/Presentations('$presentationId')", ['$select' => 'full']);
	}


	public function getPresentationAnalytics($presentationId)
	{
		return $this->get("/PresentationAnalytics('$presentationId')", ['$select' => 'full']);
	}
	

	public function updatePresentation($presentation, $data)
	{
		return $this->patch("/Presentations('{$presentation['Id']}')", $data);
	}


	public function deletePresentation($presentation)
	{
		return $this->delete("/Presentations('{$presentation['Id']}')");
	}
}