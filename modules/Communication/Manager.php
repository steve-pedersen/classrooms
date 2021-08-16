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
    $this->contactEmail = $app->getConfiguration()->getProperty('communications.contactEmail', 'at@sfsu.edu');
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

    $scheduleSchema = $this->getSchema('Classrooms_ClassData_CourseSchedule');
    $facultySchema = $this->getSchema('Classrooms_ClassData_User');
    $courseSchema = $this->getSchema('Classrooms_ClassData_CourseSection');
    $roomSchema = $this->getSchema('Classrooms_Room_Location');
    $typeSchema = $this->getSchema('Classrooms_Room_Type');

    // fetch and format the schedules data
    $labType = $typeSchema->findOne($typeSchema->name->equals('Lab'));

    $condition = $scheduleSchema->allTrue(
      $scheduleSchema->termYear->equals($event->termYear),
      $scheduleSchema->userDeleted->isNull()->orIf(
        $scheduleSchema->userDeleted->isFalse()
      )
    );
    $schedules = $scheduleSchema->find($condition);

    foreach ($schedules as $schedule)
    {
      $facultyId = $schedule->faculty_id;
      if (!isset($comms[$facultyId]))
      {
        $comms[$facultyId] = [
          'labs' => [],
          'nonlabs' => [],
          'unconfigured' => [],
          'norooms' => [],
        ];
      }

      $type = '';
      if ($schedule->room)
      {
        $type = $schedule->room->configured ? 'nonlabs' : 'unconfigured';
      }
      else
      {
        $type = 'norooms';
      }

      $comms[$facultyId][$type][] = ['room' => $schedule->room, 'course' => $schedule->course];
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
    if (!($account = $accountSchema->get($faculty->id)))
    {
      $account = $accountSchema->findOne($accountSchema->username->equals($faculty->id));
    }

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
      '|%CONTACT_EMAIL%|' => "<a href='mailto:$this->contactEmail'>$this->contactEmail</a>",
      '|%LAB_ROOM_WIDGET%|' => $this->getLabRoomWidget($comm['labs'], $communication->labRoom),
      '|%NONLAB_ROOM_WIDGET%|' => $this->getNonlabRoomWidget($comm['nonlabs'], $communication->nonlabRoom),
      '|%UNCONFIGURED_ROOM_WIDGET%|' => $this->getUnconfiguredRoomWidget($comm['unconfigured'], $communication->unconfiguredRoom),
      '|%NO_ROOM_WIDGET%|' => $this->getNoRoomWidget($comm['norooms'], $communication->noRoom),
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
      $mail->getTemplate()->title = $siteSettings->getProperty('communications-title', 'Instructor Rooms');
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
  public function getNoRoomWidget ($rooms, $intro)
  {
    if (!empty($rooms) && $intro)
    {
      $template = $this->ctrl->createTemplateInstance();
      $template->disableMasterTemplate();
      $template->intro = $intro;
      $template->rooms = $rooms;
      return trim($template->fetch($this->ctrl->getModule()->getResource('noRoom.email.tpl')));
    }

    return '';
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