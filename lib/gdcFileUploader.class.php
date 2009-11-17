<?php
  /**
   * This file contains the gdcFileUploader, gdcFileUploaderFile, gdcFileUploaderCriteria and gdcFileUploaderException classes
   *
   * @author       Georg Dresler <georg@g-dresler.de>
   * @package      de.g_dresler.code
   * @version      1.5
   * @copyright    Georg Dresler 2009
   * @license      GPL 3.0 http://www.gnu.org/licenses/
   */

  /**
   * gdcFileUploader handles uploading of files submitted via a HTML form
   *
   * @author       Georg Dresler <georg@g-dresler.de>
   * @package      de.g_dresler.code
   * @version      1.5
   * @copyright    Georg Dresler 2009
   * @license      GPL 3.0 http://www.gnu.org/licenses/
   */
class gdcFileUploader {
        
    protected
        $uploadDir    = null,
        $files        = array(),
        $goodFiles    = array(),
        $badFiles     = array();
        
    /**
     * Constructor
     *
     * @param string $uploadDir    The upload directory
     * @param string    $criteria        A string with criteria settings
     */
    public function __construct($uploadDir = null, $criteria = null) {
        $this->initialize($uploadDir, $criteria);
    }
        
    /**
     * Initializes the file arrays and creates new gdcFileUplaoderFile instances
     *
     * @param string $uploadDir    The upload directory
     * @param string    $criteria        A string with criteria settings
     */
    protected function initialize($uploadDir, $criteria) {

        $this->setUploadDir($uploadDir);

        foreach ($_FILES as $field => $file) {
                
            $gdcFile = new gdcFileUploaderFile($file, $field);
                
            // push file on appropriate arrays
            array_push($this->files, $gdcFile);
            if ($gdcFile->isGood()) {
                array_push($this->goodFiles, $gdcFile);
            } else {
                array_push($this->badFiles, $gdcFile);
            }
        }
            
        $this->addCriteria($criteria);
    }
        
    /**
     * Moves an uploaded file to a new location and renames it
     *
     * @param gdcFileUploaderFile    $file                A gdcFileUploaderFile instance; leave empty if only one file was uploaded
     * @param string                                $fileName     The new file name; May lead to data loss!
     * @param string                                $uploadDir    The upload directory; is set as new global uploadDir
     *
     * @return mixed (string) the file name, if success, otherwise (bool) false
     *
     * @throws gdcFileUploaderException
     */
    public function saveFile(gdcFileUploaderFile $file = null, $fileName = null, $uploadDir = null) {
            
        // check if $file isset
        if (is_null($file) && $this->hasFiles() && $this->countFiles() == 1) {
            $file = $this->files[0];
        } else if (is_null($file)) {
            throw new gdcFileUploaderException('More than one file has been uploaded. Therefore "$file" must not be null');
        }
            
        // check if file is faulty
        if (!$file->isGood()) {
            throw new gdcFileUploaderException(sprintf('The file "%s" has not been uploaded correctly (Error %s) and thus cannot be processed', $file->getName(), $file->getError()));    
        }
            
        // set uploadDir
        $uploadDir = is_null($uploadDir) ? $this->getUploadDir() : $uploadDir;
        if (is_writable($uploadDir)) {
            $this->setUploadDir($uploadDir);
        } else {
            throw new gdcFileUploaderException(sprintf('The upload directory "%s" is not writable', $uploadDir));
        }
            
        // set file name
        if (is_null($fileName) || $fileName == "") {
            $fileName = $file->getName();
        }
            
        // check criteria
        if ($this->hasCriteria()) {
                
            $crit = $this->getCriteria();
                
            // check type
            if (!is_null($type = $crit->getType())) {
                    
                switch($crit->getTypeMode()) {
                        
                case 'EQUAL':
                    if (!preg_match('%'.$type.'%', $file->getType())) return false;
                    break;
                case 'NOT_EQUAL':
                    if (preg_match('%'.$type.'%', $file->getType())) return false;
                    break;
                }
            }
                
            // check size
            if (!is_null($size = $crit->getSize())) {
                    
                $fsize = $file->getSize();
                switch ($crit->getSizeMode()) {
                case 'EQUAL':
                    if ($fsize != $size) return false;
                    break;
                case 'NOT_EQUAL':
                    if ($fsize == $size) return false;
                    break;
                case 'GREATER':
                    if ($fsize <= $size) return false;
                    break;
                case 'GREATER_EQUAL':
                    if ($fsize < $size) return false;
                    break;
                case 'LESS':
                    if ($fsize >= $size) return false;
                    break;
                case 'LESS_EQUAL':
                    if ($fsize > $size) return false;
                    break;
                }
            }
        }
            
        // now move that file
        if (@move_uploaded_file($file->getTmp(), $this->getUploadDir().'/'.$fileName)) {
                
            $file->setNewName($fileName);
            $file->setPath($uploadDir);
            $file->setUploaded(true);
                
            return true;
        } else
                
            return false;
    }
        
