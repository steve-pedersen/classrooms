<?php

/**
 */
class Classrooms_Communication_Controller extends Classrooms_Master_Controller
{
    public static function getRouteMap ()
    {
        return [
            '/admin/communications' => ['callback' => 'communications'],
            '/admin/communications/:id' => ['callback' => 'editCommunication', ':id' => '[0-9]+|new'],
            '/admin/communications/:cid/events/:eid' => ['callback' => 'editCommunicationEvent', ':cid' => '[0-9]+', ':eid' => '[0-9]+|new'],
            'admin/cron' => ['callback' => 'cron'],
        ];
    }

    public function communications ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit');
        $this->addBreadcrumb($this->baseUrl(''), 'Home');
        $this->setPageTitle('Manage Communications');

        $schema = $this->schema('Classrooms_Communication_Communication');

        $this->template->communications = $schema->getAll(['orderBy' => '-creationDate']);
    }

    public function editCommunication ()
    {
        $this->requirePermission('edit');
        $siteSettings = $this->getApplication()->siteSettings;
        $this->addBreadcrumb('admin/communications', 'Manage Communication');
        $comm = $this->helper('activeRecord')->fromRoute('Classrooms_Communication_Communication', 'id', array('allowNew' => true));

        $this->addBreadcrumb('', ($comm->inDatasource ? 'Edit' : 'New') . ' Communication');
        $this->setPageTitle(($comm->inDatasource ? 'Edit' : 'Create') . ' Faculty Communication');

        $roomSchema = $this->schema('Classrooms_Room_Location');
        $typeSchema = $this->schema('Classrooms_Room_Type');
        $courseSchema = $this->schema('Classrooms_ClassData_CourseSection');

        $nonlabRooms = [];
        $nonlabRoomTypes = $typeSchema->find($typeSchema->name->notEquals('Lab'));
        foreach ($nonlabRoomTypes as $type)
        {
            foreach ($type->locations as $location)
            {
                $nonlabRooms[$location->id] = $location;
            }
        }
        
        $labRooms = $typeSchema->findOne($typeSchema->name->equals('Lab'))->locations;
        $unconfiguredRooms = $roomSchema->find($roomSchema->allTrue(
            $roomSchema->deleted->isFalse()->orIf($roomSchema->deleted->isNull()),
            $roomSchema->configured->isFalse()->orIf($roomSchema->configured->isNull())
        ), ['arrayKey' => 'id', 'orderBy' => ['building_id', 'number']]);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand()) {
                case 'delete':
                    $comm->delete();
                    $this->flash('The faculty communication has been deleted');
                    $this->response->redirect('admin/communications');
                    break;

                case 'save':
                    $this->processSubmission($comm, ['roomMasterTemplate', 'labRoom', 'nonlabRoom', 'unconfiguredRoom', 'noRoom']);

                    if ($comm->isValid())
                    {
                        if (!$comm->inDatasource)
                        {
                            $comm->creationDate = new DateTime;
                        }

                        $comm->save();
                        $this->flash('The faculty communication has been saved');
                        $this->response->redirect('admin/communications');
                    }
                    break;

                case 'send':
                    $viewer = $this->getAccount();
                    $this->processSubmission($comm, ['roomMasterTemplate', 'labRoom', 'nonlabRoom', 'unconfiguredRoom', 'noRoom']);
                    $command = $this->request->getPostParameter('command');
                    $which = array_keys($command['send']);
                    $which = array_pop($which);
                    
                    if ($which)
                    {
                        $manager = new Classrooms_Communication_Manager($this->getApplication(), $this);
                        $event = $this->schema('Classrooms_Communication_Event')->createInstance();
                        $now = date('Y');
                        $event->termYear = $now[0] . $now[2] . $now[3] . '7';
                        $event->communication = $comm;
                        
                        $rooms = $this->request->getPostParameter('rooms');
                        $labIds = isset($rooms['lab']) && !empty($rooms['lab']) ? $rooms['lab'] : [];
                        $nonlabIds = isset($rooms['nonlab']) && !empty($rooms['nonlab']) ? $rooms['nonlab'] : [];
                        $unconfiguredIds = isset($rooms['unconfigured']) && !empty($rooms['unconfigured']) ? $rooms['unconfigured'] : [];

                        switch ($which) 
                        {
                            case 'roomMasterTemplate':
                                $selectedLabs = $roomSchema->find($roomSchema->id->inList($labIds), 
                                    ['arrayKey' => 'id', 'orderBy' => 'building_id', 'number']
                                );
                                $selectedNonlabs = $roomSchema->find($roomSchema->id->inList($nonlabIds), 
                                    ['arrayKey' => 'id', 'orderBy' => 'building_id', 'number']
                                );
                                $selectedUnconfigured = $roomSchema->find($roomSchema->id->inList($unconfiguredIds), 
                                    ['arrayKey' => 'id', 'orderBy' => 'building_id', 'number']
                                );

                                $course = $courseSchema->findOne($courseSchema->year->equals($now));
                                
                                $labs = [];
                                if (!empty($selectedLabs))
                                {
                                    foreach ($selectedLabs as $id => $room)
                                    {
                                        $labs[] = ['room' => $room, 'course' => $course];
                                    }
                                }

                                $nonlabs = [];
                                if (!empty($selectedNonlabs))
                                {
                                    foreach ($selectedNonlabs as $id => $room)
                                    {
                                        $nonlabs[] = ['room' => $room, 'course' => $course];
                                    }
                                }

                                $unconfigured = [];
                                if (!empty($selectedUnconfigured))
                                {
                                    foreach ($selectedUnconfigured as $id => $room)
                                    {
                                        $unconfigured[] = ['room' => $room, 'course' => $course];
                                    }
                                }

                                $norooms = [];
                                for ($i = 0; $i < 3; $i++)
                                {
                                    $course = new stdClass();
                                    $course->fullDisplayName = 'Sample class without a room ' . ($i+1);
                                    $norooms[] = ['course' => $course];
                                }

                                if (!empty($labs) || !empty($nonlabs) || !empty($unconfigured) || !empty($norooms))
                                {
                                    $manager->sendRoomMasterTemplate(
                                        ['labs' => $labs, 
                                        'nonlabs' => $nonlabs, 
                                        'unconfigured' => $unconfigured,
                                        'norooms' => $norooms], 
                                        $viewer, $event
                                    );
                                    $this->template->sendSuccess = 'You should receive a test email momentarily for the template.';
                                }
                                else
                                {
                                    $this->template->errors = ['roomMasterTemplate' => ['You must choose at least one room.']];
                                }

                                break;
                        }
                    }
                default:
                	break;
            }
        }

        $this->template->nonlabRooms = $nonlabRooms;
        $this->template->labRooms = $labRooms;
        $this->template->unconfiguredRooms = $unconfiguredRooms;
        $this->template->comm = $comm; 
    }


    public function editCommunicationEvent ()
    {
        $this->requirePermission('edit');
        $comm = $this->helper('activeRecord')->fromRoute('Classrooms_Communication_Communication', array('cid' => 'id'));
        $event = $this->helper('activeRecord')->fromRoute('Classrooms_Communication_Event', array('eid' => 'id'), array('allowNew' => true));
        $this->addBreadcrumb('admin/communications', 'Manage Communications');
        $this->addBreadcrumb('admin/communications/' . $comm->id, 'Edit Communication');
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand()) {
                case 'save':
                    $event->sendDate = new DateTime($this->request->getPostParameter('sendDate'));
                    $year = $this->request->getPostParameter('year', '');
                    $term = $this->request->getPostParameter('term', '');
                    $event->termYear = $year . $term;

                    if ($event->isValid())
                    {
                        if (!$event->inDatasource)
                        {
                            $event->creationDate = new DateTime;
                            $event->sent = false;
                        }

                        $event->communication_id = $comm->id;
                        $event->save();
                        $this->flash('The event has been saved');
                        $this->response->redirect('admin/communications');
                    }
                    break;
                
                default:
                    # code...
                    break;
            }
        }

        $terms = ['1' => 'Winter', '3' => 'Spring', '5' => 'Summer', '7' => 'Fall'];
        $years = [];
        $rs = pg_query("select distinct year from classroom_classdata_course_sections");
        while (($row = pg_fetch_row($rs)))
        {
            $years[(string)$row[0][0].$row[0][2].$row[0][3]] = $row[0];
        }

        $this->template->eventTerm = $event->inDatasource ? $event->termYear[3] : '';
        $this->template->eventYear = $event->inDatasource ? substr($event->termYear, 0, 3) : '';
        $this->template->terms = $terms;
        $this->template->years = $years;
        $this->template->comm = $comm;
        $this->template->event = $event;
    }

    public function cron ()
    {
        $moduleManager = $this->application->moduleManager;
        $xp = $moduleManager->getExtensionPoint('bss:core:cron/jobs');
        $lastRunDates = $xp->getLastRunDates();
        $cronJobMap = array();
        
        if ($this->request->wasPostedByUser() && $this->getPostCommand() === 'invoke')
        {
            $data = $this->getPostCommandData();
            $now = new DateTime;
            
            foreach ($data as $name => $nonce)
            {
                if (($job = $xp->getExtensionByName($name)))
                {
                    $xp->runJob($name);
                    $lastRunDates[$name] = $now;
                }
            }
        }
        
        foreach ($xp->getExtensionDefinitions() as $jobName => $jobInfo)
        {
            $cronJobMap[$jobName] = array(
                'name' => $jobName,
                'instanceOf' => $jobInfo[0],
                'module' => $jobInfo[1],
                'lastRun' => (isset($lastRunDates[$jobName]) ? $lastRunDates[$jobName]->format('c') : 'never'),
            );
        }
        
        $this->template->cronJobs = $cronJobMap;
    }

    public function createEmailTemplate ()
    {
        $template = $this->createTemplateInstance();
        $template->setMasterTemplate(Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'email.html.tpl'));
        return $template;
    }
    
    public function createEmailMessage ($contentTemplate = null)
    {
        $message = new Bss_Mailer_Message($this->getApplication());
        
        if ($contentTemplate)
        {
            $tpl = $this->createEmailTemplate();
            $message->setTemplate($tpl, $this->getModule()->getResource($contentTemplate));
        }
        
        return $message;
    }

}