<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	class TypeConstraint extends ConstraintInterface {
	
		const EQUAL = '=';
		const NOT_EQUAL = '!=';
		const CONTAINS = '~';
		const CONTAINS_NOT = '!~';
		
		private $modes = array(
			TypeConstraint::EQUAL,
			TypeConstraint::NOT_EQUAL,
			TypeConstraint::CONTAINS,
			TypeConstraint::CONTAINS_NOT
		);
	
		private $constraintType;
		private $mode;
		private $types = array();
		
		public function setConstraintType($type) {
			$this->constraintType = $type;
		}
		
		public function getConstraintType() {
			return $this->constraintType;
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
		
		public function setTypes(arary $types) {
			$this->types = $types;
		}
		
		public function getTypes() {
			return $this->types;
		}
		
		public function parse($options) {
			
			// = xyz xyz
			// != xyz xyz
			// ~ xyz xyz
			// !~ xyz xyz
			
			$matches = array();
			$hasMatches = preg_match('#^(=|!=|~|!~) (.+)$#', $options, $matches);
			
			if ($hasMatches === 1) {
				$this->setMode($matches[1]);
				$this->setTypes(explode(' ', $matches[2]));
			}
		}
		
		public function holds(File $file) {
			
			switch ($this->getMode()) {
				case TypeConstraint::EQUAL:
					foreach ($this->getTypes() as $type) {
						if ($file->getMimeType() != $type) {
							return false;
						}
					}
					return true;
					
				case TypeConstraint::NOT_EQUAL:
					foreach ($this->getTypes as $type) {
						if ($file->getMimeType() == $type) {
							return false;
						}
					}
					return true;
					
				case TypeConstraint::CONTAINS:
					foreach ($this->getTypes as $type) {
						if (strpos($file->getMimeType(), $this->getType()) === false) {
							return false;
						}
					}
					return true;
					
				case TypeConstraint::CONTAINS_NOT:
					foreach ($this->getTypes() as $type) {
						if (strpos($file->getMimeType(), $this->getType()) !== false) {
							return false;
						}
					}
					return true;
					
				default:
					return false;
			}
		}
	}

?>