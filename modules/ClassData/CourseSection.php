<?php

/**
 * CourseSection ActiveRecord schema of ClassData/SIS course section data.
 *
 * @author Steve Pedersen (pedersen@sfsu.edu)
 */
class Classrooms_ClassData_CourseSection extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_classdata_course_sections',
            '__pk' => ['id'],
            
            'id' => 'string',
            'title' => 'string',           
            'sectionNumber' => ['string', 'nativeName' => 'section_number'],
            'classNumber' => ['string', 'nativeName' => 'class_number'],
            'semester' => 'string',
            'year' => 'string',
            'description' => 'string',  
            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],
            // 'classroomId' => ['string', 'nativeName' => 'classroom_id'],
            'deleted' => 'bool',

            'enrollments' => ['N:M',
                'to' => 'Classrooms_ClassData_User',
                'via' => 'classroom_classdata_enrollments',
                'fromPrefix' => 'course_section',
                'toPrefix' => 'user',
                'properties' => [
                    'year_semester' => 'string', 
                    'role' => 'string', 
                    'deleted' => 'bool',
                    'created_date' => 'datetime',
                    'modified_date' => 'datetime',
                ],
                'orderBy' => ['-_map.role', 'lastName', 'firstName'],
            ],

            'course'        => ['1:1', 'to' => 'Classrooms_ClassData_Course', 'keyMap' => ['course_id' => 'id']],
        ];
    }

    public function getInstructors ()
    {
        $instructors = [];
        foreach ($this->enrollments as $enrollment)
        {
            if ($this->enrollments->getProperty($enrollment, 'role') === 'instructor')
            {
                $instructors[] = $enrollment;
            }
        }

        return $instructors;
    }

    public function getTerm ($internal=false)
    {
        $term = $this->getSemester(true) . ' ' . $this->_fetch('year');
        if ($internal)
        {
            $term = self::ConvertToCode($term);
        }

        return $term;
    }

    public function getSemester ($display=false)
    {
        $sem = $this->_fetch('semester');
        if ($display && (strlen($sem) === 1))
        {
            switch ($sem) {
                case '1':
                    $sem = 'Winter'; break;
                case '3':
                    $sem = 'Spring'; break;
                case '5':
                    $sem = 'Summer'; break;
                case '7':
                    $sem = 'Fall'; break;
                default:
            }
        }
        return $sem;
    }

    public function getShortName ($full=false)
    {
        $cn = $this->_fetch('classNumber');
        $section = $this->_fetch('sectionNumber');
        if ($full)
        {
            return $cn . "-$section " . $this->getTerm();
        }

        return $cn . "-$section";
    }

    public function getFullDisplayName ()
    {
        return $this->shortName.' - '.$this->_fetch('title');
    }

    public function getFullSummary ()
    {
        return $this->getFullDisplayName() .' ['.$this->getTerm().']';
    }

    public function getMediasiteName ()
    {
        return $this->shortName;
    }

    public function isTaughtByUser ($user)
    {
        $isTaughtByUser = false;
        foreach ($this->enrollments as $enrollment)
        {
            if ($enrollment->id === $user->username &&
                $this->enrollments->getProperty($enrollment, 'role') === 'instructor')
            {
                $isTaughtByUser = true;
                break;
            }
        }

        return $isTaughtByUser;
    }

    public static function ConvertToCode ($display)
    {
        $space = strpos($display, ' ');
        $term = substr($display, 0, $space);
        $year = substr($display, $space + 1);

        switch ($term) {
            case 'Winter':
                $term = 1;
                break;
            
            case 'Spring':
                $term = 3;
                break;

            case 'Summer':
                $term = 5;
                break;

            case 'Fall':
                $term = 7;
                break;
        }

        return $year[0] . $year[2] . $year[3] . $term;
    }

    public static function ConvertToYearSemester ($display)
    {
        $space = strpos($display, ' ');
        $term = substr($display, 0, $space);
        $year = substr($display, $space + 1);

        switch ($term) {
            case 'Winter':
                $term = 1;
                break;
            
            case 'Spring':
                $term = 3;
                break;

            case 'Summer':
                $term = 5;
                break;

            case 'Fall':
                $term = 7;
                break;
        }

        return [$year, $term];
    }
}
