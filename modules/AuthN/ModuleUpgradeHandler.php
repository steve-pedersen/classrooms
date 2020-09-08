<?php

/**
 * Handle upgrading accounts.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_AuthN_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:
                $this->requireModule('bss:core:authZ', 1);
                
                $this->useDataSource('Bss_AuthN_Account');
                
                // Entity for roles.
                $def = $this->createEntityType('ws_authn_roles', 'Classrooms_AuthN_Role');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('is_system_role', 'bool');
                $def->save();
                
                // M:N mapping for accounts <=> roles
                $def = $this->createEntityType('ws_authn_account_roles', 'Classrooms_AuthN_Role');
                $def->addProperty('account_id', 'int', array('primaryKey' => true));
                $def->addProperty('role_id', 'int', array('primaryKey' => true));
                $def->addForeignKey('bss_authn_accounts', array('account_id' => 'id'));
                $def->addForeignKey('ws_authn_roles', array('role_id' => 'id'));
                $def->addIndex('account_id');
                $def->save();
                
                // Create access levels entity.
                $def = $this->createEntityType('ws_authn_access_levels', 'Classrooms_AuthN_AccessLevel');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->save();
                
                $this->useDataSource('Classrooms_AuthN_Role');

                $roleIdMap = $this->insertRecords('ws_authn_roles',
                    array(
                        array('name' => 'Administrator', 'description' => 'Has every possible permission.', 'is_system_role' => true),
                        array('name' => 'Normal User', 'description' => 'Can view most Templates. Miscellaneous Items exculded.', 'is_system_role' => true),
                        array('name' => 'Super User', 'description' => 'Can view all Templates.', 'is_system_role' => true),
                    ),
                    array(
                        'idList' => array('id')
                    )
                );
                
                $levelIdMap = $this->insertRecords('ws_authn_access_levels',
                    array(
                        array('name' => 'General', 'description' => 'Require a password. Only some items visible. <div class="detail">Meant for Normal Users.</div>'),
                        array('name' => 'Restricted', 'description' => 'Only visible to those who have explicitly been granted access. <div class="detail">Meant for Super Users.</div>'),
                    ),
                    array(
                        'idList' => array('id')
                    )
                );
                break;
        }
    }
}
