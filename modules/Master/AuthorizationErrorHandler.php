<?php

/**
 * Workstation Selection application error handler for authorization
 * errors. This error can be caused by:
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Master_AuthorizationErrorHandler extends Classrooms_Master_ErrorHandler
{
    public static function getErrorClassList () { return [ 'Bss_AuthZ_ExPermissionDenied' ]; }
    
    protected function getStatusCode () { return 403; }
    protected function getStatusMessage () { return 'Forbidden'; }
    protected function getTemplateFile () { return 'error-403-unauthorized.html.tpl'; }
    
    protected function handleError ($error)
    {
        $this->setupAuthenticationError($error);
        $this->template->didLogin = $error->getRequest()->getCookie('didLogin');
        parent::handleError($error);
    }
}
