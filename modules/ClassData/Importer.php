<?php

class Classrooms_ClassData_Importer
{   
    const DATASOURCE_ALIAS = 'classroom_classdata';
    const ACCOUNTS_TABLE = 'classroom_classdata_users';
    const ENROLLMENTS_TABLE = 'classroom_classdata_enrollments';
    const COURSES_TABLE = 'classroom_classdata_courses';
    

    static $SimsAccountFieldMap = array(
        'Firstname' => 'firstName',
        'Lastname' => 'lastName',
        'SFSUid' => 'username',
        'Email' => 'emailAddress',
    );
    
    private $application;
    private $urlBase;
    private $apiKey;
    private $apiSecret;
    private $channel;
    private $dataSource;
    private $sims_service;
    private $r;
    private $activeSemester;
    private $allDepartments;
    private $allCourses;


    public function __construct($app, $channel = 'raw')
    {
        $this->application = $app;
        $config = $app->configuration;
        $this->urlBase = $config->getProperty('classdata.url') ?? 'https://classdata.sfsu.edu/';
        $this->apiKey = $config->getProperty('classdata.key') ?? 'ca1a3f6f-7cac-4e52-9a0a-5cbf82b16bc9';
        $this->apiSecret = $config->getProperty('classdata.secret') ?? '4af2614e-142d-4db8-8512-b3ba13dd0143';
        $this->channel = $channel;
    }

    public function getApplication ()
    {
        return $this->application;
    }

    public function getDataSource ($alias = 'default')
    {
        return $this->getApplication()->dataSourceManager->getDataSource($alias);
    }
    public function schema ($name)
    {
        return $this->application->schemaManager->getSchema($name);
    }

    public function findEnrollments ($accountId)
    {
        $enrollments = $this->schema('Classrooms_ClassData_Enrollment');
        $enrollments->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        
        return $enrollments->find($enrollments->sfsuId->lower()->equals(strtolower($accountId)));
    }
    
    public function findEnrollmentsByCourses ($courseKeys)
    {
        $courseKeys = (array)$courseKeys;
        
        $enrollments = $this->schema('Classrooms_ClassData_Enrollment');
        $enrollments->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        
        return $enrollments->find($enrollments->externalCourseKey->inList($courseKeys));
    }

    public function findCourses ($keys, $keyType = 'ssid')
    {
        $courses = $this->schema('Classrooms_ClassData_Course');
        $courses->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        return $courses->find($courses->externalCourseKey->inList($keys));
    }

    public function findSemesterCourses ($semesterCode)
    {
        $courses = $this->schema('Classrooms_ClassData_Course');
        $courses->setDefaultDataSourceAlias(self::DATASOURCE_ALIAS);
        return $courses->find($courses->externalCourseKey->like($semesterCode . '-%'));
    }
  
    public function importDepartments ()
    {   
        $service = new Classrooms_ClassData_Service($this->getApplication());
        list($code, $data) = $service->getDepartments();
        
        if ($code === 200)
        {
            $departments = $this->schema('Classrooms_Department_Department');
            $allDepartments = $departments->findValues(['name' => 'id']);
            
            foreach ($data['departments'] as $departmentName => $info)
            {
                if (!isset($allDepartments[$departmentName]))
                {               
                    $department = $departments->createInstance();
                    $department->createdDate = new DateTime;
                    $department->modifiedDate = new DateTime;
                    $department->name = $departmentName;
                    $department->save();
                    $allDepartments[$departmentName] = $departmentName;
                }
            }
        }
    }

