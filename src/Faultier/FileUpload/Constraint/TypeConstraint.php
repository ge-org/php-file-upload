<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	class TypeConstraint implements ConstraintInterface {
	
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
	
		private $mode;
		private $types = array();
		
		public function getConstraintType() {
			return 'type';
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
		
		public function setTypes(array $types) {
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
			
			$holds = true;
			switch ($this->getMode()) {
				case TypeConstraint::EQUAL:
					foreach ($this->getTypes() as $type) {
						if ($file->getMimeType() != $type) {
							$holds = false;
							break;
						}
					}
					break;
					
				case TypeConstraint::NOT_EQUAL:
					foreach ($this->getTypes() as $type) {
						if ($file->getMimeType() == $type) {
							$holds = false;
							break;
						}
					}
					break;
					
				case TypeConstraint::CONTAINS:
					foreach ($this->getTypes() as $type) {
						if (strpos($file->getMimeType(), $type) === false) {
							$holds = false;
							break;
						}
					}
					break;
					
				case TypeConstraint::CONTAINS_NOT:
					foreach ($this->getTypes() as $type) {
						if (strpos($file->getMimeType(), $type) !== false) {
							$holds = false;
							break;
						}
					}
					break;
			}

			return $holds;
		}
	}

?>