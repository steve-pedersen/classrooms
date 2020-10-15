<?php

/**
 * 
 * @author      Charles O'Sullivan (chsoney@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Software_Category extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_software_categories',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'deleted' => 'bool',

            'parentCategory' => [ '1:1', 'to' => 'Classrooms_Software_Category', 'keyMap' => [ 'parent_category_id' => 'id' ] ],
            'titles' => ['1:N', 
                'to' => 'Classrooms_Software_Title', 
                'reverseOf' => 'category', 
                'orderBy' => [ '+modifiedDate', '+createdDate' ]
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return $this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return '/software/category/';
    }

    public function getNoteUrl ()
    {
        return '/software/category/' . $this->id;
    }
}
