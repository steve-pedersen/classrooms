<?php

class Classrooms_Email_EmailManager
{
	private $app;
	private $ctrl;
	private $type;
	private $fromEmail;
	private $fromName;
	private $testEmail;
	private $testingOnly;
	private $subjectLine;
	private $attachments;
	private $ccRequest;
	private $emailLogId;
	private $templateInstance;

	private $schemas = array();

	public function __construct (Bss_Core_Application $app, $ctrl=null)
	{
		$this->app = $app;
		$this->ctrl = $ctrl;	// phasing this out...
		$this->fromEmail = $app->siteSettings->getProperty('email-default-address', 'at@sfsu.edu');
		$this->fromName = "Academic Technology";
		$this->testingOnly = $app->siteSettings->getProperty('email-testing-only');
		$this->testEmail = $app->siteSettings->getProperty('email-test-address');
		$this->subjectLine = "Academic Technology";
		$this->attachments = array();
		$this->ccRequest = false;
	}

	public function validEmailTypes ()
	{
		$types = array(
			'sendNewAccount',
			'sendCourseRequestedAdmin',
			'sendCourseRequestedTeacher',
			'sendCourseAllowedTeacher',
			'sendCourseAllowedStudents',
			'sendCourseDenied',
			'sendReservationDetails',
			'sendReservationReminder',
			'sendReservationMissed',
			'sendReservationCanceled'
		);

		return $types;
	}

	public function setTemplateInstance ($inst)
	{
		$this->templateInstance = $inst;
	}

 /**
	* Determines which email function to call.
	*
	* @param $type string to be used as name of function call (e.g. 'sendCourseRequested') 
	* @param $params array of variables needed by called function
	*/
	public function processEmail ($type, $params, $test=false)
	{	
		if (!in_array($type, $this->validEmailTypes()))
		{
			return false; 
			exit;
		}

		$this->type = $type;
		$fileType = lcfirst(str_replace('send', '', $type));
		$this->attachments = $this->getEmailAttachments($fileType);	
		$this->ccRequest = (!$this->testingOnly ? ($type === 'sendCourseRequestedAdmin') : false);

		$emailLog = $this->getSchema('Classrooms_Email_EmailLog')->createInstance();
		$emailLog->type = ($test ? 'TEST: ' : '') . $type;
		$emailLog->creationDate = new DateTime;
		$emailLog->save();
		$this->emailLogId = $emailLog->id;

		// send email based on type
		$this->$type($params, $test);
	}

