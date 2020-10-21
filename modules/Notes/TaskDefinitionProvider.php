<?php

/**
 */
class Classrooms_Notes_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'view notes' => 'view notes',
        );
    }
}
