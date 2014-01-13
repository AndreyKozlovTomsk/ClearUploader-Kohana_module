<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ClearUploader Library
 *
 * @todo        Simple save upload files to DB or not AND remove folders with ALL files in current folder (with folder) from DB
 * @package     ClearUploader
 * @category    Kohana-module
 * @author      Andrey Kozlov <KozlovAB@mail.ru>
 * @copyright   (c) 2014 Andrey Kozlov Tomsk
 * @license     MIT, GPLv3
 */
class Kohana_ClearUploader {

    /**
     * Default Configuration
     *
     * @access  protected
     * @var     array
     */
    protected $_config = array (
        'langauge'          => 'en_US',
        'from_ajax_file'    => NULL,
        'allowed_file_extensions' => array(),
        'root_folder'       => 'upload/',
        'nested_folder'     => NULL,
        'related_id'        => NULL,
        'remove_folder'     => FALSE,
        'with_base'         => TRUE,
        'model'             => NULL,
        'add_fields'        => array(),
    );
    
    /**
     * Information about response
     * 
     * @access  protected
     * @var     array
     */
    protected $_response = array(
        'success'   => TRUE,
        'message'   => NULL,
        'file'      => array(),
    );
    
    /**
     * Information about the saved file
     * 
     * @access  protected
     * @var     array
     */
    protected $_file = array(
        'id'        => NULL,
        'name'      => NULL,
        'location'  => NULL,
    );
    
    /**
     * Factory pattern
     *
     * @static
     * @access	public
     * @param array $config
     * @return ClearUploader
     */
    public static function factory(array $config = array()) {
        
        return new ClearUploader($config);
    }

    /**
     * Initialize ClearUploader
     *
     * @access	public
     * @param   array   $config
     */
    public function __construct(array $config = array()) {
        
        $this->_config = array_merge($this->_config, Kohana::$config->load('clearuploader')->modules['clearuploader'], $config);
    }
    
    /**
     * Saves the file to the hard disk and the database if 'with_base' === TRUE
     * 
     * @access  public
     * @return  array =
     *      (bool)      'success'   => TRUE || FALSE,
     *      (string)    'message'   => NULL || Something,
     *      (string)    'file'      => array =
     *              (int)       'id'        => NULL,
     *              (string)    'name'      => NULL,
     *              (string)    'location'  => NULL,
     */
    public function save() {
        
        // If file not uploaded
        if ($this->_config['from_ajax_file'] === NULL)
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge']. '.file_not_found');
            return $this->_response;
        }
        
        // If exist extension in array
        if (count($this->_config['allowed_file_extensions']))
        {
            if ( ! in_array(strtolower(pathinfo($this->_config['from_ajax_file']['name'], PATHINFO_EXTENSION)), $this->_config['allowed_file_extensions']))
            {
                $this->_response['success'] = FALSE;
                $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge']. '.invalid_extension');
                return $this->_response;
            }
        }
        
        // Build full path
        $path_name = $this->str2eng($this->_config['root_folder'] . $this->_config['nested_folder']);
        
        // Get extension of file
        $file_extension = strtolower(pathinfo($this->_config['from_ajax_file']['name'], PATHINFO_EXTENSION));
        
        // Get clear file name
        $file_name = $this->str2eng($this->cutExtension(($this->_config['from_ajax_file']['name']), $file_extension));
        
        // if folder not exist, create folder
        if ( ! file_exists(trim($path_name, '/')))
        {
            $umask = umask(0);
            mkdir(trim($path_name, '/'), 0777, TRUE);
            umask($umask);
        }
        
        // Get uniqID
        $uniqid_for_file = uniqid('_');
        
        // If file exist get new UniqID
        while (file_exists($path_name.$file_name.$uniqid_for_file. '.' .$file_extension))
        {
            $uniqid_for_file = uniqid('_');
        }
        
        // Save The file
        $file_save = Upload::save($this->_config['from_ajax_file'], $file_name.$uniqid_for_file. '.' .$file_extension, $path_name);

