<?php

/**
 */
class Classrooms_Department_TaskDefinitionProvider extends Bss_AuthZ_TaskDefinitionProvider
{
    public function getTaskDefinitions ()
    {
        return array(
        	'view department' => 'Can view their own department',
        	'view list' => 'Can view all departments',
        );
    }
}
