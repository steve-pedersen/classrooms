<?php 

/**
 * Mediasite Room service
 */
class MediasiteBackup_Mediasite_RoomService extends MediasiteBackup_Mediasite_Service
{
	
	public function getRooms()
	{
		return $this->get("/Rooms", ['$select' => 'full']);
	}


	public function getRoom($RoomId)
	{
		return $this->get("/Rooms('$RoomId')", ['$select' => 'full']);
	}

	public function getDevices()
	{
		return $this->get("/Devices", ['$select' => 'full']);
	}


	public function getDevice($DeviceId)
	{
		return $this->get("/Devices('$DeviceId')", ['$select' => 'full']);
	}

	public function getDeviceScheduledRecordingTimes($DeviceId)
	{
		return $this->get("/Devices('$DeviceId')/ScheduledRecordingTimes", ['$select' => 'full']);
	}

	public function getMediasiteObject($MediasiteId)
	{
		return $this->get("/MediasiteObjects('$MediasiteId')", ['$select' => 'full']);
	}

	public function getSchedules()
	{
		return $this->get("/Schedules", ['$select' => 'full']);
	}

	public function getSchedule($ScheduleId)
	{
		return $this->get("/Schedules('$ScheduleId')", ['$select' => 'full']);
	}

	public function getScheduleRecurrences($ScheduleId)
	{
		return $this->get("/Schedules('$ScheduleId')/Recurrences", ['$select' => 'full']);
	}

	public function getScheduleRecurrence($ScheduleId, $RecurrenceId)
	{
		return $this->get("/Schedules('$ScheduleId')/Recurrences($RecurrenceId)", ['$select' => 'full']);
	}

	public function getScheduleRecorder($ScheduleId)
	{
		return $this->get("/Schedules('$ScheduleId')/Recorder", ['$select' => 'full']);
	}

	public function getRecorder($RecorderId)
	{
		return $this->get("/Recorders('$RecorderId')");
	}

	public function getRecorderStatus($RecorderId)
	{
		return $this->get("/Recorders('$RecorderId')/Status");
	}

	public function getRecorderExtendedStatus($RecorderId)
	{
		return $this->get("/Recorders('$RecorderId')/ExtendedStatus");
	}

	public function getFolder($FolderId)
	{
		return $this->get("/Folders('$FolderId')", ['$select' => 'full']);
	}

	public function getFolderPresentations($FolderId)
	{
		return $this->get("/Folders('$FolderId')/Presentations", ['$select' => 'full']);
	}

	public function getPresentation($PresentationId)
	{
		return $this->get("/Presentations('$PresentationId')", ['$select' => 'full']);
	}
	
	public function getRoomConfigurations()
	{
		return $this->get("/RoomConfigurations", ['$select' => 'full']);
	}


	public function getRoomConfiguration($RoomConfigurationId)
	{
		return $this->get("/RoomConfigurations('$RoomConfigurationId')", ['$select' => 'full']);
	}

	public function updateRoom($Room, $data)
	{
		return $this->patch("/Rooms('{$Room['Id']}')", $data);
	}


	public function deleteRoom($Room)
	{
		return $this->delete("/Rooms('{$Room['Id']}')");
	}
}