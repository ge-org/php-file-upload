<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	
	interface ConstraintInterface {
	
		public function setConstraintType($type);
		public function getConstraintType();
	
		public function parse($options);
		public function holds(File $file);
	}

?>