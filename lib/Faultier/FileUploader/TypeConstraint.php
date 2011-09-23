<?php

	namespace Faultier\FileUploader;
	
	use Faultier\FileUploader\Constraint;
	use Faultier\FileUploader\File;
	
	class SizeConstraint extends Constraint {
	
		private $type;
		
		public function setType($type) {
			$this->type = $type;
		}
		
		public function getType() {
			return $this->type;
		}
		
		public function holds(File $file) {
			return true;
		}
		
		public function __toString() {
			return parent::__toString().', Type: '.$this->getType();
		}
	
	}

?>