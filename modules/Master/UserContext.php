<?php

/**
 * Represents a user of Workstation Selection application, regardless of whether they are logged in
 * or not.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Master_UserContext extends Bss_Master_UserContext
{
    protected function setAccount ($account)
    {
        parent::setAccount($account);
        
        if ($account)
        {
            $authZ = $this->getAuthorizationManager();
            if ($authZ->hasPermission($account, 'edit') || $authZ->hasPermission($account, 'view schedules'))
            {
                $this->response->redirect('/');
            }
            else
            {
                if ($return = $this->request->getQueryParameter('returnTo'))
                {
                    $this->response->redirect($return);
                }
                else
                {
                    $this->response->redirect('schedules');
                }
            }
        }
        $this->response->redirect('rooms');
    }
}
