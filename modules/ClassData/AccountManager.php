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

    public function createFacultyAccount ($identity)
    {
        $accounts = $this->schema('Bss_AuthN_Account');
        $account = $accounts->createInstance();
        
        if ($classDataUser = $this->schema('Classrooms_ClassData_User')->get($identity->getProperty('username')))
        {
            $isFaculty = false;
            foreach ($classDataUser->enrollments as $user)
            {
                if ($classDataUser->enrollments->getProperty($user, 'role') === 'instructor')
                {
                    $account->username = $classDataUser->id;
                    $account->emailAddress = $identity->getProperty('emailAddress') ?? $classDataUser->emailAddress ?? '';
                    $account->firstName = $classDataUser->firstName;
                    $account->lastName = $classDataUser->lastName;
                    $account->createdDate = new DateTime;
                    $account->isFaculty = true;
                    $account->save();

                    $account = $this->grantRole($account, $classDataUser);
                    break;
                }
            }
        }

        return $account;
    }

    public function createUserAccount ($identity)
    {
        $accounts = $this->schema('Bss_AuthN_Account');
        $account = $accounts->createInstance();
        
        if ($classDataUser = $this->schema('Classrooms_ClassData_User')->get($identity->getProperty('username')))
        {
            $account->username = $classDataUser->id;
            $account->emailAddress = $identity->getProperty('emailAddress') ?? $classDataUser->emailAddress ?? '';
            $account->firstName = $classDataUser->firstName;
            $account->lastName = $classDataUser->lastName;
            $account->createdDate = new DateTime;
            $account->save();

            $account = $this->grantRole($account, $classDataUser);
        }
        else
        {
            $account->username = $identity->getProperty('username');
            $account->firstName = $identity->getProperty('firstName');
            $account->lastName = $identity->getProperty('lastName');
            $account->emailAddress = $identity->getProperty('emailAddress');
            $account->createdDate = new DateTime;
            $account->save();
        }

        return $account;
    }

    public function grantRole ($account, $classDataUser)
    {
        $roles = $this->schema('Classrooms_AuthN_Role');
        $departments = $this->schema('Classrooms_Department_Department');
        $userDepartments = [];
        $isStudent = false;

        foreach ($classDataUser->enrollments as $enrollment)
        {
            if ($classDataUser->enrollments->getProperty($enrollment, 'role') === 'instructor')
            {
                if (empty($userDepartments))
                {
                    $facultyRole = $roles->findOne($roles->name->equals('Faculty'));
                    $account->roles->add($facultyRole);
                    $account->roles->save();
                }
                if (!isset($userDepartments[$enrollment->department->id]))
                {
                    $userDepartments[$enrollment->department->id] = $enrollment->department;
                }
            }
            // elseif ($classDataUser->enrollments->getProperty($enrollment, 'role') === 'student')
            // {
            //     $isStudent = true;        
            // }
        }

        // if ($isStudent)
        // {
        //     $studentRole = $roles->findOne($roles->name->equals('Student'));
        //     $account->roles->add($studentRole);
        //     $account->roles->save();   
        // }

        return $account;
    }

    public function schema ($name)
    {
        return $this->application->schemaManager->getSchema($name);
    }
}