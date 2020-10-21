<?php

/**
 */
class Classrooms_Room_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'list rooms' => 'list all rooms',
            'edit room' => 'edit a room',
        );
    }
}
