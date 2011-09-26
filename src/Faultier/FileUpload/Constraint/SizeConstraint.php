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
	
		private $size; // in bytes
		private $mode;
		
		public function getConstraintType() {
			return 'size';
		}
		
		public function setSize($size) {
			if (is_numeric($size) && $size >= 0) {
				$this->size = $size;
			} else {
				throw new \InvalidArgumentException(sprintf('The size "%i" is not valid', $size));
			}
		}
		
		public function getSize() {
			return $this->size;
		}
		
		public function setMode($mode) {
			if (in_array($mode, $this->modes)) {
				$this->mode = $mode;
			} else {
				throw new \InvalidArgumentException(sprintf('The mode "%s" is not valid', $mode));
			}
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		public function parse($options) {
			
			// < 123
			// = 123
			// > 123
			// <= 123
			// >= 123
			
			$matches = array();
			$hasMatches = preg_match('#^(<|=|>|<=|>=) ([0-9]+)$#', $options, $matches);
			
			if ($hasMatches === 1) {
				$this->setMode($matches[1]);
				$this->setSize($matches[2]);
			}
		}
		
		public function holds(File $file) {
			
			switch ($this->getMode()) {
				case SizeConstraint::LESS:
					return ($file->getSize() < $this->getSize());
					
				case SizeConstraint::EQUAL:
					return ($file->getSize() == $this->getSize());
					
				case SizeConstraint::GREATER:
					return ($file->getSize() > $this->getSize());
					
				case SizeConstraint::LESS_EQUAL:
					return ($file->getSize() <= $this->getSize());
					
				case SizeConstraint::GREATER_EQUAL:
					return ($file->getSize() >= $this->getSize());
				
				default:
					return false;
			}
		}
		
	}

?>