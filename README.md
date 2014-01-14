ClearUploader-Kohana_module
===========================

**Simple save upload files to DB or not AND remove folders with ALL files in current folder (with folder) from DB**

Saving/Deleting files made in DB and to the hard disk

To work with the DB connection required module ORM

Tested on **Kohana v.3.3.1**

==========================================================
For Use:

1. Connection in the file **bootstrap.php**:

```php
'clearuploader' => MODPATH.'clearuploader', // ClearUploader
```
2. Copy **config** file to 'application/config/'

3. Copy **message** file to 'application/messages/'

==========================================================
**SQL Model template** ( **+ ANY** other fields):

    id(int pk auto_increment),
    related_id(int unsigned index),
    folder(string varchar(64) index),
    file_name(string varchar(64) index),
    uniqid(string varchar(15)),
    size(int unsigned),
    extension(string varchar(5))
    
**Example** of creating a model and a model for Kohana:

    modules/clearuploader/model/

==========================================================

For convenience of the user data to a file, use ClearUploader-jQuery.

==========================================================

**Example save the file** (more detail, refer to config):
```php
$db_save = Clearuploader::factory(array(
        'from_ajax_file'    => $_FILES['file'],
        'nested_folder'     => 'staffs/',
        'related_id'        => 125,             // value - for example
        'model'             => 'uploadfiles',
        'add_fields'        => array(           // taking into account the custom field
            'custom_field_1'    => 'Some text', // field and value - for example
            'custom_field_2'    => 648,         // field and value - for example
        ),
    ))
    ->save();

if ( ! $db_save['success'])
{
    $this->_response['error'] = TRUE;
    $this->_response['response'] = $db_save['message'];
}
```
**Object returned** class ClearUploader after saving:
```php
@return  array =
    (bool)      'success'   => TRUE || FALSE,
    (string)    'message'   => NULL || 'Something',
    (string)    'file'      => array =  // if 'success' == TRUE
            (int)       'id'        => NULL,
            (string)    'name'      => NULL,
            (string)    'location'  => NULL,
```
==========================================================

**Example remove the file** (more detail, refer to config):
```php
$db_remove = Clearuploader::factory(array(
        'model' => 'files' .$post_who,
    ))
    ->delete('id', 125);    // value - for example


if ( ! $db_remove['success'])
{
    $this->_response['error'] = TRUE;
    $this->_response['response'] = $db_remove['message'];
}
```
**Object returned** class ClearUploader after removing:
```php
@return  array =
    (bool)      'success'   => TRUE || FALSE,
    (string)    'message'   => NULL || 'Something',
```
==========================================================

**More detailing settings**, refer to config:

    modules/clearuploader/config/clearuploader.php
