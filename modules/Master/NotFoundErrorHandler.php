<?php

/**
 * Workstation Selection application error handler for 404 errors.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Master_NotFoundErrorHandler extends Classrooms_Master_ErrorHandler
{
    public static function getErrorClassList () { return [ 'Bss_Routing_ExNotFound' ]; }
    
    protected function getStatusCode () { return 404; }
    protected function getStatusMessage () { return 'Not Found'; }
    protected function getTemplateFile () { return 'error-404.html.tpl'; }
}
