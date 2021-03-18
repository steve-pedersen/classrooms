<?php

/**
 * Manager to integrate ClassData with Classrooms DB Account data.
 *
 * @author Steve Pedersen (pedersen@sfsu.edu)
 */
class Classrooms_ClassData_AccountManager
{
    private $application;

    public function __construct($app)
    {
        $this->application = $app;
    }

    public function hasEnrollment ($identity)
    {
        $schema =  $this->schema('Classrooms_ClassData_Enrollment');
        return $schema->findOne($schema->userId->equals($identity->getProperty('username')));
    }

    public function hasClassData ($identity)
    {
        return $this->schema('Classrooms_ClassData_User')->get($identity->getProperty('username'));
    }

    public function checkAndCreateFacultyAccount ($identity)
    {
        $deptUsers = $this->schema('Classrooms_Department_User');
        $accounts = $this->schema('Bss_AuthN_Account');
        $account = $accounts->createInstance();
        
        if ($classDataUser = $this->schema('Classrooms_ClassData_User')->get($identity->getProperty('username')))
        {
            $isFaculty = false;
            foreach ($classDataUser->enrollments as $user)
            {
                if ($classDataUser->enrollments->getProperty($user, 'role') === 'instructor')
                {
                    $account = $this->createFacultyAccount($account, $classDataUser);
                    break;
                }
            }
        }
        elseif ($departmentUser = $deptUsers->findOne($deptUsers->sfStateId->equals($identity->getProperty('username'))))
        {
            $account = $this->createDepartmentUserAccount($identity, $departmentUser);
        }

        return $account;
    }

    public function createFacultyAccount ($account, $classDataUser)
    {
        $account->username = $classDataUser->id;
        $account->emailAddress = $classDataUser->emailAddress ?? '';
        $account->firstName = $classDataUser->firstName;
        $account->lastName = $classDataUser->lastName;
        $account->createdDate = $account->createdDate ?? new DateTime;
        $account->isFaculty = true;
        $account->save();

        return $this->grantRole($account, $classDataUser, 'Faculty');
    }

    public function createDepartmentUserAccount ($identity, $departmentUser)
    {
        $deptUsers = $this->schema('Classrooms_Department_User');
        $accounts = $this->schema('Bss_AuthN_Account');
        $account = $accounts->findOne($accounts->username->equals($departmentUser->sfStateId)) ?? $accounts->createInstance();
        $account->username = $departmentUser->sfStateId;
        $account->emailAddress = $departmentUser->emailAddress ?? '';
        $account->firstName = $departmentUser->firstName;
        $account->lastName = $departmentUser->lastName;
        $account->createdDate = new DateTime;
        $account->isFaculty = false;
        $account->save();

        return $this->grantRole($account, $departmentUser, 'Management');        
    }

    public function grantRole ($account, $userData, $role='Faculty')
    {
        $roles = $this->schema('Classrooms_AuthN_Role');
        $departments = $this->schema('Classrooms_Department_Department');
        $userDepartments = [];
        $isStudent = false;

        $accountRole = $roles->findOne($roles->name->equals($role));
        $account->roles->add($accountRole);
        $account->roles->save();        

        return $account;
    }

    public function schema ($name)
    {
        return $this->application->schemaManager->getSchema($name);
    }
}