    public function syncDepartments ()
    {
        $viewer = $this->schema('Bss_AuthN_Account')->get(1);
        $schema = $this->schema('Classrooms_Department_Department');
        $users = $this->schema('Classrooms_Department_User');
        $accountManager = new Classrooms_ClassData_AccountManager($this->getApplication());
        
        $service = new Classrooms_ClassData_Service($this->getApplication());
        $departments = $service->getOrganizations()[1]['organizations'];

        $count = 0;
        foreach ($departments as $key => $dept)
        {
            $department = $schema->findOne($schema->code->equals($key));
            $department = $department && $department->id ? $department : $schema->createInstance();
            $department->modifiedDate = $department && $department->id && $department->name == $dept['name'] && $department->code == $key && $department->modifiedDate ? $department->modifiedDate : new DateTime;
            $department->name = $dept['name'];
            $department->code = $key;
            $department->createdDate = $department->createdDate ?? new DateTime;
            $department->save();
            $count++;
        }
        
        $personnel = $service->getPersonnel()[1]['personnel'];
        ksort($personnel);
        $personnelChanges = [];
        
        foreach ($personnel['colleges'] as $cid => $college)
        {
            foreach ($college['departments'] as $key => $dept)
            {   
                $department = $schema->findOne($schema->code->equals($key));
                $personnelChanges[$department->name] = ['add' => [], 'remove' => []];
                if (!empty($dept['people']))
                {
                    foreach ($dept['people'] as $id => $person)
                    {
                        if (!($user = $users->findOne($users->sfStateId->equals($id))))
                        {
                            $user = $users->createInstance();
                            $user->firstName = $person['firstName'];
                            $user->lastName = $person['lastName'];
                            $user->position = $person['role'];
                            $user->sfStateId = $id;
                            $user->createdDate = new DateTime;
                        }
                        $user->modifiedDate = $user->deleted && $user->modifiedDate ? new DateTime : $user->modifiedDate;
                        $user->deleted = false;
                        $user->save();

                        if (!$department->users->has($user))
                        {
                            $department->users->add($user);
                            $department->users->save();
                            $personnelChanges[$department->name]['add'][] = $user->sfStateId;
                            $user->addNote($user->sfStateId . ' added to ' . $department->name, $viewer);
                        }

                        $account = $accountManager->createDepartmentUserAccount(null, $user);
                    }
                }

                // remove as necessary
                foreach ($department->users as $user)
                {   
                    if (!in_array($user->sfStateId, array_keys($dept['people'])))
                    {   
                        $department->users->remove($user);
                        $department->users->save();
                        $user->deleted = true;
                        $user->save();

                        $personnelChanges[$department->name]['remove'][] = $user->sfStateId;
                    }
                }
            }
        }

        $added = 0;
        $removed = 0;
        foreach ($personnelChanges as $departmentName => $changes)
        {
            $department = $schema->findOne($schema->name->equals($departmentName));
            if (!empty($changes['add']))
            {
                $department->addNote(count($changes['add']) . ' personnel added', $viewer, ['new' => $changes['add']]);
                $added += count($changes['add']);
            }
            if (!empty($changes['remove']))
            {
                $department->addNote(count($changes['remove']) . ' personnel removed', $viewer, ['new' => $changes['remove']]);
                $removed += count($changes['remove']);
            }
        }

        return [$count, $added, $removed];
    }

    public function importSchedules ($semester, $facultyList)
    {
        $facultyList = is_array($facultyList) ? $facultyList : [$facultyList];
        $scheduleSchema = $this->schema('Classrooms_ClassData_CourseSchedule');
        $facultySchema = $this->schema('Classrooms_ClassData_User');
        $courseSchema = $this->schema('Classrooms_ClassData_CourseSection');
        $roomSchema = $this->schema('Classrooms_Room_Location');
        $buildingSchema = $this->schema('Classrooms_Room_Building');
        $accounts = $this->schema('Bss_AuthN_Account');

        $accountManager = new Classrooms_ClassData_AccountManager($this->getApplication());
        $service = new Classrooms_ClassData_Service($this->getApplication());

        foreach ($facultyList as $facultyId)
        {
            $service = new Classrooms_ClassData_Service($this->getApplication());
            $result = $service->getUserSchedules($semester, $facultyId);
            foreach ($result['courses'] as $cid => $data)
            {
                if (!empty($data['schedule']))
                {
                    foreach ($data['schedule'] as $sched)
                    {
                        $room = null;
                        if ($sched['facility']['building'] !== 'ON' && $sched['facility']['building'] !== 'OFF')
                        {
                            $building = $this->parseBuilding($sched['facility']);
                            $cond = $roomSchema->allTrue(
                                $roomSchema->number->equals($sched['facility']['room']),
                                $roomSchema->building_id->equals($building->id)
                            );
                            if (!($room = $roomSchema->findOne($cond)))
                            {
                                $room = $roomSchema->createInstance()->applyDefaults($sched['facility']['room'], $building);
                                $room->addNote('New room created automatically with default settings.', $accounts->get(1));
                            }
                        }
                    }

                    $course = $courseSchema->get($data['id']);
                    $cond = $scheduleSchema->allTrue(
                        $scheduleSchema->course_section_id->equals($course->id),
                        $scheduleSchema->faculty_id->equals($facultyId)
                    );
                    if (!($schedule = $scheduleSchema->findOne($cond)))
                    {
                        $schedule = $scheduleSchema->createInstance();
                        $schedule->createdDate = new DateTime;
                    }
                    $schedule->course_section_id = $course->id;
                    $schedule->faculty_id = $facultyId;
                    $schedule->termYear = $semester;
                    $schedule->schedules = serialize($data['schedule']);
                    $schedule->room_id = $room ? $room->id : null;
                    $schedule->save();
                }  
            }
        }
    }

