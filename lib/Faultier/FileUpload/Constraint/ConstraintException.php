<?php
	
	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface
	
	class ConstraintException extends \Exception {
	
		private $file;
		private $constraint;
		
		public function setFile(File $file) {
			$this->file = $file;
		}
		
		public function getFile() {
			return $this->file;
		}
		
		public function setConstraint(ConstraintInterface $constraint) {
			$this->constraint = $constraint;
		}
		
		public function getConstraint() {
			return $this->constraint;
		}
	}

?>