<?php

/**
 */
class Classrooms_AuthN_AccessLevel extends Bss_ActiveRecord_BaseWithAuthorization
{
    public static function SchemaInfo ()
    {
        return array(
            '__class' => 'Classrooms_AuthN_AccessLevelSchema',
            '__type' => 'ws_authn_access_levels',
            '__pk' => array('id'),
            '__azidPrefix' => 'at:classrooms:authN/AccessLevel/',
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
        );
    }
}
