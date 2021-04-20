<?php

/**
 *  An Event is a scheduled date for which to send out a communication's emails
 */
class Classrooms_Communication_Event extends Bss_ActiveRecord_Base
{
    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_communication_events',
            '__pk' => ['id'],
            
            'id' => 'int',
            'communication' => ['1:1', 'to' => 'Classrooms_Communication_Communication', 'keyMap' => ['communication_id' => 'id']],
            
            'creationDate' => ['datetime', 'nativeName' => 'creation_date'],
            'termYear' => ['string', 'nativeName' => 'term_year'],
            'sendDate' => ['datetime', 'nativeName' => 'send_date'],
            'sent' => 'bool',

            'logs' => ['1:N', 'to' => 'Classrooms_Communication_Log', 'reverseOf' => 'event', 'orderBy' => ['+creation_date']],
        ];
    }

    public function formatTermYear ()
    {
        $year = $this->termYear[0] . '0' . $this->termYear[1] . $this->termYear[2];
        $semester = '';
        switch ($this->termYear[3])
        {
          case 1:
            $semester = 'Winter'; break;
          case 3:
            $semester = 'Spring'; break;
          case 5:
            $semester = 'Summer'; break;
          case 7:
            $semester = 'Fall'; break;
        }

        return $semester . ' ' . $year;
    }
}