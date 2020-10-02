<?php

/**
 * Create the configuration options.
 * 
 * @author      Daniel A. Koepke (dkoepke@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Software_ModuleUpgradeHandler extends Bss_ActiveRecord_BaseModuleUpgradeHandler
{
    public function onModuleUpgrade ($fromVersion)
    {
        switch ($fromVersion)
        {
            case 0:

                $this->useDataSource($this->getApplication()->dataSourceManager->getDataSource('default'));

                $def = $this->createEntityType('classroom_software_categories');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('parent_category_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('parent_category_id', 'deleted');
                $def->save();

                $def = $this->createEntityType('classroom_software_developers');
                $def->addProperty('id', 'int', array('sequence' => true, 'primaryKey' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_software_titles');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('name', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('developer_id', 'int');
                $def->addProperty('category_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('deleted');
                $def->save();

                $def = $this->createEntityType('classroom_software_versions');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('number', 'string');
                $def->addProperty('title_id', 'int');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('title_id', 'deleted');
                $def->save();

                $def = $this->createEntityType('classroom_software_licenses');
                $def->addProperty('id', 'int', array('primaryKey' => true, 'sequence' => true));
                $def->addProperty('number', 'string');
                $def->addProperty('description', 'string');
                $def->addProperty('seats', 'string');
                $def->addProperty('version_id', 'int');
                $def->addProperty('expiration_date', 'datetime');
                $def->addProperty('created_date', 'datetime');
                $def->addProperty('modified_date', 'datetime');
                $def->addProperty('deleted', 'bool');
                $def->addIndex('version_id', 'deleted');
                $def->save();

                $now = new DateTime;
                $this->useDataSource('Classrooms_Software_Category');
                $groupIdMap = $this->insertRecords('classroom_software_categories',
                    [
                        ['name' => 'Adobe Creative Cloud', 'created_date' => $now],
                        ['name' => 'Anti-Virus/Anti-Malware', 'created_date' => $now],
                        ['name' => 'Microsoft Office', 'created_date' => $now],
                        ['name' => 'Statistical Software', 'created_date' => $now],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                $this->useDataSource('Classrooms_Software_Developer');
                $groupIdMap = $this->insertRecords('classroom_software_developers',
                    [
                        ['name' => 'Adobe', 'created_date' => $now],
                        ['name' => 'Microsoft', 'created_date' => $now],
                        ['name' => 'McAfee', 'created_date' => $now],
                    ],
                    [
                        'idList' => ['id']
                    ]
                );

                break;
        }
    }
}