<?php

	namespace Faultier\FileUploader;
	
	use Faultier\FileUploader\ConstraintInterface;
	use Faultier\FileUploader\File;
	
	abstract class Constraint extends ConstraintInterface {
	
		private $mode;
		private $name;
		
		public function __construct($mode, $name) {
			$this->setMode($mode);
			$this->setName($name);
		}
	
		public function setMode($mode) {
			$this->mode = $mode;
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		public function setName($name) {
			$this->name = $name;
		}
		
		public function getName() {
			return $this->name;
		}
		
		abstract public function holds(File $file);

		public function __toString() {
			return 'Name: '.$this->getName().', Mode: '.$this->getMode();
		}
	}

?>