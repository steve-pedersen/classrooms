<?php

class Classrooms_Communication_Manager
{
  private $app;
  private $ctrl;
  private $fixedEmail;
  private $fixedName;
  private $subjectLine;
  private $facultyRole;

  private $schemas = array();

  public function __construct (Bss_Core_Application $app, $ctrl)
  {
    $this->app = $app;
    $this->ctrl = $ctrl;
    $this->fixedEmail = $app->getConfiguration()->getProperty('communications.fixedEmail');
    $this->fixedName = $app->getConfiguration()->getProperty('communications.fixedName');
    $this->replyTo = $app->getConfiguration()->getProperty('communications.replyTo');
    $this->subjectLine = $app->getConfiguration()->getProperty('communications.subjectLine', 'Classroom Database');

    $roleName = $app->getConfiguration()->getProperty('authorization.role', 'Faculty');
    $roleSchema = $this->getSchema('Classrooms_AuthN_Role');
    $this->facultyRole = $roleSchema->findOne($roleSchema->name->equals($roleName));
  }

  public function processCommunicationEvent (Classrooms_Communication_Event $event)
  {
    if ($event->sent)
    {
      $this->app->log('info', 'Event #' . $event->id . ' already sent.');
      return;
    }

 
    $facultySchema = $this->getSchema('Classrooms_ClassData_User');
    $courseSchema = $this->getSchema('Classrooms_ClassData_CourseSection');
    $roomSchema = $this->getSchema('Classrooms_Room_Location');
    $typeSchema = $this->getSchema('Classrooms_Room_Type');
    $buildingSchema = $this->getSchema('Classrooms_Room_Building');

    // fetch and format the schedules data
    $term = $event->termYear;
    $labType = $typeSchema->findOne($typeSchema->name->equals('Lab'));

    $service = new Classrooms_ClassData_Service($this->app);
    $schedules = [];
    $facilities = $service->getFacilities()['facilities'];

    foreach ($facilities as $fid => $facility)
    {
        if (isset($facility['building']) && isset($facility['room']) && 
            $facility['building'] !== 'OFF' && $facility['building'] !== 'ON')
        {
            $roomNum = $facility['room'];
            $bldgCode = $facility['building'];
        }
        else
        {
            continue;
        }

        $result = $service->getSchedules($term, $fid)['courseSchedules'];
        
        if (!empty($result['courses']))
        {
            if (!($building = $buildingSchema->findOne($buildingSchema->code->equals($bldgCode))))
            {
                $building = $buildingSchema->createInstance();
                $building->code = $bldgCode;
                $roomNumLen = strlen($roomNum) + 1;
                $start = strlen($facility['description']) - $roomNumLen - 1;
                $end = strlen($facility['description']) - $roomNumLen;
                if (substr($facility['description'], $start, $end) === ' ')
                {
                    $roomNumLen += 1;
                }
                $building->name = substr($facility['description'], 0, strlen($facility['description']) - $roomNumLen);
                $building->save();
            }

            if (!($room = $roomSchema->findOne($roomSchema->number->equals($roomNum))))
            {
                $room = $roomSchema->createInstance()->applyDefaults($roomNum, $building);
            }

            foreach ($result['courses'] as $cid => $info)
            {
                $course = $courseSchema->get($cid);
                $course->classroom_id = $room->id;

                foreach ($course->instructors as $instructor)
                {
                    if (!isset($schedules[$instructor->id]))
                    {
                        $schedules[$instructor->id] = [];
                    }

                    $type = '';
                    switch ($room->type_id)
                    {
                        case $labType->id:
                            $type = 'labs';
                            break;
                        case null:
                            $type = 'unconfigured';
                            break;
                        default:
                            $type = 'nonlabs';
                    }

                    $schedules[$instructor->id][$cid] = [
                        'course_section_id' => $cid,
                        'room_id' => $room->id,
                        'room_external_id' => $fid,
                        'type' => $type,
                        'schedules' => $info['schedules']
                    ];
                }
            }
        }
    }

    // organize for communication
    $comms = [];
    foreach ($schedules as $username => $courses)
    {
    	$comms[$username] = [];
    	$facultyRooms = [
    		'labs' => [],
    		'nonlabs' => [],
    		'unconfigured' => []
    	];

    	foreach ($courses as $sectionId => $info)
    	{
    		$course = $courseSchema->get($sectionId);
    		$room = $roomSchema->get($info['room_id']);
        $course->classroom_id = $info['room_id'];
        $course->save();
    		$facultyRooms[$info['type']][] = ['room' => $room, 'course' => $course];
    	}
    	$comms[$username] = $facultyRooms;
    }

    foreach ($comms as $username => $comm)
    {
    	$faculty = $facultySchema->get($username);
    	$this->processFacultyCommunicationEvent($event, $faculty, $comm);
    }

    $event->sent = true;
    $event->save();
    $event->logs->save();
  }


