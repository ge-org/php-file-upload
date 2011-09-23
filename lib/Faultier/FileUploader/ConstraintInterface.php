<?php

	namespace Faultier\FileUploader;
	
	use Faultier\FileUploader\File;
	
	interface ConstraintInterface {
	
		const EQUAL;
		const NOT_EQUAL;
		const GREATER;
		const GREATER_EQUAL;
		const LESS;
		const LESS_EQUAL;
	
		public function setMode($mode);
		public function getMode();
		
		public function setName($name);
		public function getName();
	
		public function holds(File $file);
		
		public function __toString();
	}

?>