    // constructs a building name from the description. this is not ideal
    protected function parseBuilding ($data)
    {
        $buildings = $this->schema('Classrooms_Room_Building');
        if ($building = $buildings->findOne($buildings->code->equals($data['building'])))
        {
            return $building;
        }
        $building = $buildings->createInstance();
        $building->code = $data['building'];
        $roomNumLen = strlen($data['room']) + 1;
        $start = strlen($data['description']) - $roomNumLen - 1;
        $end = strlen($data['description']) - $roomNumLen;
        if (substr($data['description'], $start, $end) === ' ')
        {
            $roomNumLen += 1;
        }
        $building->name = substr($data['description'], 0, strlen($data['description']) - $roomNumLen);
        $building->save();

        return $building;
    }

    // no longer being used
    public function importScheduledRooms ($termYear, $ignoreKeys = ['ON', 'OFF'])
    {
        $scheduleSchema = $this->schema('Classrooms_ClassData_CourseScheduledRoom');
        $facultySchema = $this->schema('Classrooms_ClassData_User');
        $courseSchema = $this->schema('Classrooms_ClassData_CourseSection');
        $roomSchema = $this->schema('Classrooms_Room_Location');
        $buildingSchema = $this->schema('Classrooms_Room_Building');
        $accounts = $this->schema('Bss_AuthN_Account');

        $accountManager = new Classrooms_ClassData_AccountManager($this->getApplication());
        $service = new Classrooms_ClassData_Service($this->getApplication());
        $schedules = [];
        $facilities = $service->getFacilities()['facilities'];

        foreach ($facilities as $fid => $facility)
        {
            if (isset($facility['building']) && isset($facility['room']) && !in_array($facility['building'], $ignoreKeys))
            {
                $roomNum = $facility['room'];
                $bldgCode = $facility['building'];
            }
            else
            {
                continue;
            }

            $result = $service->getSchedules($termYear, $fid)['courseSchedules'];
            
            if (!empty($result['courses']))
            {
                if (!($building = $buildingSchema->findOne($buildingSchema->code->equals($bldgCode))))
                {
                    $building = $this->parseBuilding($facility);
                }

                if (!($room = $roomSchema->findOne($roomSchema->number->equals($roomNum)->andIf($roomSchema->building_id->equals($building->id)))))
                {
                    $room = $roomSchema->createInstance()->applyDefaults($roomNum, $building);
                }

                foreach ($result['courses'] as $cid => $info)
                {                    
                    $course = $courseSchema->get($cid);
                    $course->classroom_id = (int)$room->id;
                    $course->save();

                    $condition = $scheduleSchema->allTrue(
                        $scheduleSchema->course_section_id->equals($cid),
                        $scheduleSchema->room_id->equals($room->id)
                    );
                    $previousFaculty = $scheduleSchema->find($condition, ['arrayKey' => 'faculty_id']) ?? [];
                    $currentFaculty = [];

                    foreach ($course->instructors as $instructor)
                    {
                        if (!($account = $accounts->findOne($accounts->username->equals($instructor->id))))
                        {
                            $account = $accountManager->createFacultyAccount($accounts->createInstance(), $instructor);
                        }

                        if (!isset($previousFaculty[$instructor->id]))
                        {
                            $schedule = $scheduleSchema->createInstance();
                            $schedule->termYear = $termYear;
                            $schedule->course_section_id = $cid;
                            $schedule->faculty_id = $instructor->id;
                            $schedule->account_id = $account->id;
                            $schedule->room_id = $room->id;
                            $schedule->room_external_id = $fid;
                            $schedule->createdDate = new DateTime;
                            $schedule->schedules = serialize($info['schedules']);
                            $schedule->save();
                        }
                        else
                        {
                            $schedule = $scheduleSchema->findOne($condition->andIf(
                                $scheduleSchema->faculty_id->equals($instructor->id))
                            );
                        }

                        $currentFaculty[$instructor->id] = $schedule;
                    }

                    foreach ($previousFaculty as $id => $schedule)
                    {
                        if (!isset($currentFaculty[$id]))
                        {
                            $currentFaculty[$id]->userDeleted = true;
                            $currentFaculty[$id]->modifiedDate = new DateTime;
                            $currentFaculty[$id]->save();
                        }
                    }
                }
            }
        }
    }
     
