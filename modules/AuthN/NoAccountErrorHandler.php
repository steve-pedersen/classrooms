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
            $facultySchema = $this->schema('Classrooms_Purchase_Faculty');

            $faculty = $facultySchema->findOne($facultySchema->allTrue(
                $facultySchema->SFSUid->equals($username),
                $facultySchema->deleted->isFalse()
            ));

            if ($faculty)
            {
                $accounts = $this->schema('Bss_AuthN_Account');
                $account = $accounts->createInstance();

                $account->firstName = $faculty->firstName;
                $account->lastName = $faculty->lastName;
                $account->emailAddress = $faculty->email;
                $account->username = $faculty->SFSUid;
                $account->faculty_id = $faculty->id;
                $account->createdDate = new DateTime;
                $account->save();

                $roleName = $this->getApplication()->getConfiguration()->getProperty('facultyRequest.authorization.role', 'Faculty');
                $roleSchema = $this->schema('Classrooms_AuthN_Role');

                if ($facultyRole = $roleSchema->findOne($roleSchema->name->equals($roleName)))
                {
                    $account->roles->add($facultyRole);
                }

                $account->roles->save();

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
