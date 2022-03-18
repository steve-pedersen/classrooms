<?php

/**
 *  Communication is the email template.
 */
class Classrooms_Communication_Communication extends Bss_ActiveRecord_Base
{
    private static $AccessType = array(
        'open' => 'Open',
        'closed' => 'Closed',
        'limited' => 'Limited',
    );

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_communication_communications',
            '__pk' => ['id'],
            
            'id' => 'int',
            'access' => 'string',
            'roomMasterTemplate' => ['string', 'nativeName' => 'room_master_template'],
            'labRoom' => ['string', 'nativeName' => 'lab_room'],
            'nonlabRoom' => ['string', 'nativeName' => 'nonlab_room'],
            'unconfiguredRoom' => ['string', 'nativeName' => 'unconfigured_room'],
            'noRoom' => ['string', 'nativeName' => 'no_room'],
            'upgradeRoom' => ['string', 'nativeName' => 'upgrade_room'],
            'creationDate' => ['datetime', 'nativeName' => 'creation_date'],

            'type' => [ '1:1', 'to' => 'Classrooms_Communication_Type', 'keyMap' => [ 'type_id' => 'id' ] ],
            'events' => ['1:N', 'to' => 'Classrooms_Communication_Event', 'reverseOf' => 'communication', 'orderBy' => ['-creationDate']],
        ];
    }
}