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
            'upgradeDate' => ['datetime', 'nativeName' => 'upgrade_date'],
            'isComplete' => ['bool', 'nativeName' => 'is_complete'],
            'notificationSent' => ['bool', 'nativeName' => 'notification_sent'],

            'room' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['room_id' => 'id']],
            'relocatedTo' => ['1:1', 'to' => 'Classrooms_Room_Location', 'keyMap' => ['relocated_to' => 'id']],

            'createdDate' => ['datetime', 'nativeName' => 'created_date'],
            'modifiedDate' => ['datetime', 'nativeName' => 'modified_date'],
        ];
    }

    public function hasDiff ($existing)
    {
        return $existing->upgradeDate != $this->upgradeDate || $existing->relocated_to != $this->relocated_to;
    }

    public function getDiff ($existing)
    {
        $relocatedTo = $this->getSchema('Classrooms_Room_Location')->get($this->relocated_to);
        return [
            'old' => [
                'upgradeDate' => $existing->upgradeDate->format('m/d/Y'),
                'relocatedTo' => $existing->relocated_to ? $existing->relocatedTo->getCodeNumber() : ''
            ], 
            'new' => [
                'upgradeDate' => $this->upgradeDate->format('m/d/Y'),
                'relocatedTo' => $relocatedTo ? $relocatedTo->getCodeNumber() : ''
            ]
        ];
    }

    public function getSummary ()
    {
        $relocatedTo = $this->getSchema('Classrooms_Room_Location')->get($this->relocated_to);
        $summary = 'This room is scheduled to be upgraded on ' . $this->upgradeDate->format('m/d/Y') . '.';
        if ($relocatedTo)
        {
            $link = '<a href="' . $relocatedTo->getRoomUrl() . '">' . $relocatedTo->getCodeNumber() . '</a>';
            $summary .= " Until then, classes are relocated to $link.";
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
