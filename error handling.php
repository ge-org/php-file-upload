<?php

// TODO constraint string parser
// 'size' => SizeConstraint->parse('<= 2M')
// 'foobar' => FoobarConstraint->parse('...')

// ERROR HANDLING
// callback??

$fileUploader = new FileUpload('./', array(
	'size' => '<= 2M',
	'type' => '= image',
	'type' => '!= jpg'
));

$fileUploader->errorConstraint(function(\Exception $e) {
	
});

$fileUploader->error(function(\Exception $e) use ($logger) {
	$logger->error($e->getMessage());
});

$fileUploader->saveFiles(function(File $file) {
	$file->setName('Hello-World');
	return '/other/dir';
});

///////////////////////////////////////////////////////////////////////////////////////////////

if (!saveFiles()) {
	$badFiles = $fileUpload->getNotUploadedFiles();
}

foreach ($badFiles as $file) {

	// TODO convenient methods for size, type constraints

	switch($file->getErrorType()) {
		case FileUpload::ERR_PHP_UPLOAD:
		case FileUpload::ERR_PHP_MOVE_FILE:
		case FileUpload::ERR_FILESYSTEM:
			echo redirect(500);
		break;
		
		case FileUpload::ERR_CONSTRAINT:
			$file->getBrokenConstrains();
		break;
	}
	
	foreach ($errors as $error) {
		
			// ERR_PHP_UPLOAD
			// ERR_CONSTRAINT
			// ERR_FILESYSTEM
	
		/*
		
			how to combine php errors and lib errors???
			files with errors: php err only, not uploaded only, both??
		
			array(
				ERR_PHP_UPLOAD => array(8 => msg),				-> no err_filesystem
				ERR_FILESYSTEM => array(0 => msg),				-> no err_constraint
				ERR_CONSTRAINT => array(									-> no err_move
					constraint a,
					constraint b
				),
				ERR_MOVE_FILE
			)
		
			php error while uploading
				-> php err
			constraint broke
				-> list of constraints
			upload directory does not exist 0
			upload directory is no directory 1
			upload directory not writable 2
			can not move file to new place
		
		*/
	}
}

?>