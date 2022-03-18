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

  public function processRoomUpgradeCommunication ($upgrade)
  {
    $facultySchema = $this->getSchema('Classrooms_ClassData_User');
    $scheduleSchema = $this->getSchema('Classrooms_ClassData_CourseSchedule');
    $eventSchema = $this->getSchema('Classrooms_Communication_Event');
    $commTypeSchema = $this->getSchema('Classrooms_Communication_Type');
    $commSchema = $this->getSchema('Classrooms_Communication_Communication');

    $upgradeType = $commTypeSchema->findOne($commTypeSchema->isUpgrade->isTrue());
    if (!$upgradeType)
    {
      $this->app->log('info', 'Upgrade #' . $upgrade->id . ' email not sent due to unconfigured communication type.');
      return;
    }

    $communication = $commSchema->findOne($commSchema->type_id->equals($upgradeType->id));
    if (!$communication)
    {
      $this->app->log('info', 'Upgrade #' . $upgrade->id . ' email not sent due to unconfigured upgrade communication.');
      return;
    }

    $event = $eventSchema->createInstance();
    $event->creationDate = $event->sendDate = new DateTime;
    $event->communication = $communication;
    $event->termYear = $upgrade->termYear;
    $event->sent = true; // setting to true so it doesn't get picked up by the cron if something fails
    $event->save();

    $condition = $scheduleSchema->allTrue(
      $scheduleSchema->room_id->equals($upgrade->room_id),
      $scheduleSchema->termYear->equals($upgrade->getTermYear()),
      $scheduleSchema->userDeleted->isNull()->orIf($scheduleSchema->userDeleted->isFalse())
    );
    $schedules = $scheduleSchema->find($condition, ['arrayKey' => 'faculty_id']);

    foreach ($schedules as $username => $schedule)
    {
      $faculty = $facultySchema->get($username);
      $this->processFacultyCommunicationEvent($event, $faculty, $upgrade, true);
    }

    $event->sent = true;
    $event->save();
    $event->logs->save();

    return $event->logs;
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

    $communicationRoomTypes = [];
    foreach ($event->communication->type->roomTypes as $roomType)
    {
      $communicationRoomTypes[$roomType->id] = $roomType->id;
    }

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
        if ((!$schedule->room->type_id || !$schedule->room->configured) && $event->communication->type->includeUnconfiguredRooms)
        {
          $type = 'unconfigured';
        }
        elseif ($schedule->room->type_id && in_array($schedule->room->type_id, $communicationRoomTypes))
        {
          $type = $schedule->room->type->isLab ? 'labs' : 'nonlabs';
        }
        elseif ($schedule->room->type_id && !in_array($schedule->room->type_id, $communicationRoomTypes))
        {
          // this room type is not to be included in this communication
          // continue;
          $type = '';
        }
      }
      elseif ($event->communication->type->includeCoursesWithoutRooms)
      {
        $type = 'norooms';
      }
      else
      {
        // continue;
        $type = '';
      }

      if ($type !== '')
      {
        $comms[$facultyId][$type][] = ['room' => $schedule->room, 'course' => $schedule->course];
      }
    }

    foreach ($comms as $username => $comm)
    {
      $hasRoomTypeForCommunication = false;
      foreach ($comm as $type)
      {
        if (!empty($type))
        {
          $hasRoomTypeForCommunication = true;
          break;
        }
      }

      if ($hasRoomTypeForCommunication)
      {
        $faculty = $facultySchema->get($username);
        $this->processFacultyCommunicationEvent($event, $faculty, $comm);
      }
    }

    $event->sent = true;
    $event->save();
    $event->logs->save();
  }


  public function processFacultyCommunicationEvent ($event, $faculty, $commData = null, $isUpgrade = false)
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

    if ($isUpgrade)
    {
      $this->sendUpgradeRoomMasterTemplate($commData, $account, $event);
    }
    else
    {
      $this->sendRoomMasterTemplate($commData, $account, $event);
    }

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

  public function sendUpgradeRoomMasterTemplate ($upgrade, $user, $event)
  { 
    $communication = $event->communication;

    $params = array(
      '|%FIRST_NAME%|' => $user->firstName,
      '|%LAST_NAME%|' => $user->lastName,
      '|%SEMESTER%|' => $event->formatTermYear(),
      '|%CONTACT_EMAIL%|' => "<a href='mailto:$this->contactEmail'>$this->contactEmail</a>",
      '|%UPGRADE_ROOM_WIDGET%|' => $this->getUpgradeRoomWidget($upgrade, $communication->upgradeRoom),
    );

    $this->sendEmail($user, $params, $communication->roomMasterTemplate);
  }

  private function getUpgradeRoomWidget($upgrade, $templateText)
  {
    if ($upgrade && $templateText)
    { 
      $relocatedTo = $this->getSchema('Classrooms_Room_Location')->get($upgrade->relocated_to);
      $relocatedLink = $relocatedTo ? '<a href="'.$relocatedTo->getRoomUrl().'">'.$relocatedTo->getCodeNumber().'</a>' : 'N/A';
      $roomLink = '<a href="'.$upgrade->room->getRoomUrl().'">'.$upgrade->room->getCodeNumber().'</a>';
      
      $params = [
        '|%UPGRADE_DATE%|' => $upgrade->upgradeDate->format('m/d/Y'),
        '|%ROOM_TO_BE_UPGRADED_LINK%|' => $roomLink,
        '|%RELOCATED_TO_ROOM_LINK%|' => $relocatedLink
      ];

      $preppedText = strtr($templateText, $params);
      $template = $this->ctrl->createTemplateInstance();
      $template->disableMasterTemplate();
      $template->templateText = $preppedText;

      return trim($template->fetch($this->ctrl->getModule()->getResource('upgradeRoom.email.tpl')));
    }

    return '';
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