<?php

/**
 * Workstation Selection application error handler for when a user
 * authenticates but does not have an account. This can only be caused with
 * identity provider implementations that authenticate against remote identity
 * providers.
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_AuthN_NoAccountErrorHandler extends Classrooms_Master_ErrorHandler
{
    public static function getErrorClassList () { return [ 'Bss_AuthN_ExNoAccount' ]; }
    
    protected function getStatusCode () { return 403; }
    protected function getStatusMessage () { return 'Forbidden'; }
    protected function getTemplateFile () { return 'error-403-no-account.html.tpl'; }
    
    protected function handleError ($error)
    {
        $identity = $error->getExtraInfo();
        
        if (!$identity->getAuthenticated())
        {
            // To avoid leaking information, we only handle NoAccount if the
            // identity provider has authenticated the identity (i.e., the
            // person is who they say they are, they just don't have an
            // account).
            
            // Specifically, for the PasswordAuthentication system, this means
            // that the error page is the same if someone enters a non-existent
            // username AND if someone enters an existing username with the
            // wrong password.
            
            $this->forwardError('Bss_AuthN_ExAuthenticationFailure', $error);
        }

        if (($username = $identity->getProperty('username')))
        {
            $accountManager = new Classrooms_ClassData_AccountManager($this->getApplication());
            $account = $accountManager->checkAndCreateFacultyAccount($identity);

            if ($account->isFaculty)
            {
                $this->request = $error->getRequest();
                $this->response = $error->getResponse();

                $this->getUserContext()->login($account);
            }
            else
            {
                $this->forwardError('Classrooms_Faculty_Refresh_ExNoFaculty', $error);
            }
        }
        
        $this->template->identity = $identity;
        $this->template->identityProvider = $identity->getIdentityProvider();
        parent::handleError($error);
    }
}
