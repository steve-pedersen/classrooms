<?php

/**
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University.
 */
class Classrooms_Tutorial_Page extends Bss_ActiveRecord_Base
{
    use Notes_Provider;

    public static function SchemaInfo ()
    {
        return [
            '__type' => 'classroom_tutorial_pages',
            '__pk' => ['id'],
            
            'id' => 'int',
            'name' => 'string',
            'description' => 'string',
            'headerImageUrl' => ['string', 'nativeName' => 'header_image_url'],
            'youtubeEmbedCode' => ['string', 'nativeName' => 'youtube_embed_code'],
            'deleted' => 'bool',

            'rooms' => ['1:N', 
                'to' => 'Classrooms_Room_Location', 
                'reverseOf' => 'tutorial', 
                'orderBy' => [ '+modifiedDate', '+createdDate' ]
            ],
            'images' => ['1:N', 
                'to' => 'Classrooms_Files_File', 
                'reverseOf' => 'tutorial', 
                'orderBy' => [ '+uploadedDate', 'remoteName' ]
            ],

            'createdDate' => [ 'datetime', 'nativeName' => 'created_date' ],
            'modifiedDate' => [ 'datetime', 'nativeName' => 'modified_date' ],
        ];
    }

    public function getNotePath ()
    {
        return @$this->getNoteBase() . $this->id;
    }

    public function getNoteBase ()
    {
        return 'tutorials/' . $this->id;
    }

    public function getNoteUrl ()
    {
        return 'tutorial/' . $this->id;
    }

    public function hasDiff ($data)
    {
        $updated = false;
        foreach ($this->getData() as $key => $value)
        {
            if ($updated) break;
            if (isset($data[$key]) && !is_object($value))
            {
                if ($this->$key != $data[$key])
                {   
                    $updated = true;
                }
            }
        }

        return $updated;
    }

    public function getDiff ($data)
    {
        $updated = ['old' => [], 'new' => []];
        foreach ($this->getData() as $key => $value)
        {
            if (isset($data[$key]) && !is_object($value))
            {
                if ($this->$key != $data[$key])
                {   
                    $updated['old'][$key] = $this->$key;
                    $updated['new'][$key] = $data[$key];
                }
            }
        }

        return $updated;
    }
}