        // If file do NOT Saved
        if ($file_save === FALSE)
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge']. '.error_saving_file').$this->generationErrorString($this->_config['from_ajax_file']->errors('upload'));
            return $this->_response;
        }
        
        $this->_file['name'] = $file_name;
        $this->_file['location'] = $path_name;
        $this->_response['file'] = $this->_file;
        
        // If not require to DB save
        if ($this->_config['with_base'] !== TRUE OR $this->_config['model'] === NULL)
        {
            return $this->_response;
        }

        // Add custom fields
        $db_object = array_merge($this->_config['add_fields'], array(
            'related_id'    => $this->_config['related_id'],
            'folder'        => $path_name,
            'file_name'     => $file_name,
            'uniqid'        => $uniqid_for_file,
            'extension'     => $file_extension,
            'size'          => $this->_config['from_ajax_file']['size'],
        ));

        // Save file to DB
        try
        {
            $db_save_file = ORM::factory($this->_config['model'])
                    ->values($db_object)
                    ->save();
            $this->_file['id'] = $db_save_file->pk();
        }
        catch (ORM_Validation_Exception $e)
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge'] .'.error_saving_to_database').$this->generationErrorString($e->errors('validation'));
            return $this->_response;
        }
        
        // Update the file information
        $this->_response['file'] = $this->_file;
        return $this->_response;
    }
    
    /**
     * Removes the file from the hard disk and from the database if 'with_base' === TRUE
     * Requires id from the current model parameter and the model name in the configuration file
     *
     * @access  public
     * @param   string  $field  Name field from the database
     * @param   string  $value  id from current DB/Model
     * @return  array =
     *      (bool)      'success'   => TRUE || FALSE,
     *      (string)    'message'   => NULL || Something,
     */
    public function delete($field, $value) {

        // When attempting to delete a database without
        if ($this->_config['with_base'] !== TRUE OR $this->_config['model'] === NULL OR  $this->_config['model'] === '')
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge'] .'.db_not_defined');
            return $this->_response;
        }

        // If $field OR $value is empty
        if ($field === NULL OR $value === NULL)
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge'] .'.id_not_determined');
            return $this->_response;
        }
        
        // Find row in the DB
        $db_row = ORM::factory($this->_config['model'])
                ->where($field, '=', $value)
                ->find();
        $file_folder = $db_row->folder;

        // If row not found
        if ( ! $db_row->loaded() OR $db_row === NULL)
        {
            $this->_response['success'] = FALSE;
            $this->_response['message'] = Kohana::message('clearuploader', $this->_config['langauge'] .'.row_not_found_in_the_database');
            return $this->_response;
        }
        
        // If you want to delete a file folder with all files
        if ($this->_config['remove_folder'])
        {
            $db_one_folder = ORM::factory($this->_config['model'])
                    ->where('folder', '=', $db_row->folder)
                    ->find_all();
            
            foreach ($db_one_folder as $db_one_file)
            {
                unlink($db_one_file->folder.$db_one_file->file_name.$db_one_file->uniqid. '.' .$db_one_file->extension);
                
                $db_one_file->delete();
            }
        }
        else
        {
            unlink($db_row->folder.$db_row->file_name.$db_row->uniqid. '.' .$db_row->extension);

            $db_row->delete();
        }
        
        // Delete the folder if it is empty
        if (count(glob($file_folder. "*")) == 0)
        {
            rmdir($file_folder);
        }

        return $this->_response;
    }
    
    /**
     * Cut file extension
     * 
     * @param   string  $file_name      File name with extension
     * @param   string  $file_extension Extension file
     * @return  string
     */
    private function cutExtension($file_name, $file_extension) {
        
        return str_replace('.' .$file_extension, NULL, $file_name);
    }
    
    /**
     * Convert ALL symbols to ENG
     * 
     * @param   string  $str
     * @return  string
     */
    private function str2eng($str) {

        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );

        $str = strtr($str, $converter);

        // to lower case
        $str = strtolower($str);

        // replace all bug symbols to '_'
        $str = preg_replace('~[^-a-z0-9_/]+~u', '_', $str);

        // remove first and last '-'
        $str = trim($str, "-");

        return $str;
    }
    
    /**
     * Generating a string of objects ORM_Validation_Exception and others
     * 
     * @param   object  $errors From Exception
     * @return  string
     */
    private function generationErrorString ($errors) {
    
        $result_errors = NULL;

        // For each Exception
        foreach ($errors as $error)
        {
            if(is_array($error))
            {
                $result_errors = $this->generationErrorString($error);
            }
            else
            {
                // Concate string with errors
                $result_errors .= ' ' .$error;
            }
        }
        
        return $result_errors;
    }
}
