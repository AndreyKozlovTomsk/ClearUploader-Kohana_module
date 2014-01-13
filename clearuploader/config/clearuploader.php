<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Require Upload helper exists!
 *
 * Comes with ClearUploader (jQuery), sending AJAXom (asynchronously POST) files to the server
 */

return array(
    'modules' => array(

        // It does not recode, does not compress, does not change the extension
        'clearuploader' => array(

            // (string) Message langauge
            // Require file application/messages/clearuploader.php
            // Example ('ru_RU', 'en_US')
            'langauge' => 'ru_RU',
            
            // (object) Object from AJAX
            // Obtained from ClearUploader (jQuery)
            'from_ajax_file' => NULL,
            
            // (array) Processed files (listing extensions without keys)
            // Empty array - ANY extensions
            // Without '.' in lower case!
            'allowed_file_extensions' => array(),
            
            // (string) Root folder name
            // If folder name spaces exists, replace all spaces to '_'
            // Necessarily complete '/'
            'root_folder' => 'upload/',

            // (string) Current folder, addition to 'root_folder'
            // If folder name spaces exists, replace all spaces to '_'
            // Necessarily complete '/'
            'nested_folder' => NULL,

            // (int) id related table for DB
            'related_id' => NULL,
            
            // For remove
            // (bool) Delete all files in the folder, then select the folder by saving the database was used
            'remove_folder' => FALSE,
            
            // For DB
            // (bool) Whether to save the file path in a table
            // Required connection ORM module!
            'with_base' => TRUE,
            
            // (string) Model table
            // Struct table -
            // id(int pk auto_increment),
            // related_id(int unsigned index),
            // folder(string varchar(64) index),
            // file_name(string varchar(64) index),
            // uniqid(string varchar(15)),
            // size(int unsigned),
            // extension(string varchar(5))
            // Other fields in the table are counted 'add_fields'
            'model' => NULL,

            // (array) associative array, taking into account the custom field
            // $field_name => $value,
            'add_fields' => array(),
        )	
    )
);