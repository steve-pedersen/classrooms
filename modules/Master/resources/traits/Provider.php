<?php

trait Notes_Provider 
{
	abstract public function getNotePath ();
	abstract public function getNoteBase ();
	abstract public function getNoteUrl ();
	abstract public function getSchema ($schemaName = null);

	public function addNote ($message, $user, $details=[])
	{
		$note = $this->getSchema('Classrooms_Notes_Entry')->createInstance();

		if (!empty($details) && (!empty($details['old']) || !empty($details['new'])))
		{
			$note->oldValues = !empty($details['old']) ? serialize($details['old']) : null;
			$note->newValues = !empty($details['new']) ? serialize($details['new']) : null;
		}
		$note->message = $message;
		$note->path = $this->getNotePath();
		$note->url = $this->getNoteUrl();
		$note->deleted = false;
		$note->createdBy = $user;
		$note->createdDate = new DateTime;

		return $note->save();
	}

	public function getNotes ($limit = 0, $offset = 0)
	{
		$schema = $this->getSchema('Classrooms_Notes_Entry');

		$params = [ 
			'limit' => $limit,
			'offset' => $offset,
			'orderBy' => ['-created_date']
		];

		return $schema->find(
			$schema->path->like($this->getNotePath() . '%'), 
			array_filter($params)
		);
	}

	public function getNotesSince ($sinceDate, $limit = 0, $offset = 0)
	{
		$schema = $this->getSchema('Classrooms_Notes_Entry');

		$params = [ 
			'limit' => $limit,
			'offset' => $offset,
			'orderBy' => ['-created_date']
		];

		return $schema->find(
			$schema->allTrue(
				$schema->path->like($this->getNotePath() . '%'),
				$schema->createdDate->afterOrEquals($sinceDate)
			), 
			array_filter($params)
		);
	}
}