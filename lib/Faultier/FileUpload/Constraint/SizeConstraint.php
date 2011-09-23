<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	class SizeConstraint implements ConstraintInterface {
	
		const LESS = '<';
		const EQUAL = '=';
		const GREATER = '>';
		const LESS_EQUAL = '<=';
		const GREATER_EQUAL = '>=';
	
		private $modes = array(
			SizeConstraint::LESS,
			SizeConstraint::EQUAL,
			SizeConstraint::GREATER,
			SizeConstraint::LESS_EQUAL,
			SizeConstraint::GREATER_EQUAL
		);
	
		private $mode;
		private $size; // in bytes
		
		public function setSize($size) {
			if (is_numeric($size) && $size >= 0) {
				$this->size = $size;
			} else {
				throw new InvalidArgumentException(sprintf('The size "%i" is not valid', $size));
			}
		}
		
		public function getSize() {
			return $this->size;
		}
		
		public function setMode($mode) {
			if (in_array($mode, $modes)) {
				$this->mode = $mode;
			} else {
				throw new \InvalidArgumentException(sprintf('The mode "%s" is not valid', $mode));
			}
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		public function parse($options) {
			
		}
		
		public function holds(File $file) {
			return true;
		}
		
	}

?>