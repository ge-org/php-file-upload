<?php

// TODO unit tests!
// TODO doc comments
// TODO readme

$fileUploader = new FileUpload('./', array(
	'size' => '<= 2M',
	'type' => '= image',
	'type' => '!= jpg tiff'
));

$fileUploader->error(function($type, $message, $file) {
	
});

$fileUploader->errorConstraint(function($constraint, $file) {
	
});

$fileUploader->save(function(File $file) {
	$file->setName('Hello-World');
	return 'some/other/dir';
});

?>