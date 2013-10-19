<?php

namespace Faultier\FileUpload\Constraint;
	
use Faultier\FileUpload\File;
	
interface ConstraintInterface {
	
	public function setOptions($options);
	public function isValid(File $file);

    public function getErrors();
}
