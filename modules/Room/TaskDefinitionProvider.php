<?php

/**
 */
class Classrooms_Room_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
        	'edit' => 'edit all the things',
            'list rooms' => 'list all rooms',
            'edit room' => 'edit a room',
            'view schedules' => 'view schedules in a room',
        );
    }
}