    /**
     * Moves all uploaded files to a new location and renames them
     *
     * The $callable must be a valid PHP callable and must return the new filename as a string.
     * The gdcFileUploaderFile instance will be passed as argument.
     * If null the original file name will be used. May lead to data loss!!!
     *
     * @param string    $uploadDir    The upload directory; is set as new global uploadDir
     * @param mixed    $callable        A PHP callable to determine the file name
     *
     * @return bool true, if there were no errors, otherwise false
     *
     * @throws gdcFileUploaderException
     */
    public function saveAllFiles($uploadDir = null, $callable = null) {
            
        $ret = true;
            
        // iterate over all files
        foreach ($this->getFiles() as $file) {
                
            if (!is_null($callable) && !is_callable($callable)) {
                throw new gdcFileUploaderException('The "$callable" argument is not a valid PHP callable');
            } else if (!is_null($callable)) {
                $fileName = call_user_func($callable, $file);
            } else {
                $fileName = $file->getName();
            }
                
            $ret = $this->saveFile($file, $fileName, $uploadDir);
        }
            
        return $ret;
    }
        
    /**
     * Adds criteria to limit uploading
     *
     * @param string $settings    A string containing criteria
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     *
     * @see gdcFileUploaderCriteria::addByString()
     */
    public function addCriteria($settings = null) {
            
        gdcFileUploaderCriteria::getInstance()->setByString($settings);
            
        return gdcFileUploaderCriteria::getInstance();
    }
        
    /**
     * Clears all criteria settings
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function removeCriteria() {
            
        gdcFileUploaderCriteria::getInstance()->removeAll();
            
        return gdcFileUploaderCriteria::getInstance();
    }
        
    /**
     * Returns the gdcFileUploaderCriteria instance
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function getCriteria() {
            
        return gdcFileUploaderCriteria::getInstance();
    }
        
    /**
     * Indicates wether any criteria is set
     *
     * @return bool true, if criteria is set, otherwise false
     */
    public function hasCriteria() {
            
        return $this->getCriteria()->hasCriteria();
    }
        
    /**
     * Sets the upload directory
     *
     * @param string $uploadDir The upload directory
     */
    public function setUploadDir($uploadDir) {
            
        $this->uploadDir = $uploadDir;
    }
        
    /**
     * Returns the upload directory
     *
     * @return string The upload directory
     */
    public function getUploadDir() {
            
        return $this->uploadDir;
    }
        
    /**
     * Returns the total size of all uploaded files
     *
     * @param bool $omitBad true, to omit faulty files, otherwise true (default)
     *
     * @return int The total size
     */
    public function getTotalSize($omitBad = true) {
            
        $size = 0;
        foreach ($this->getFiles($omitBad) as $file) {
                
            $size += $file->getSize();
        }
            
        return $size;
    }
        
