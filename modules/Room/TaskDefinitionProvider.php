<?php

/**
 */
class Classrooms_Room_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'location view' => 'view a room location',
        );
    }
}
