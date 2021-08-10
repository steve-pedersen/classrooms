<?php

/**
 * Sends communications for unsent Events that are due
 *
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Communication_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 60; // 60 minutes

    private $userContext = null;
    
    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            set_time_limit(0);
            
            $app = $this->getApplication();
            $schemaManager = $app->schemaManager;
            $eventSchema = $schemaManager->getSchema('Classrooms_Communication_Event');

            $events = $eventSchema->find(
                $eventSchema->allTrue(
                    $eventSchema->sendDate->beforeOrEquals($startTime),
                    $eventSchema->sent->isFalse()
                )
            );
            
            $commManager = new Classrooms_Communication_Manager($app, $this);

            foreach ($events as $event)
            {
                $commManager->processCommunicationEvent($event);
            }

            return true;
        }
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

    public function getUserContext()
    {
        if ($this->userContext === null)
        {
            $request = new Bss_Core_Request($this->getApplication());
            $response = new Bss_Core_Response($request);
            $this->userContext = new Classrooms_Master_UserContext($request, $response);
        }

        return $this->userContext;
    }

    public function createTemplateInstance()
    {
        $tplClass = $this->getTemplateClass();
        $request = new Bss_Core_Request($this->getApplication());
        $response = new Bss_Core_Response($request);
        
        $inst = new $tplClass ($this, $request, $response);

        return $inst;
    }

    protected function getTemplateClass ()
    {
        return 'Classrooms_Master_Template';
    }
}