  public function processFacultyCommunicationEvent ($event, $faculty, $commData = null)
  {

    $accountSchema = $this->getSchema('Bss_AuthN_Account');
    $account = $accountSchema->findOne($accountSchema->username->equals($faculty->id));

    if (!$account)
    {
      $account = $accountSchema->createInstance();
      $account->firstName = $faculty->firstName;
      $account->lastName = $faculty->lastName;
      $account->username = $faculty->id;
      $account->emailAddress = $faculty->emailAddress;
      $account->createdDate = new DateTime;
      $account->faculty = $faculty;

      $account->save();

      if ($this->facultyRole)
      {
        $account->roles->add($this->facultyRole);
        $account->roles->save();
      }
    }

    $eventLog = $this->getSchema('Classrooms_Communication_Log')->createInstance();
    $eventLog->communication = $event->communication;
    $eventLog->event = $event;
    $eventLog->faculty = $faculty;
    $eventLog->emailAddress = $faculty->emailAddress;
    $eventLog->creationDate = new DateTime;

    $this->sendRoomMasterTemplate($commData, $faculty, $event);

    $event->logs->add($eventLog);
  }

  public function sendRoomMasterTemplate ($comm, $user, $event)
  {
    $communication = $event->communication;

    $params = array(
      '|%FIRST_NAME%|' => $user->firstName,
      '|%LAST_NAME%|' => $user->lastName,
      '|%SEMESTER%|' => $event->formatTermYear(),
      '|%LAB_ROOM_WIDGET%|' => $this->getLabRoomWidget($comm['labs'], $communication->labRoom),
      '|%NONLAB_ROOM_WIDGET%|' => $this->getNonlabRoomWidget($comm['nonlabs'], $communication->nonlabRoom),
      '|%UNCONFIGURED_ROOM_WIDGET%|' => $this->getUnconfiguredRoomWidget($comm['unconfigured'], $communication->unconfiguredRoom),
    );
    
    $this->sendEmail($user, $params, $communication->roomMasterTemplate);
  }



  public function sendEmail($user, $params, $templateText)
  {
    if ($this->hasContent($templateText))
    {
      $siteSettings = $this->app->siteSettings;
      $preppedText = strtr($templateText, $params);
      $mail = $this->ctrl->createEmailMessage('roomMasterTemplate.email.tpl');
      $mail->Subject = $this->subjectLine;

      if ($this->fixedEmail)
      {
        $mail->AddAddress($this->fixedEmail, $this->fixedName);
      }
      else
      {
        $mail->AddAddress($user->emailAddress, $user->fullName);
      }

      if ($this->replyTo)
      {
        $mail->AddReplyTo($this->replyTo);
      }

      $mail->getTemplate()->message = $preppedText;
      $mail->getTemplate()->title = $siteSettings->getProperty('communications-title', 'Intructor Rooms');
      $mail->Send();
    }
  }


  private function hasContent ($text)
  {
    return (strlen(strip_tags(trim($text))) > 1);
  }

  private function getLabRoomWidget ($rooms, $intro)
  {
    if (!empty($rooms) && $intro)
    { 
      $template = $this->ctrl->createTemplateInstance();
      $template->disableMasterTemplate();
      $template->intro = $intro;
      $template->rooms = $rooms;
      return trim($template->fetch($this->ctrl->getModule()->getResource('labRoom.email.tpl')));
    }

    return '';
  }
  private function getNonlabRoomWidget ($rooms, $intro)
  {
    if (!empty($rooms) && $intro)
    {
      $template = $this->ctrl->createTemplateInstance();
      $template->disableMasterTemplate();
      $template->intro = $intro;
      $template->rooms = $rooms;
      return trim($template->fetch($this->ctrl->getModule()->getResource('nonlabRoom.email.tpl')));
    }

    return '';
  }
  private function getUnconfiguredRoomWidget ($rooms, $intro)
  {
    if (!empty($rooms) && $intro)
    {
      $template = $this->ctrl->createTemplateInstance();
      $template->disableMasterTemplate();
      $template->intro = $intro;
      $template->rooms = $rooms;
      return trim($template->fetch($this->ctrl->getModule()->getResource('unconfiguredRoom.email.tpl')));
    }

    return '';
  }

  private function getRequestLink ()
  {
    $template = $this->ctrl->createTemplateInstance();
    $template->disableMasterTemplate();
    $template->linkUrl = $this->app->baseUrl('fr/request');
    return $template->fetch($this->ctrl->getModule()->getResource('link.email.tpl'));
  }


  private function getLastSystem($faculty)
  {
    $lastSystem = 'Your previous refresh computer';

    if ($faculty && $faculty->systems->count() > 0)
    {
      $lastSystem = $faculty->systems->index(0);
    }

    return $lastSystem;
  }


  private function getSchema($schemaName)
  {
    if (!isset($this->schemas[$schemaName]))
    {
      $schemaManager = $this->app->schemaManager;
      $this->schemas[$schemaName] = $schemaManager->getSchema($schemaName);
    }

    return $this->schemas[$schemaName];
  }
}