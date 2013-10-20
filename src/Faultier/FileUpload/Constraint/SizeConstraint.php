<?php
namespace Faultier\FileUpload\Constraint;
	
use \Faultier\FileUpload\File;

class SizeConstraint extends baseConstraint {

	
	const LESS = '<';
	const EQUAL = '=';
	const GREATER = '>';
	const LESS_EQUAL = '<=';
	const GREATER_EQUAL = '>=';

    const fileIsLarger="fileIsLarger";

    const fileIsSmaller="fileIsSmaller";

    const fileIsNotEqual="fileIsNotEqual";

    protected $messageTemplates=array(
        self::fileIsLarger=>"The file uploaded is greater than the size limit!",
        self::EQUAL=>"The file uploaded is not equal to the requested size!",
        self::fileIsSmaller=>"The file uploaded is less than the size limit!",
    );

	
	protected $modes = array(
		self::LESS,
		self::EQUAL,
		self::GREATER,
		self::LESS_EQUAL,
		self::GREATER_EQUAL
	);
	    
    /*
    **  @param size --  size of uploaded file in bytes,
    */
	protected $size; // in bytes

    /*
    **  @param mode --  way by which user wants to validate file, less-than, greater than etc.
    */
	protected $mode;
		

    /*
    **  @function setSize  --  sets size of uploaded file
    **  @param $size    --  size of uploaded file
    **  @returns null
    */		
	public function setSize($size) {
		if (is_numeric($size) && $size >= 0) {
			$this->size = $size;
		} else {
			throw new \InvalidArgumentException(sprintf('The size "%i" is not valid', $size));
		}
	}


    /*
    **  @function getSize  --  gets size of uploaded file
    **  @returns size of uploaded file
    */			
	public function getSize() {
		return $this->size;
	}
		

    /*
    **  @function setMode   --  sets value of property, $this->property
    **  @param mode --  value to be set
    */
	public function setMode($mode) {
		if (in_array($mode, $this->modes)) {
			$this->mode = $mode;
		} else {
			throw new \InvalidArgumentException(sprintf('The mode "%s" is not valid', $mode));
		}
	}


    /*
    **  @function getMode   --  retutns value of property, $this->property
    */		
	public function getMode() {
		return $this->mode;
	}


    /*
    **  @function setOptions   --  performs regular expressions to check user options
    **  @param $options -- options
    */		
	public function setOptions($options) {
			
		// < 123
		// = 123
		// > 123
		// <= 123
		// >= 123
		if(isset($options['value'])){
			$matches = array();
			$hasMatches = preg_match('#^(<|=|>|<=|>=) ([0-9]+)$#', $options['value'], $matches);
			
			if ($hasMatches === 1) {
				$this->setMode($matches[1]);
				$this->setSize($matches[2]);
			}			    
		}


	}

    /*
    **  @function isValid   --  checks if uploaded is valid and sets error messages
    **  @param $file -- instance of class,\Faultier\FileUpload\File
    */		
	public function isValid(File $file) {
			
		$valid = false;
		switch ($this->getMode()) {
			case self::LESS:
				$valid = ($file->getSize() < $this->getSize());
                $message=$this->messageTemplates[self::fileIsLarger];
				break;
					
			case self::EQUAL:
				$valid = ($file->getSize() == $this->getSize());
                $message=$this->messageTemplates[self::fileIsNotEqual];
				break;

			case self::GREATER:
				$valid = ($file->getSize() > $this->getSize());
                $message=$this->messageTemplates[self::fileIsSmaller];
				break;
					
			case self::LESS_EQUAL:
				$valid = ($file->getSize() <= $this->getSize());
                $message=$this->messageTemplates[self::fileIsLarger];
				break;
					
			case self::GREATER_EQUAL:
                $valid = ($file->getSize() >= $this->getSize());
				$message=$this->messageTemplates[self::fileIsSmaller];
				break;
		}
        if(!$valid){
            $messageTemplate=$message;
            $this->addError($messageTemplate);
        }
		return $valid;
	}
		
}

?>