    public function getEmailAttachments ($emailType)
    {
        $attachments = array();
        $files = $this->getSchema('Classrooms_Email_File')->getAll();
        foreach ($files as $file)
        {
            if (in_array($emailType, $file->attachedEmailKeys))
            {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

	public function sendNewAccount ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: An account has been created for you";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%SITE_LINK%|' => $this->generateLink('', true, "Academic Technology"),
			'message_title' => 'An account has been created for you.'
		);

		$body = trim($this->app->siteSettings->getProperty('email-new-account'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendCourseRequestedAdmin ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Course Requested";

		if (!$test)
		{
			$courseReq = $this->getSchema('Ccheckin_Courses_Request')->get($data['courseRequest']->id);
		}
		
		$params = array(
			'|%FIRST_NAME%|' => $data['requestingUser']->firstName,
			'|%LAST_NAME%|' => $data['requestingUser']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $courseReq->course->fullName : $data['courseRequest']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $courseReq->course->shortName : $data['courseRequest']->shortName),
			'|%SEMESTER%|' => (!$test ? $courseReq->course->semester->display : $data['courseRequest']->semester),
			'|%REQUEST_LINK%|' => $this->generateLink('/admin/courses/queue/' . $data['courseRequest']->id, true, 'View Course Request'),
			'message_title' => 'Course Requested'
		);

		$body = trim($this->app->siteSettings->getProperty('email-course-requested-admin'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendCourseRequestedTeacher ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Course Requested";
		if (!$test)
		{
			$courseReq = $this->getSchema('Ccheckin_Courses_Request')->get($data['courseRequest']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $courseReq->course->fullName : $data['courseRequest']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $courseReq->course->shortName : $data['courseRequest']->shortName),
			'|%SEMESTER%|' => (!$test ? $courseReq->course->semester->display : $data['courseRequest']->semester),
			'message_title' => 'Course Requested'
		);

		$body = trim($this->app->siteSettings->getProperty('email-course-requested-teacher'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendCourseAllowedTeacher ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Course Request Approved";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%COURSE_VIEW_LINK%|' => $this->generateLink('/courses/view/'.$data['course']->id, true, 'View Course'),
			'|%OPEN_DATE%|' => (!$test ? $course->semester->openDate : $data['course']->openDate)->format('M j, Y'),
			'|%LAST_DATE%|' => (!$test ? $course->semester->lastDate : $data['course']->lastDate)->format('M j, Y'),
			'message_title' => 'Course Request Approved'
		);

		$body = trim($this->app->siteSettings->getProperty('email-course-allowed-teacher'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}


	public function sendCourseAllowedStudents ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: New Course Available";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%OPEN_DATE%|' => (!$test ? $course->semester->openDate : $data['course']->openDate)->format('M j, Y'),
			'|%LAST_DATE%|' => (!$test ? $course->semester->lastDate : $data['course']->lastDate)->format('M j, Y'),
			'|%SITE_LINK%|' => $this->generateLink('', true, "Academic Technology"),
			'message_title' => 'Course Available'
		);

		$body = trim($this->app->siteSettings->getProperty('email-course-allowed-students'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}


	public function sendCourseDenied ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Course Request Denied";
		if (!$test)
		{
			$course = $this->getSchema('Ccheckin_Courses_Course')->get($data['course']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%COURSE_FULL_NAME%|' => (!$test ? $course->fullName : $data['course']->fullName),
			'|%COURSE_SHORT_NAME%|' => (!$test ? $course->shortName : $data['course']->shortName),
			'|%SEMESTER%|' => (!$test ? $course->semester->display : $data['course']->semester),
			'message_title' => 'Course Request Denied'
		);

		$body = trim($this->app->siteSettings->getProperty('email-course-denied'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendReservationDetails ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Reservation Details";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/'.$data['reservation']->id, true, 'View Reservation'),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/'.$data['reservation']->id, true, 'Cancel Reservation'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'|%ROOM_NAME%|' => (!$test ? $reservation->room->name : $data['reservation']->room),
			'message_title' => 'Reservation Details'
		);

		$body = trim($this->app->siteSettings->getProperty('email-reservation-details'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendReservationReminder ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Reservation Reminder";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_VIEW_LINK%|' => $this->generateLink('/reservations/view/'.$data['reservation']->id, true, 'View Reservation'),
			'|%RESERVE_CANCEL_LINK%|' => $this->generateLink('/reservations/delete/'.$data['reservation']->id, true, 'Cancel Reservation'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'|%ROOM_NAME%|' => (!$test ? $reservation->room->name : $data['reservation']->room),
			'message_title' => 'Reservation Reminder'
		);

		$body = trim($this->app->siteSettings->getProperty('email-reservation-reminder'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendReservationMissed ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Reservation Missed";
		if (!$test)
		{
			$reservation = $this->getSchema('Ccheckin_Rooms_Reservation')->get($data['reservation']->id);
		}

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => (!$test ? $reservation->startTime : $data['reservation']->startTime)->format('M j, Y g:ia'),
			'|%RESERVE_MISSED_LINK%|' => $this->generateLink('/reservations/missed', true, 'Missed Reservations'),
			'|%PURPOSE_INFO%|' => (!$test ? $reservation->observation->purpose->shortDescription : $data['reservation']->purpose),
			'message_title' => 'Reservation Missed'
		);

		$body = trim($this->app->siteSettings->getProperty('email-reservation-missed'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendReservationCanceled ($data, $test)
	{
		$this->subjectLine = "Academic Technology Check-in: Reservation Canceled";

		$params = array(
			'|%FIRST_NAME%|' => $data['user']->firstName,
			'|%LAST_NAME%|' => $data['user']->lastName,
			'|%RESERVE_DATE%|' => $data['reservation_date']->format('M j, Y g:ia'),
			'|%RESERVE_SIGNUP_LINK%|' => $this->generateLink('/reservations', true, 'Sign Up For a Visit'),
			'|%PURPOSE_INFO%|' => $data['reservation_purpose'],
			'message_title' => 'Reservation Canceled'
		);

		$body = trim($this->app->siteSettings->getProperty('email-reservation-canceled'));
		if ($this->hasContent($body))
		{
			$this->sendEmail($data['user'], $params, $body);	
		}
	}

	public function sendEmail($user, $params, $templateText, $templateFile=null)
	{
		if ($this->hasContent($templateText))
		{
			$messageTitle = $params['message_title'];
			$preppedText = strtr($templateText, $params);			
			$templateFileName = $templateFile ?? 'emailBody.email.tpl';
			$mail = ($this->templateInstance ? $this->createEmailMessage($templateFileName) : $this->ctrl->createEmailMessage($templateFileName));
			$mail->Subject = $this->subjectLine;

			$mail->set('From', $this->fromEmail);
			$mail->set('FromName', $this->fromName);
			$mail->set('Sender', $this->fromEmail);
			$mail->AddReplyTo($this->fromEmail, $this->fromName);

			$recipients = array();

			if ($this->testingOnly && $this->testEmail)
			{
				// send only to testing address
				$mail->AddAddress($this->testEmail, "Testing Academic Technology");
				$recipients[] = -1;
			}
			elseif (is_array($user) && (count($user) > 1))
			{
				// send to multiple recipients
				foreach ($user as $recipient)
				{
					$recipient = is_array($recipient) ? array_shift($recipient) : $recipient;
					$mail->AddAddress($recipient->emailAddress, $recipient->fullName);
					$recipients[] = $recipient->id;
				}
			}
			else
			{
				// send to a single specified recipient
				if (is_array($user) && array_shift($user))
				{
					$user = array_shift($user);
				}				
				if ($user)
				{
					$email = $user->emailAddress ?? '';
					$name = ($user->fullName ?? $user->displayName) ?? (($user->firstName . ' ' . $user->lastName) ?? '');
					$id = $user->id;				
				}
				else
				{
					$email = '';
					$name = '';
					$id = -1;				
				}

				$mail->AddAddress($email, $name);
				$recipients[] = $id;
			}

			foreach ($this->attachments as $attachment)
			{
				$title = isset($attachment->title) ? $attachment->title : $attachment->remoteName;
				$mail->AddAttachment($attachment->getLocalFilename(true), $title);
			}
			if ($this->ccRequest && !$this->testingOnly && !$this->testEmail)
			{
				$mail->AddAddress($this->fromEmail, $this->fromName);
				$recipients[] = '[CC] at@sfsu.edu';
			}

			$mail->getTemplate()->message = $preppedText;
			$mail->getTemplate()->messageTitle = $messageTitle;
			$mail->getTemplate()->signature = $this->app->siteSettings->getProperty('email-signature', "<br><p>&nbsp;&nbsp;&mdash;Academic Technology</p>");
			
			$success = $mail->Send();
        
			// finish email log
			$emailLog = $this->getSchema('Classrooms_Email_EmailLog')->get($this->emailLogId);
			$emailLog->recipients = implode(',', $recipients);
			$emailLog->subject = $this->subjectLine;
			$emailLog->body = $preppedText;
			$emailLog->attachments = $this->attachments;
			$emailLog->success = $success;
			$emailLog->save();
		}
	}

    public function createEmailTemplate ()
    {
        $template = $this->templateInstance;
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }
    
    
    public function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->app);

        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            if ($this->ctrl)
            {
            	$message->setTemplate($tpl, $this->ctrl->getModule()->getResource($contentTemplate));
            }
            else
            {
            	$message->setTemplate($tpl, $this->app->moduleManager->getModule('at:classrooms:master')->getResource($contentTemplate));
            }

        }
        
        return $message;
    }

	private function hasContent ($text)
	{
		return (strlen(strip_tags(trim($text))) > 1);
	}

	private function generateLink ($url='', $asAnchor=true, $linkText='')
	{
		$href = $this->app->baseUrl($url);
		if ($asAnchor)
		{
			$text = $linkText ?? $href;
			return '<a href="' . $href . '">' . $text . '</a>';
		}
		
		return $href;
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