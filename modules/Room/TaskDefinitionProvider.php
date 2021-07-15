<?php

/**
 */
class Classrooms_Room_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
        	'edit' => 'edit all the things',
            'view schedules' => 'view schedules in a room',
        );
    }
}
