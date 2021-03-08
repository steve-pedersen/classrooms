<?php

/**
 */
class Classrooms_Email_Controller extends Classrooms_Master_Controller
{
    public static function getRouteMap ()
    {
        return array(        
            '/admin/settings/email' => array('callback' => 'emailSettings'),
            '/admin/files/:fid/download' => array( 'callback' => 'download', 'fid' => '[0-9]+'),
        );
    }
    
    protected function beforeCallback ($callback)
    {
        parent::beforeCallback($callback);
        $this->requirePermission('admin');
        $this->template->clearBreadcrumbs();
        $this->addBreadcrumb('home', 'Home');
        $this->addBreadcrumb('admin', 'Admin');
        // if admin and on admin page, don't display 'Contact' sidebar
        $this->template->adminPage = $this->hasPermission('admin') && (strpos($this->request->getFullRequestedUri(), 'admin') !== false);
    }

    public function download ()
    {
        $account = $this->requireLogin();
        
        $fid = $this->getRouteVariable('fid');
        $file = $this->requireExists($this->schema('Classrooms_Email_File')->get($fid));
        
        if ($file->uploadedBy && ($account->id != $file->uploadedBy->id))
        {
            
            if ($item = $this->getRouteVariable('item'))
            {
                $authZ = $this->getAuthorizationManager();
                $extension = $item->extension;
                
                if ($authZ->hasPermission($account, $extension->getItemViewTask(), $item))
                {
                    $file->sendFile($this->response);
                }
            }
            
            // $this->requirePermission('file download');
        }
        
        $file->sendFile($this->response);
    }
  

    public function updateEmailAttachments ($attachmentData)
    {
        $files = $this->schema('Classrooms_Email_File');
        $attachedFiles = array();

        foreach ($attachmentData as $emailKey => $fileIds)
        {
            foreach ($fileIds as $fileId)
            {
                if (!isset($attachedFiles[$fileId]))
                {
                    $attachedFiles[$fileId] = array();
                }
                if (!in_array($emailKey, $attachedFiles[$fileId]))
                {
                    $attachedFiles[$fileId][] = $emailKey;
                }
            }
        }

        // make sure each file matches the state of posted data
        foreach ($files->getAll() as $file)
        {
            if (!in_array($file->id, array_keys($attachedFiles)))
            {
                $file->attachedEmailKeys = array();
            }
            else // make sure all the files match the posted data
            {
                $file->attachedEmailKeys = $attachedFiles[$file->id];
            }
            $file->save();
        }
    }

    public function emailSettings ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        $files = $this->schema('Classrooms_Email_File');
        $removedFiles = array();
        $reminderOptions = array('1 day', '2 days', '12 hours', '6 hours', '2 hours', '1 hour');

