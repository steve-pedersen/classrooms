<?php

/**
 */
class Classrooms_AuthN_IdentityProvider extends At_Shibboleth_IdentityProvider
{
    /**
     * Get the identity of the authenticated user.
     * 
     * @param Bss_Core_IRequest $request
     * @return Bss_AuthN_Identity
     */
    public function getIdentity (Bss_Core_IRequest $request)
    {
        if ($identity = parent::getIdentity($request))
        {
            if ($identity->getAuthenticated() && 
                ($account = $identity->getAccount()))
            {
                $identity->setAuthenticated(!$account->disabled);
            }
        }
        
        return $identity;
    }

    private $allowedAffiliationList;
    
    protected function getDefaultAttributeHeaders ()
    {
        return array(
            'username' => 'UID',
            'organization' => 'calstateEduPersonOrg',
            'emailAddress' => 'mail',
            'displayName' => 'displayName',
            'firstName' => 'givenName',
            'lastName' => 'surname',
            'affiliation' => 'calstateEduPersonAffiliation',
        );
    }
    
    protected function getDefaultAllowedAffiliations ()
    {
        return array('Employee Faculty', 'Employee Staff');
    }
    
    protected function configureProvider ($attributeMap)
    {
        parent::configureProvider($attributeMap);
        
        $this->allowedAffiliationsList = array_map(
            array($this, 'normalizeAffiliation'),
            (!empty($attributeMap['allowedAffiliations'])
                ? $attributeMap['allowedAffiliations']
                : $this->getDefaultAllowedAffiliations()
            )
        );
    }
    
    protected function initializeIdentityProperties (Bss_Core_IRequest $request, Bss_AuthN_Identity $identity)
    {
        parent::initializeIdentityProperties($request, $identity);
        
        $identity->setProperty('allowCreateAccount', $this->getAllowCreateAccount($identity));
    }
    

    /**
     *  Allow faculty, staff, and students who teach courses to create accounts.
     */
    protected function getAllowCreateAccount (Bss_AuthN_Identity $identity)
    {
        return false;
    }
    
    protected function normalizeAffiliation ($affiliation)
    {
        return strtolower(trim($affiliation));
    }

    protected function schema ($recordClass)
    {
        return $this->getApplication()->schemaManager->getSchema($recordClass);
    }
}
