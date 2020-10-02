<?php

require_once Bss_Core_PathUtils::path(dirname(__FILE__), 'resources', 'traits', 'Provider.php');

/**
 * The master controller for Workstation Selection application. Put basic functionality that you want
 * the other controllers in your application to have available to them here.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
abstract class Classrooms_Master_Controller extends Bss_Master_Controller
{
    protected function initController ()
    {
        parent::initController();
        
        // Initialize data members here.
        
        // If you want to setup template variables, it's recommended to do that
        // in Classrooms_Master_Template. If you setup template variables
        // here (instead of your template class), they will not be set in
        // framework controllers.
    }

    protected function flash ($content, $class='success') {
        $session = $this->request->getSession();
        $session->flashContent = $content;
        $session->flashClass = $class;
    }

    protected function afterCallback ($callback)
    {
        $session = $this->request->getSession();
        if (isset($session->flashContent))
        {
            $this->template->flashContent = $session->flashContent;
            unset($session->flashContent);
        }
        if (isset($session->flashClass))
        {
            $this->template->flashClass = $session->flashClass;
            unset($session->flashClass);
        }
        
        parent::afterCallback($callback);
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
