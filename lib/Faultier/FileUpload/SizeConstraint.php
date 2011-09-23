<?php

	namespace Faultier\FileUpload;
	
	use Faultier\FileUpload\Constraint;
	use Faultier\FileUpload\File;
	
	class SizeConstraint extends Constraint {
	
		private $size; // in bytes
		
		public function setSize($size) {
			$this->size = $size;
		}
		
		public function getSize() {
			return $this->size;
		}
		
		public function holds(File $file) {
			return true;
		}
		
		public function __toString() {
			return parent::__toString().', Size: '.$this->getSize();
		}
		
	}

?>