    /**
     * Returns a readable representation of the total size
     *
     * @see gdcFileUploader::makeBytesReadable()
     *
     * @param bool $omitBad true (default), to omit faulty files, otherwise false
     *
     * @return string A readable size
     */
    public function getTotalSizeReadable($omitBad = true) {
            
        return gdcFileUploader::makeBytesReadable($this->getTotalSize($omitBad));
    }
        
    /**
     * Returns all files
     *
     * @param bool $omitBad true (default) to omit faulty files, otherwise false
     *
     * @return array An array containing gdcFileUploaderFile instances
     */
    public function getFiles($omitBad = true) {
            
        return $omitBad ? $this->goodFiles : $this->files;
    }
    
    /**
     * Returns a gdcFileUploaderFile instance or false
     *
     * @param mixed $file (int) an array index, (string) the name of an input field
     *
     * @return mixed gdcFileUploaderFile instance, otherwise (bool) false
     */
    public function getFile($fileid) {
            
        if (is_numeric($fileid) && $fileid <= $this->countFiles(false)) {
                
            return $this->files[$fileid];
        } else if (is_string($fileid)) {
                
            foreach ($this->files as $file) {
                    
                if ($file->getField() == $fileid) {
                    return $file;
                } else {
                    continue;
                }
            }
        } else {
                
            return false;
        }
    }
        
    /**
     * Returns an array containing all files that uplaoded without an error
     *
     * @return array Array containing gdcFileUploaderFile instances
     */
    public function getGoodFiles() {
            
        return $this->goodFiles;
    }
        
    /**
     * Returns ana array containing all files that uploaded with an error
     *
     * @return array Array containing gdcFleUploaderFile instances
     */
    public function getBadFiles() {
            
        return $this->badFiles;
    }
        
    /**
     * Indicates wether any files were uploaded
     *
     * @param bool $omitBad true (default), to omit faulty files, otherwise false
     *
     * @return bool true, files were uploaded, otherwise false
     */
    public function hasFiles($omitBad = true) {
            
        return $this->countFiles($omitBad);
    }
    
    /**
     * Indicates wether a certain file has been uploaded
     *
     * @param mixed $file (int) an array index, (string) an input field's name
     *
     * @return bool     true, file has been uploaded, otherwise false
     */
    public function hasFile($file) {
            
        return (boolean) $this->getFile($file);
    }
        
    /**
     * Returns amount of files uploaded
     *
     * @param bool $omitBad true (default), to omit faulty files, otherwise true
     *
     * @return int The amount of files
     */
    public function countFiles($omitBad = true) {
            
        if ($omitBad) {
            return count($this->goodFiles);
        } else {
            return count($this->files);
        }
    }
        
    /**
     * toString
     *
     * @param bool $omitBad true (default), to omit faulty files, otherwise false
     *
     * @return string A textual representation of all files
     */
    public function __toString($omitBad = true) {
            
        $ret = '<ul>';
        foreach ($this->getFiles($omitBad) as $file) {
                
            $ret .= '<li>'.$file->__toString().'</li>';
        }
        $ret .= '</ul>';
            
        return $ret;
    }
        
    /**
     * Returns a textual representation of any amount of bytes
     *
     * @author        wesman20 (php.net)
     * @author        Jonas John
     * @version    0.3
     * @link            http://www.jonasjohn.de/snippets/php/readable-filesize.htm
     *
     * @param int $size The size to convert
     *
     * @return string A readable representation
     */
    public static function makeBytesReadable($size) {
            
        $mod        = 1024;
        $units    = explode(' ','B KB MB GB TB PB');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
            
        return round($size, 2) . ' ' . $units[$i];
    }
  }
    