    public function createFacultyAccounts ($accountsToCreate)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');   

        $users = $this->schema('Classrooms_ClassData_User');
        $accounts = $this->schema('Bss_AuthN_Account');
        $accountManager = new Classrooms_ClassData_AccountManager($this->getApplication());

        foreach ($accountsToCreate as $userId)
        {
            $account = $accounts->findOne($accounts->username->equals($userId)) ?? $accounts->createInstance();
            $accountManager->createFacultyAccount($account, $users->get($userId));
        }
    }

    public function import ($semesterCode, $createFacultyAccount=false)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');   

        $courses = $this->schema('Classrooms_ClassData_CourseSection');
        $users = $this->schema('Classrooms_ClassData_User');
        $enrollments = $this->schema('Classrooms_ClassData_Enrollment');
        $this->allDepartments = $this->schema('Classrooms_Department_Department')->findValues(['name' => 'id']);
        $this->allCourses = $this->schema('Classrooms_ClassData_Course')->findValues(['id' => 'id']);
        $accountsToCreate = [];
        
        $dataSource = $courses->getDefaultDataSource();
        $tx = $dataSource->createTransaction();
        $now = new DateTime;
    
        $since = '1970-01-01';
        
        $service = new Classrooms_ClassData_Service($this->getApplication());
        list($status, $data) = $service->getChanges($semesterCode, $since);
        
        if ($status != 200)
        {
            if ($data && isset($data['error']))
            {
                $newLog->errorCode = $data['error'];
                $newLog->errorMessage = $data['message'];
            }
            else
            {
                $newLog->errorCode = 'NoErrorResource';
                $newLog->errorMessage = 'The response contained an error code, but the body was not a JSON-formatted error document.';
            }
        }
        else
        {

            // Keeps track of existing courses and users as we process the batches.
            $existingCourseSet = $courses->findValues(['id' => 'id']);
            $existingUserSet = $users->findValues(['id' => 'id']);
            
            // Process the courses in batches.
            
            foreach ($this->batches($data['courses'], 1000) as $batch)
            {
                foreach ($batch as $courseId => $actionList)
                {
                    foreach ($actionList as $action)
                    {
                        if (array_key_exists($courseId, $existingCourseSet))
                        {
                            if ($action['t'] == '+' && $existingCourseSet[$courseId])
                            {
                                // If we're trying to add a course that was 
                                // previously marked as deleted, remove all of its
                                // old enrollments. (We kept them before so that we
                                // had a record of the course's instructors. But
                                // now we're expecting new enrollments for the
                                // course -- which might replicate the info we
                                // saved.)
                                
                                // $this->deleteCourseEnrollments($tx, $enrollments, $courseId);
                            }
                            
                            if ($action['t'] == '+' || $action['t'] == '!')
                            {
                                $this->updateCourseSection($tx, $now, $courses, $courseId, $action['d']);
                            }
                            elseif ($action['t'] == '-')
                            {
                                $this->dropCourseSection($tx, $now, $courses, $courseId);
                                $existingCourseSet[$courseId] = true; // Mark as deleted.
                            }
                        }
                        elseif ($action['t'] == '+' || $action['t'] == '!')
                        {
                            $this->addCourseSection($tx, $now, $courses, $courseId, $action['d']);
                            $existingCourseSet[$courseId] = false;
                        }
                    }
                }
            }
            
            foreach ($this->batches($data['users'], 1000) as $idx => $batch)
            {
                foreach ($batch as $userId => $actionList)
                {
                    foreach ($actionList as $action)
                    {
                        if (array_key_exists($userId, $existingUserSet))
                        {
                            switch ($action['t'])
                            {
                                case '+':
                                case '!':
                                    $this->updateUser($tx, $now, $users, $userId, $action['d']);
                                    break;
                                case '-':
                                    $this->dropUser($tx, $now, $users, $userId);
                                    unset($existingUserSet[(string)$userId]);
                                    break;
                            }
                        }
                        elseif ($action['t'] == '+' || $action['t'] == '!')
                        {
                            $this->addUser($tx, $now, $users, $userId, $action['d']);
                            $existingUserSet[(string)$userId] = true;
                        }
                    }
                }
            }

            $data['enrollments'] = $data['enrollments'] ?? [];
            $existingEnrollmentSet = $this->loadExistingEnrollments($dataSource, $data['enrollments'], $existingCourseSet, $existingUserSet);
            
            // Enrollments.
            foreach ($data['enrollments'] as $courseId => $courseEnrollList)
            {
                if (array_key_exists($courseId, $existingCourseSet))
                {
                    foreach ($courseEnrollList as $action)
                    {
                        $role = ($action[1] == 's' ? 'student' : 'instructor');
                        $userId = substr($action, 2);
                        
                        if (isset($existingUserSet[(string)$userId]))
                        {
                            switch ($action[0])
                            {
                                case '+':
                                    if (!isset($existingEnrollmentSet[$courseId]) || !isset($existingEnrollmentSet[$courseId][$userId]))
                                    {
                                        $this->addEnrollment($tx, $now, $enrollments, $userId, $courseId, $role, $semesterCode);
                                    }

                                    // create faculty account
                                    if ($createFacultyAccount && $role === 'instructor')
                                    {
                                        $accountsToCreate[] = $userId;
                                    }
                                    break;
                                case '-':
                                    $this->dropEnrollment($tx, $now, $enrollments, $userId, $courseId, $role);
                                    break;
                            }
                        }
                        else
                        {
                            $this->application->log('debug', "Enrollment {$action} in {$courseId} for non-existent user: {$userId}");
                        }
                    }
                }
                else
                {
                    $this->application->log('debug', "Enrollment for non-existent course: {$courseId}");
                }
            }
        }
        
        $tx->commit();

        if ($createFacultyAccount && !empty($accountsToCreate))
        {
            $this->createFacultyAccounts($accountsToCreate);
        }

        return $now;
    }

    protected function batches ($data=[], $entries)
    {
        $count = isset($data) ? count($data) : 0;
        $batches = [];
        
        for ($i = 0; $i < $count; $i += $entries)
        {
            $batches[] = array_slice($data, $i, $entries, true);
        }
        
        return $batches;
    }

    /* Flags the user as being deleted but doesnt actually delete from the dB*/
    protected function dropUser ($tx, $now, $users, $userId)
    {
        $users->update(
            [
                'deleted' => true,
                'modifiedDate' => $now,
            ],
            $users->id->equals($userId)
        );
    }

    // add the create and modify fields
    public function addUser ($tx, $now, $users, $userId, $data)
    {
        $users->insert(
            [
                'id' => $userId,
                'firstName' => $data['first'] ?? '',
                'lastName' => $data['last'] ?? '',
                'emailAddress' => $data['mail'] ?? '',
                'createdDate' => $now,
                'modifiedDate' => $now,
                'deleted' => false,
            ],
            ['transaction' => $tx]
        );
    } 

    protected function updateUser ($tx, $now, $users, $userId, $data)
    {
        $users->update(
            [
                'firstName' => $data['first'],
                'lastName' => $data['last'],
                'emailAddress' => $data['mail'],
                'modifiedDate' => $now,
            ],
            $users->id->equals($userId),
            ['transaction' => $tx]
        );
    }

    public function addEnrollment ($tx, $now, $enrollments, $userId, $courseId, $role, $ysem)
    {
        $enrollments->insert(
            [
                'courseSectionId' => $courseId,
                'userId' => $userId,
                'role' => $role,
                'yearSemester' => $ysem,
                'createdDate' => $now,
                'modifiedDate' => $now,
                'deleted' => false,
            ],
            ['transaction' => $tx]
        );
    } 

    protected function dropEnrollment ($tx, $now, $courses, $userId, $courseId, $role)
    {
        $ref = $courses->enrollments;
        $dataSource = $courses->getDefaultDataSource();
        
        if ($ref)
        {
            $deleteQuery = $tx->createDeleteQuery($ref->getVia());
            $deleteQuery->setCondition($dataSource->andConditions([
                $dataSource->createCondition(
                    $dataSource->createSymbol('course_section_id'),
                    Bss_DataSource_Condition::OP_EQUALS,
                    $dataSource->createTypedValue($courseId, 'string')
                ),
                $dataSource->createCondition(
                    $dataSource->createSymbol('user_id'),
                    Bss_DataSource_Condition::OP_EQUALS,
                    $dataSource->createTypedValue($userId, 'string')
                ),
            ]));
            $deleteQuery->execute();            
        }

    }

    /**
     * Flags a course as having been dropped. We actually keep it around in the
     * cache because we might have resources associated with it in DIVA and we
     * want to keep that stuff.
     */
    protected function dropCourseSection ($tx, $now, $courses, $courseId)
    {
        $courses->update(
            [
                'deleted' => true,
                'modifiedDate' => $now,
            ],
            $courses->id->equals($courseId)
        );
    }

    /**
     * Add a course to the cache. add prereq
     */
    public function addCourseSection ($tx, $now, $courses, $courseId, $data)
    {
        $num='-1';
        $sem='-1';
        $year='-1';
        $sec='-1';
        if (empty($data['sn']) || empty($data['title']))
        {
            $missing = [];
            if (empty($data['sn'])) $missing[] = 'sn';
            if (empty($data['title'])) $missing[] = 'title';
            $this->application->log('warning', "Skipping add for course {$courseId}: Missing required field" . (count($missing) > 1 ? 's' : '') . ': ' . implode(', ', $missing));
            return;
        }
        //Parse the Course Section
        $sec = preg_split('[-]', $data['sn']);
        $sec = $sec[2];
        //Parse the semester from the short name
        if (strpos($data['sn'],'Fall') !== false) {
            $sem='7';
        }elseif (strpos($data['sn'],'Winter') !== false) {
            $sem='1';
        }elseif (strpos($data['sn'],'Spring') !== false) {
            $sem='3';
        }else{
            $sem='5';
        }
        $year = preg_split('[-]', $data['sn']);
        $year = $year[4];

        $num = preg_split('[-]', $data['sn']);
        $num = $num[0] . ' ' . $num[1];

        if (!isset($this->allCourses[$data['course']]))
        {
            $course = $this->schema('Classrooms_ClassData_Course')->createInstance();
            $course->id = $data['course'];
            $course->createdDate = new DateTime;
            $course->modifiedDate = new DateTime;
            $course->deleted = false;
            $course->department_id = @$this->allDepartments[$data['department']] ?? null;
            $course->save($tx);
            $this->allCourses[$data['course']] = $course->id;
        }

        $courses->insert(
            [
                'id' => $courseId,
                'title' => $data['title'],
                'classNumber'=> $num,
                'year' => $year,
                'semester' => $sem,
                'sectionNumber' => $sec,
                'description' => (isset($data['desc']) ? $data['desc'] : ''),
                'createdDate' => $now,
                'modifiedDate' => $now,
                'deleted' => false,
                'department_id' => @$this->allDepartments[$data['department']] ?? null,
                'course_id' => $this->allCourses[$data['course']],
            ],
            ['transaction' => $tx]
        );
    } 
    
    protected function updateCourseSection ($tx, $now, $courses, $courseId, $data)
    {
        if (empty($data['sn']) || empty($data['title']))
        {
            $missing = [];
            if (empty($data['sn'])) $missing[] = 'sn';
            if (empty($data['title'])) $missing[] = 'title';
            $this->application->log('warning', "Skipping update for course {$courseId}: Missing required field" . (count($missing) > 1 ? 's' : '') . ': ' . implode(', ', $missing));
            return;
        }

        //Parse the Course Section
        $sec = preg_split('[-]', $data['sn']);
        $sec = $sec[2];
        //Parse the semester from the short name
        if (strpos($data['sn'],'Fall') !== false) {
            $sem='7';
        }elseif (strpos($data['sn'],'Winter') !== false) {
            $sem='1';
        }elseif (strpos($data['sn'],'Spring') !== false) {
            $sem='3';
        }else{
            $sem='5';
        }
        $year = preg_split('[-]', $data['sn']);
        $year = $year[4];

        $num = preg_split('[-]', $data['sn']);
        $num = $num[0] . ' ' . $num[1];

        $courses->update(
            [
                'id' => $courseId,
                'title' => $data['title'],
                'classNumber'=> $num,
                'year' => $year,
                'semester' => $sem,
                'sectionNumber' => $sec,
                'description' => (isset($data['desc']) ? $data['desc'] : ''),
                'createdDate' => $now,
                'modifiedDate' => $now,
                'deleted' => false,
                'department_id' => @$this->allDepartments[$data['department']] ?? null,
            ],
            $courses->id->equals($courseId),
            ['transaction' => $tx]
        );
    } 

    protected function loadExistingEnrollments ($dataSource, $enrollments, $existingCourseSet, $existingUserSet)
    {
        $enrollCourseSet = [];
        $enrollUserSet = [];
        
        foreach ($enrollments as $courseId => $courseEnrollList)
        {
            if (array_key_exists($courseId, $existingCourseSet))
            {
                $enrollCourseSet[$courseId] = true;
                foreach ($courseEnrollList as $action)
                {
                    $userId = substr($action, (($action[0] === '+' || $action[0] === '-') ? 2 : 1));
                    $enrollUserSet[$userId] = true;
                }
            }
        }
        
        // There are no enrollments to check?
        if (empty($enrollCourseSet) || empty($enrollUserSet))
        {
            return [];
        }
        
        $existingEnrollmentSet = [];
        
        $query = $dataSource->createSelectQuery('classroom_classdata_enrollments');
        $query->project('course_section_id');
        $query->project('user_id');
        $query->setCondition($dataSource->andConditions([
            $dataSource->createCondition(
                $dataSource->createSymbol('course_section_id'),
                Bss_DataSource_Condition::OP_IN,
                $dataSource->createTypedValue(array_keys($enrollCourseSet), 'string')
            ),
            $dataSource->createCondition(
                $dataSource->createSymbol('user_id'),
                Bss_DataSource_Condition::OP_IN,
                $dataSource->createTypedValue(array_keys($enrollUserSet), 'string')
            ),
        ]));
        $query->orderBy('course_section_id', SORT_ASC);
        $query->orderBy('user_id', SORT_ASC);
        $rs = $query->execute();
        
        while ($rs->next())
        {
            $courseId = $rs->getValue('course_section_id', 'string');
            $userId = $rs->getValue('user_id', 'string');
            
            if (!isset($existingEnrollmentSet[$courseId]))
            {
                $existingEnrollmentSet[$courseId] = [];
            }
            
            $existingEnrollmentSet[$courseId][$userId] = true;
        } 
        return $existingEnrollmentSet;
    }

}
