<?php

/**
 */
class Classrooms_Software_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
            'list software' => 'list all software',
            'edit software' => 'edit a software',
        );
    }
}