/**
 * gdcFileUploaderFile stores information about an uploaded file
 *
 * @author            Georg Dresler <georg@g-dresler.de>
 * @package        de.g_dresler.code
 * @version        1.1
 * @copyright    Georg Dresler 2009
 * @license        GPL 3.0 http://www.gnu.org/licenses/
 */
class gdcFileUploaderFile {
        
    protected
        $fieldName,
        $fileName,
        $tmpName,
        $type,
        $error,
        $size                = 0,
        $newName        = null,
        $path                = null,
        $isUploaded    = false,
        $isGood            = true;
        
    /**
     * Constructor
     *
     * @param array     $file     An array containing file information
     * @param string    $field    The HTML form's input field name
     */
    public function __construct(array $file, $field) {

        $this->fieldName    = $field;
        $this->fileName        = $file['name'];
        $this->tmpName        = $file['tmp_name'];
        $this->type                = $file['type'];
        $this->error            = $file['error'];
        $this->size                = $file['size'];
        $this->isGood            = (is_uploaded_file($file['tmp_name']) && $this->getError(true) == 0);
    }
        
    /**
     * Sets the new name
     *
     * @param string $newName The new name
     */
    public function setNewName($newName) {
            
        $this->newName = $newName;
    }
        
    /**
     * Sets the path to the file
     *
     * @param string $path The path
     */
    public function setPath($path) {
            
        $this->path = $path;
    }
        
    /**
     * Sets uploaded flag
     *
     * @param bool $isUploaded true, if file has been processed, otherwise false
     */
    public function setUploaded($isUploaded) {
            
        $this->isUploaded = $isUploaded;
    }
        
    /**
     * Returns the name of the HTML input field
     *
     * @return string The HTML input field's name
     */
    public function getField() {
            
        return $this->fieldName;
    }
        
    /**
     * Returns the name
     *
     * @return string The name
     */
    public function getName() {
            
        return $this->fileName;
    }
        
    /**
     * Returns the temporary name
     *
     * @return string The temporary name
     */
    public function getTmp() {
            
        return $this->tmpName;
    }
        
    /**
     * Returns the type
     *
     * @param bool    $split    If true, the MIME-Type will be split at the '/'
     *
     * @return string The type
     */
    public function getType($split = false) {
            
        return $split ? substr(strrchr($this->type, '/'), 1) : $this->type;
    }
        
    /**
     * Returns the file's suffix
     *
     * @param bool    $includeDot    Set to false to disable a leading dot (.)
     *
     * @return string The suffix
     */
    public function getSuffix($includeDot = true) {
            
        $info = pathinfo($this->getName(), PATHINFO_EXTENSION);
        return $includeDot ? '.'.$info : $info;
    }
        
    /**
     * Returns the error code/message
     *
     * @param bool $numeral true, to retrieve (int) error code, false (default) to retrieve (string)
     *
     * @return mixed (int) if flag is set, otherwise (string)
     */
    public function getError($numeral = false) {
            
        if ($numeral) return $this->error;
            
        switch ($this->error) {
        case '0':
            return 'UPLOAD_ERR_OK';
        case '1':
            return 'UPLOAD_ERR_INI_SIZE';
        case '2':
            return 'UPLOAD_ERR_FORM_SIZE';
        case '3':
            return 'UPLOAD_ERR_PARTIAL';
        case '4':
            return 'UPLOAD_ERR_NO_FILE';
        case '6':
            return 'UPLOAD_ERR_NO_TMP_DIR';
        case '7':
            return 'UPLOAD_ERR_CANT_WRITE';
        case '8':
            return 'UPLOAD_ERR_EXTENSION';
        }
    }
            
