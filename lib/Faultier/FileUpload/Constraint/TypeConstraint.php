<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	class TypeConstraint extends ConstraintInterface {
	
		const EQUAL = '=';
		const CONTAINS = '~';
		const NOT_EQUAL = '!=';
		
		private $modes = array(
			TypeConstraint::EQUAL,
			TypeConstraint::CONTAINS,
			TypeConstraint::NOT_EQUAL
		);
	
		private $mode;
		private $type;
		
		public function setMode($mode) {
			if (in_array($mode, $modes)) {
				$this->mode = $mode;
			} else {
				throw new InvalidArgumentException(sprintf('The mode "%s" is not valid', $mode));
			}
		}
		
		public function getMode() {
			return $this->mode;
		}
		
		public function setType($type) {
			$this->type = $type;
		}
		
		public function getType() {
			return $this->type;
		}
		
		public function parse($options) {
		
		}
		
		public function holds(File $file) {
			return true;
		}
	}

?>