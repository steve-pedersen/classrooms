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
    public function login (Bss_AuthN_Account $account)
    {
        $firstLogin = ($account->firstLoginDate === null);
        parent::login($account);
        $this->sendRedirect($account, $firstLogin);
    }

    public function sendRedirect ($account, $firstLogin)
    {
        $authZ = $this->getAuthorizationManager();
        $returnTo = isset($_SESSION['returnTo']) ? $_SESSION['returnTo'] : null;
        $returnToWithParams = false;
        if (isset($_SESSION['returnToQueryString']))
        {
            $returnToWithParams = true;
            $returnTo .= '?' . $_SESSION['returnToQueryString'];
        }
        
        if ($returnToWithParams)
        {
            $this->response->redirect($returnTo);
        }
        elseif ($return = $this->request->getQueryParameter('returnTo', $returnTo))
        {
            $this->response->redirect($return);
        }
        elseif ($authZ->hasPermission($account, 'edit'))
        {
            if ($returnTo) $this->response->redirect($returnTo);
            $this->response->redirect('/');
        }
        elseif ($authZ->hasPermission($account, 'view schedules'))
        {
            if ($returnTo) $this->response->redirect($returnTo);
            $this->response->redirect('/schedules');
        }

        $this->response->redirect('rooms');
    }
}
