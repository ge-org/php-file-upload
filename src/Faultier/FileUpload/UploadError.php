<?php

  namespace Faultier\FileUpload;
  
  use Faultier\FileUpload\File;
  use Faultier\FileUpload\Constraint\ConstraintInterface;
  
  class UploadError {
  
    const ERR_PHP_UPLOAD = 0;
    const ERR_FILESYSTEM = 1;
    const ERR_CONSTRAINT = 2;
  
    private $type;
    private $message;
    private $file;
    private $constraint;
    
    private $availableTypes = array(
      UploadError::ERR_PHP_UPLOAD,
      UploadError::ERR_FILESYSTEM,
      UploadError::ERR_CONSTRAINT
    );
    
    public function __construct($type, $messsage, File $file = null, ConstraintInterface $constraint = null) {
      $this->setType($type);
      $this->setMessage($messsage);
      $this->file = $file;
      $this->constraint = $constraint;
    }
    
    public function setType($type) {
      if (!in_array($type, $this->availableTypes, true)) {
        throw new \InvalidArgumentException(sprintf('The type "%s" is not a valid type. Muste be one of %s.', $type, implode(' ', $this->availableTypes)));
      } else {
        $this->type = $type;
      }
    }
    
    public function getType() {
      return $this->type;
    }
    
    public function setMessage($message) {
      $this->message = $message;
    }
    
    public function getMessage() {
      return $this->message;
    }
    
    public function setFile(File $file) {
      $this->file = $file;
    }
    
    public function getFile() {
      return $this->file;
    }
    
    public function setConstraint(ConstraintInterface $constraint) {
      $this->constraint = $constraint;
    }
    
    public function getConstraint() {
      return $this->constraint;
    }
  
  }

?>