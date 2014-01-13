<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Upload files
 */
class Model_Uploadfiles extends ORM {

    protected $_table_name = 'tablename';

    public function rules()
    {
        return array(
            'related_id' => array(
                array('not_empty'),
                array('digit'),
            ),
            'folder' => array(
                array('not_empty'),
            ),
            'file_name' => array(
                array('not_empty'),
            ),
            'uniqid' => array(
                array('not_empty'),
            ),
            'size' => array(
                array('not_empty'),
                array('digit'),
            ),
            'extension' => array(
                array('not_empty'),
            ),
        );
    }

    public function labels()
    {
        return array(
            'related_id'    => 'Related table key',
            'folder'        => 'Filefolder',
            'file_name'     => 'Filename',
            'uniqid'        => 'Unique ID',
            'size'          => 'Filesize',
            'extension'     => 'File extension',
        );
    }

    public function filters()
    {
        return array(
            TRUE => array(
                array('trim'),
                array('strip_tags'),
            ),
        );
    }
}