    /**
     * Returns a detailed error message
     *
     * @return string An error message
     */
    public function getErrorMsg() {
            
        switch ($this->error) {
        case '0':
            return $this->getError().': The file was successfully uploaded';
        case '1':
            return $this->getError().': The size exceeds upload_max_filesize set in php.ini';
        case '2':
            return $this->getError().': The size exceeds MAX_FILE_SIZE set in the HTML form';
        case '3':
            return $this->getError().': The file was only partially uploaded';
        case '4':
            return $this->getError().': No file was uploaded';
        case '6':
            return $this->getError().': No temporary directory was set';
        case '7':
            return $this->getError().': Could not write to disk';
        case '8':
            return $this->getError().': File upload stopped due to extension';
        }
    }    
            
    /**
     * Returns the size
     *
     * @return int The size
     */
    public function getSize() {
            
        return $this->size;
    }
        
    /**
     * Returns a readable representation of the file's size
     *
     * @see gdcFileUploader::makeBytesReadable()
     *
     * @return string Readable size
     */
    public function getSizeReadable() {
            
        return gdcFileUploader::makeBytesReadable($this->getSize());
    }
        
    /**
     * Returns the new name
     *
     * @return string The new name
     */
    public function getNewName() {
            
        return $this->newName;
    }
        
    /**
     * Returns the path
     *
     * @return string The path
     */
    public function getPath() {
            
        return $this->path.'/'.$this->newName;
    }
        
    /**
     * Indicates wether the file has been processed/uploaded
     *
     * @return bool true, the file has been processed, otherwise false
     */
    public function isUploaded() {
            
        return $this->isUploaded;
    }
        
    /**
     * Indicates wether any error occured
     *
     * @return bool true, no errors occured, otherwise false
     */
    public function isGood() {
            
        return $this->isGood;
    }        
        
    /**
     * toString
     */
    public function __toString() {
            
        $ret  = '<ul>';
        $ret .= '<li>Name: '.$this->getName().'</li>';
        $ret .= '<li>Size: '.$this->getSize().'</li>';
        $ret .= '<li>Type: '.$this->getType().'</li>';
        $ret .= '<li>Error: '.$this->getError().'</li>';
        $ret .= '</ul>';
            
        return $ret;;
    }
}
    
/**
 * gdcFileUploaderCriteria stores criteria for file uploading
 * The class implements the Singleton pattern
 *
 * @author            Georg Dresler <georg@g-dresler.de>
 * @package        de.g_dresler.code
 * @version        1.0
 * @copyright    Georg Dresler 2009
 * @license        GPL 3.0 http://www.gnu.org/licenses/
 */
class gdcFileUploaderCriteria {
        
    protected static
        $instance = null;
            
    protected
        $type            = null,
        $typeMode    = null,
        $size            = null,
        $sizeMode    = null,
        $modes        = array();
        
    /**
     * Constructor
     */
    protected function __construct() {
            
        $this->modes = array('EQUAL', 'NOT_EQUAL', 'GREATER', 'GREATER_EQUAL', 'LESS', 'LESS_EQUAL');
    }
        
    /**
     * Clone. Private due to Singleton pattern
     */
    private function __clone() {}
        
    /**
     * Retrieve Singleton instance
     *
     * @return gdcFileUploaderCriteria A gdcFileUploaderCriteria instance
     */
    public function getInstance() {
            
        if (is_null(self::$instance)) {
                
            self::$instance = new gdcFileUploaderCriteria;
        }
            
        return self::$instance;;
    }
        
    /**
     * Indicates wether any criteria is set
     *
     * @return bool true, criteria is set, otherwise false
     */
    public function hasCriteria() {
            
        return (is_null($this->type) && is_null($this->size)) ? false : true;
    }
        
    /**
     * Returns an array containing the allowed comparison modes
     *
     * @return array The allowed comparison modes
     */
    public function getModes() {
            
        return $this->modes;
    }
        
