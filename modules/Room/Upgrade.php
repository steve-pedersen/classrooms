<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Room_Upgrade extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_room_upgrades',
            '__pk' => ['id'],
            
            'id' => 'int',
            'semester' => 'int',
            'upgradeDate' => ['datetime', 'nativeName' => 'upgrade_date'],
            'isComplete' => ['bool', 'nativeName' => 'is_complete'],
            'notificationSent' => ['bool', 'nativeName' => 'notification_sent'],

            'room' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['room_id' => 'id']],
            'relocatedTo' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['relocated_to' => 'id']],

            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],
        ];
    }

    public function getTermYear ($display = false)
    {
        $year = $this->upgradeDate->format('Y');

        if (!$display)
        {
            return "" . $year[0] . $year[2] . $year[3] . $this->semester;
        }

        switch ($this->semester)
        {
            case 1:
                $semester = 'Winter'; break;
            case 3:
                $semester = 'Spring'; break;
            case 5:
                $semester = 'Summer'; break;
            case 7:
                $semester = 'Fall'; break;
            default:
                break;
        }

        return $semester . ' ' . $year;
    }


    public function hasDiff ($existing)
    {
        return (
            $existing->upgradeDate != $this->upgradeDate || 
            $existing->relocated_to != $this->relocated_to || 
            $existing->semester != $this->semester
        );
    }

    public function getDiff ($existing)
    {
        $relocatedTo = $this->getSchema('Classrooms_Room_Location')->get($this->relocated_to);
        return [
            'old' => [
                'upgradeDate' => $existing->upgradeDate->format('m/d/Y'),
                'relocatedTo' => $existing->relocated_to ? $existing->relocatedTo->getCodeNumber() : '',
                'semester' => $existing->semester
            ], 
            'new' => [
                'upgradeDate' => $this->upgradeDate->format('m/d/Y'),
                'relocatedTo' => $relocatedTo ? $relocatedTo->getCodeNumber() : '',
                'semester' => $this->semester
            ]
        ];
    }

    public function getSummary ()
    {
        $relocatedTo = $this->getSchema('Classrooms_Room_Location')->get($this->relocated_to);
        $summary = 'This room is scheduled to be upgraded on ' . $this->upgradeDate->format('m/d/Y');
        $summary .= $this->semester ? ', during the ' . $this->getTermYear(true) . ' semester.' : '.';
        if ($relocatedTo)
        {
            $link = '<a href="' . $relocatedTo->getRoomUrl() . '">' . $relocatedTo->getCodeNumber() . '</a>';
            $summary .= " Classes scheduled for that date are relocated to $link.";
        }

        return $summary;
    }

    public function getNotePath ()
    {
        return @$this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'room/rooms/' . $this->room->id . '/upgrades/';
    }

    public function getNoteUrl ()
    {
        return 'upgrades/' . $this->id;
    }
}
