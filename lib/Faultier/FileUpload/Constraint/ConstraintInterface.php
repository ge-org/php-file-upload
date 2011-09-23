<?php

	namespace Faultier\FileUpload\Constraint;
	
	use Faultier\FileUpload\File;
	
	interface ConstraintInterface {
	
		public function setType($type);
		public function getType();
	
		public function parse($options);
		public function holds(File $file);
	}

?>