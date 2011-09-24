<?php
	
	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface
	
	class ConstraintException extends \Exception {
	
		private $constraint;
		
		public function setConstraint(ConstraintInterface $constraint) {
			$this->constraint = $constraint;
		}
		
		public function getConstraint() {
			return $this->constraint;
		}
	}

?>