        if ($this->request->wasPostedByUser())
        {
            if ($removedFiles = $this->request->getPostParameter('removed-files', array()))
            {
                $removedFiles = $files->find($files->id->inList($removedFiles), array('arrayKey' => 'id'));
            }

            if ($attachments = $this->request->getPostParameter('attachments'))
            {
                $attRecords = $files->find($files->id->inList($attachments));
                
                foreach ($attRecords as $record)
                {
                    if (empty($removedFiles[$record->id]))
                    {
                        $attachments[$record->id] = $record;
                    }
                }
            }

            switch ($this->getPostCommand()) {
                case 'upload':
                    $file = $files->createInstance();
                    $file->createFromRequest($this->request, 'attachment');
                    
                    if ($file->isValid())
                    {
                        $file->uploadedBy = $this->getAccount();
                        $file->save();

                        $this->flash('The file has been uploaded to the server.');
                        $this->response->redirect('admin/settings/email');
                    }
                    
                    $this->template->errors = $file->getValidationMessages();
                    break;

                case 'remove-attachment':
                    $command = $this->request->getPostParameter('command');
                    $tmpArray = array_keys($command['remove-attachment']);
                    $id = array_shift($tmpArray);
                    if ($fileToRemove = $files->get($id))
                    {
                        $removedFiles[$fileToRemove->id] = $fileToRemove;
                        $fileToRemove->delete();
                    }

                    $this->flash("This file has been removed from the server.");
                    break;

                case 'save':
                    $testing = $this->request->getPostParameter('testingOnly');
                    $testingOnly = ((is_null($testing) || $testing === 0) ? 0 : 1);
                    $siteSettings->setProperty('email-testing-only', $testingOnly);
                    $siteSettings->setProperty('email-test-address', $this->request->getPostParameter('testAddress'));
                    $siteSettings->setProperty('email-default-address', $this->request->getPostParameter('defaultAddress'));
                    $siteSettings->setProperty('email-signature', $this->request->getPostParameter('signature'));
                    $siteSettings->setProperty('email-new-account', $this->request->getPostParameter('newAccount'));
                    $siteSettings->setProperty('email-course-allowed-teacher', $this->request->getPostParameter('courseAllowedTeacher'));
                    $siteSettings->setProperty('email-course-allowed-students', $this->request->getPostParameter('courseAllowedStudents'));
                    $siteSettings->setProperty('email-course-denied', $this->request->getPostParameter('courseDenied'));
                    $siteSettings->setProperty('email-course-requested-admin', $this->request->getPostParameter('courseRequestedAdmin'));
                    $siteSettings->setProperty('email-course-requested-teacher', $this->request->getPostParameter('courseRequestedTeacher'));
                    $siteSettings->setProperty('email-reservation-details', $this->request->getPostParameter('reservationDetails'));
                    $siteSettings->setProperty('email-reservation-reminder-time', $this->request->getPostParameter('reservationReminderTime'));
                    $siteSettings->setProperty('email-reservation-reminder', $this->request->getPostParameter('reservationReminder'));
                    $siteSettings->setProperty('email-reservation-missed', $this->request->getPostParameter('reservationMissed'));
                    $siteSettings->setProperty('email-reservation-canceled', $this->request->getPostParameter('reservationCanceled'));

                    $attachmentData = $this->request->getPostParameter('attachment');
                    $this->updateEmailAttachments($attachmentData);

                    $this->flash("Children's Campus email settings and content have been saved.");
                    $this->response->redirect('admin/settings/email');
                    exit;
                    
                case 'sendtest':
                    $viewer = $this->getAccount();
                    $command = $this->request->getPostParameter('command');
                    $which = array_keys($command['sendtest']);
                    $which = array_pop($which);

                    if ($which)
                    {
                        $emailData = array();
                        $emailData['user'] = $viewer;
                        $emailManager = new Classrooms_Email_EmailManager($this->getApplication(), $this);                   

                        switch ($which) 
                        {
                            case 'newAccount':
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);
                                
                                $this->template->sendSuccess = 'You should receive a test email momentarily for New-Account template.';
                                break;

                            case 'courseRequestedAdmin':
                                $emailData['requestingUser'] = $viewer;
                                $emailData['courseRequest'] = new stdClass();
                                $emailData['courseRequest']->id = 0;
                                $emailData['courseRequest']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['courseRequest']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['courseRequest']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);
                                
                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Requested-Admin template.';
                                break;

                            case 'courseRequestedTeacher':
                                $emailData['courseRequest'] = new stdClass();
                                $emailData['courseRequest']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['courseRequest']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['courseRequest']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Requested-Teacher template.';                                
                                break;

                            case 'courseAllowedTeacher':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->id = 0;
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->openDate = new DateTime;
                                $emailData['course']->lastDate = new DateTime('now + 1 month');
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Allowed-Teacher template.';
                                break;

                            case 'courseAllowedStudents':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->openDate = new DateTime;
                                $emailData['course']->lastDate = new DateTime('now + 1 month');
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Allowed-Students template.';
                                break;

                            case 'courseDenied':
                                $emailData['course'] = new stdClass();
                                $emailData['course']->fullName = 'TEST: Introduction to Childhood Development';
                                $emailData['course']->shortName = 'TEST-CAD-0101-01-Spring-2025';
                                $emailData['course']->semester = 'TEST Spring 2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Course-Denied template.';                       
                                break;

                            case 'reservationDetails':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->id = 0;
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailData['reservation']->room = 'TEST CC-221';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Details template.';  
                                break;
                            
                            case 'reservationReminder':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->id = 0;
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailData['reservation']->room = 'TEST CC-221';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Reminder template.';  
                                break;

                            case 'reservationMissed':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation']->startTime = new DateTime;
                                $emailData['reservation']->purpose = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Reminder template.';  
                                break;

                            case 'reservationCanceled':
                                $emailData['reservation'] = new stdClass();
                                $emailData['reservation_date'] = new DateTime;
                                $emailData['reservation_purpose'] = 'TEST Observation only course - TEST-CAD-0101-01-Spring-2025';
                                $emailManager->processEmail('send' . ucfirst($which), $emailData, true);

                                $this->template->sendSuccess = 'You should receive a test email momentarily for Reservation-Reminder template.';  
                                break;
                        }
                    }
            }
        }

        $accounts = $this->schema('Bss_AuthN_Account');
        $this->template->systemNotificationRecipients = $accounts->find($accounts->receiveAdminNotifications->isTrue());
        $this->template->authZ = $this->getApplication()->authorizationManager;
        $this->template->removedFiles = $removedFiles;
        $this->template->attachments = $files->getAll();
        $this->template->testingOnly = $siteSettings->getProperty('email-testing-only', 0);
        $this->template->testAddress = $siteSettings->getProperty('email-test-address');
        $this->template->defaultAddress = $siteSettings->getProperty('email-default-address');
        $this->template->signature = $siteSettings->getProperty('email-signature');
        $this->template->newAccount = $siteSettings->getProperty('email-new-account');
        $this->template->courseRequestedAdmin = $siteSettings->getProperty('email-course-requested-admin');
        $this->template->courseRequestedTeacher = $siteSettings->getProperty('email-course-requested-teacher');
        $this->template->courseAllowedTeacher = $siteSettings->getProperty('email-course-allowed-teacher');
        $this->template->courseAllowedStudents = $siteSettings->getProperty('email-course-allowed-students');
        $this->template->courseDenied = $siteSettings->getProperty('email-course-denied');       
        $this->template->reservationDetails = $siteSettings->getProperty('email-reservation-details');
        $this->template->reservationReminder = $siteSettings->getProperty('email-reservation-reminder');
        $this->template->reservationReminderTime = $siteSettings->getProperty('email-reservation-reminder-time');
        $this->template->reservationMissed = $siteSettings->getProperty('email-reservation-missed');
        $this->template->reservationCanceled = $siteSettings->getProperty('email-reservation-canceled');
        $this->template->reminderOptions = $reminderOptions;
    }
    


    public function sendReservationCanceledNotification ($blockedDate)
    {

        $reservations = $this->schema('Ccheckin_Rooms_Reservation');
        $blocked = new DateTime($blockedDate);
        $canceled = array();

        $cond = $reservations->allTrue(
            $reservations->startTime->after(new DateTime()),
            $reservations->missed->isNull()->orIf($reservations->missed->isFalse()),
            $reservations->checkedIn->isNull()->orIf($reservations->checkedIn->isFalse())

        );
        $upcoming = $reservations->find($cond);

        foreach ($upcoming as $reservation)
        {
            if ($blocked->format('Y/m/d') === $reservation->startTime->format('Y/m/d'))
            {
                $canceled[] = $reservation;
            }
        }

        $emailManager = new Classrooms_Email_EmailManager($this->getApplication(), $this);
        
        // notify students of cancellation
        foreach ($canceled as $reservation)
        {
            $emailData = array();        
            $emailData['reservation_date'] = $reservation->startTime;
            $emailData['reservation_purpose'] = $reservation->observation->purpose->shortDescription;
            $emailData['user'] = $reservation->account;
            $emailManager->processEmail('sendReservationCanceled', $emailData);

            $observation = $reservation->observation;
            $reservation->delete();
            $observation->delete();
        }
    }
}