    /**
     * Sets criteria by string
     *
     * Use this syntax to add criteria. Seperate the strings by space ( )
     * - to set type: type=image|EQUAL
     * - to set size: size=1024|GREATER
     *
     * @param string    $criteria The criteria string
     *
     * @return bool true, setting was successfull, otherwise false
     */
    public function setByString($criteria) {
            
        if (!is_null($criteria) && $criteria != "") {
                
            $crits = explode(' ', $criteria);
                
            foreach ($crits as $crit) {
                    
                $mode    = strtoupper(substr(strrchr($crit, '|'), 1));
                $crit = str_replace('|'.$mode, '', $crit);
                $crit = explode('=', $crit);

                switch (strtolower($crit[0])) {
                case 'type':
                    $this->setType(strtolower($crit[1]), $mode);
                    break;
                case 'size':
                    $this->setSize(strtolower($crit[1]), $mode);
                    break;
                }
            }
                
            return true;
        } else {
                
            return false;
        }
    }
        
    /**
     * Sets the file type
     *
     * @param string    $type    The file's type
     * @param string    $mode    The comparison mode; Either EQUAL or NOT_EQUAL
     *
     * @return    gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function setType($type, $mode = 'EQUAL') {
            
        if ($mode != 'EQUAL' && $mode != 'NOT_EQUAL') {
                
            throw new gdcFileUploaderException(sprintf('The mode "%s" is not valid for setType() criteria', $mode));
        }
            
        $this->type            = $type;
        $this->typeMode    = $mode;
            
        return $this->getInstance();
    }
        
    /**
     * Returns the type setting
     *
     * @return string The type setting
     */
    public function getType() {
            
        return $this->type;
    }
        
    /**
     * Returns the type mode setting
     *
     * @return string The type mode setting; either EQUAL or NOT_EQUAL
     */
    public function getTypeMode() {
            
        return $this->typeMode;
    }
        
    /**
     * Clears the type settings
     *
     * @return gddcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function removeType() {
            
        $this->type            = null;
        $this->typeMode    = null;
            
        return $this->getInstance();
    }
        
    /**
     * Sets the size
     *
     * @param int         $size The size in bytes
     * @param string    $mode    The comparison mode
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function setSize($size, $mode = 'LESS_EQUAL') {
            
        if (!in_array($mode, $this->modes)) {
                
            throw new gdcFileUploaderException(sprintf('The mode "%s" is not a valid comparison mode. Only the following modes are allowed: %s', $mode, var_export($this->modes)));
        }
            
        $this->size            = $size;
        $this->sizeMode    = $mode;
            
        return $this->getInstance();
    }
        
    /**
     * Returns the size setting
     *
     * @return int The size in bytes
     */
    public function getSize() {
            
        return $this->size;
    }
        
    /**
     * Returns the size mode
     *
     * @return string The size mode
     */
    public function getSizeMode() {
            
        return $this->sizeMode;
    }
        
    /**
     * Clears the size settings
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function removeSize() {
            
        $this->size            = null;
        $this->sizeMode    = null;
            
        return $this->getInstance();
    }
        
    /**
     * Removes all settings
     *
     * @return gdcFileUploaderCriteria The gdcFileUploaderCriteria instance
     */
    public function removeAll() {
            
        $this->size            = null;
        $this->sizeMode    = null;
        $this->type            = null;
        $this->typeMode    = null;
            
        return $this->getInstance();
    }
        
    /**
     * toString
     */
    public function __toString() {
            
        $ret = '';
            
        if (!is_null($this->getType())) {
            $ret .= 'type='.$this->getType().'|'.$this->getTypeMode();
        }
            
        if (!is_null($this->getSize())) {
            $ret .= 'size='.$this->getSize().'|'.$this->getSizeMode();
        }
            
        return $ret;
    }
}
    
/**
 * gdcFileUploaderException is thrown by gdcFileUploader
 *
 * @author         Georg Dresler <georg@g-dresler.de>
 * @package        de.g_dresler.code
 * @version        1.0
 * @copyright      Georg Dresler 2009
 * @license        GPL 3.0 http://www.gnu.org/licenses/
 */
class gdcFileUploaderException extends Exception {}
?>