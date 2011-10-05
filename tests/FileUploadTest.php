<?php

	/*
	 * install via PEAR:
	 * pear channel-discover pear.php-tools.net
	 * pear install pat/vfsStream-beta
	 *
	 * or clone from github: https://github.com/mikey179/vfsStream
	*/
	require_once 'vfsStream/vfsStream.php';
	require_once 'vfsStream/visitor/vfsStreamPrintVisitor.php';

	use Faultier\FileUpload\FileUpload;
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\UploadError;
	use Faultier\FileUpload\Constraint\SizeConstraint;

	class FileUploadTest extends \PHPUnit_Framework_TestCase {
	
		protected $dirGood = 'exampleDir';
		protected $dirBad = 'notWritable';
		protected $dirFile = 'foo.txt';
	
		protected $up;
		
		public function setUp() {
			vfsStream::create(array(
				$this->dirGood => array(
					$this->dirFile => 'foo'
				),
				$this->dirBad => array()
			));
			
			$this->up = new FileUpload(vfsStream::url($this->dirGood));
		}
		
		/**
		 * @test
		 */
		public function constructor() {
			//$this->assertInstanceOf('Faultier\FileUpload\FileUpload', $this->up);
		}
		
		/**
		 * @test
		 */
		public function uploadDirectoryGood() {
			$this->up->setUploadDirectory(vfsStream::url($this->dirGood));
			$this->assertEquals(vfsStream::url($this->dirGood), $this->up->getUploadDirectory());
		}
		
		/**
		 * @test
		 * @expectedException					\InvalidArgumentException
		 * @expectedExceptionMessage	The given upload directory does not exist
		 */
		public function uploadDirectoryNull() {
			$this->up->setUploadDirectory(null);
		}
		
		/**
		 * @test
		 * @expectedException					\InvalidArgumentException
		 * @expectedExceptionMessage	The given upload directory is not a directory
		 */
		public function uploadDirectoryFile() {
			$this->up->setUploadDirectory(vfsStream::url($this->dirGood.DIRECTORY_SEPARATOR.$this->dirFile));
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The given upload directory is not writable
		 */
		public function uploadDirectoryNotWritable() {
		  vfsStreamWrapper::getRoot()->getChild($this->dirBad)->chmod(0000);
		  $this->up->setUploadDirectory(vfsStream::url($this->dirBad));
		}
		
		/**
		 * @test
		 */
		public function filesEmpty() {
			$this->assertEquals(array(), $this->up->getFiles());
			$this->assertFalse($this->up->hasFiles());
			$this->assertEquals(array(), $this->up->getUploadedFiles());
			$this->assertEquals(array(), $this->up->getNotUploadedFiles());
			$this->assertEquals(0, $this->up->getAggregatedFileSize());
			$this->assertEquals('0.00 B', $this->up->getReadableAggregatedFileSize());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 */
		public function fileNotExists() {
		  $this->up->getFile('');
		}
		
		/**
		 * @test
		 */
		public function constraintsEmpty() {
			$this->assertEquals(array(), $this->up->getConstraints());
			$this->assertFalse($this->up->hasConstraints());
			$this->up->removeAllConstraints();
			$this->assertFalse($this->up->hasConstraints());
		}
		
		/**
		 * @test
		 */
		public function constraintAddOne() {
		  $constraint = new SizeConstraint;
		  $this->up->addConstraint($constraint);
		  $this->assertTrue($this->up->hasConstraints());
		  $this->assertEquals(array($constraint), $this->up->getConstraints());
		}
		
		/**
		 * @test
		 */
		public function constraintAddManyObjects() {
		  $constraint = new SizeConstraint;
		  $constraint2 = new SizeConstraint;
		  $this->up->addConstraints(array($constraint, $constraint2));
		  $this->assertTrue($this->up->hasConstraints());
		  $this->assertEquals(array($constraint, $constraint2), $this->up->getConstraints());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage 
		 */
		public function constraintAddInvalidInterface() {
		  $constraint = new File;
		  $this->up->addConstraints(array($constraint));
		}
		
		/**
		 * @test
		 */
		public function constrainAddWithString() {
		  $this->up->removeAllConstraints();
		  $this->up->addConstraints(array(
		    'size' => '= 1',
		    'type' => '= image'
		  ));
		  $this->assertTrue($this->up->hasConstraints());
		}
		
		/**
		 * @test
		 */
		public function constrainAddWithStringMixed() {
		  $this->up->removeAllConstraints();
		  $this->up->addConstraints(array(
		    'size' => '= 1',
		    'type' => '= image',
		    new SizeConstraint
		  ));
		  $this->assertTrue($this->up->hasConstraints());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The constraint alias "foo" has not been registered
		 */
		public function constrainAddUnregistered() {
		  $this->up->removeAllConstraints();
		  $this->up->addConstraints(array(
		    'foo' => '= 1'
		  ));
		}
		
		/**
		 * @test
		 */
		public function constraintRegister() {
		  $this->up->registerConstraintNamespace('bar', 'Faultier\FileUpload\Constraint\SizeConstraint');
		  $this->assertEquals(array(
		    'size' => 'Faultier\FileUpload\Constraint\SizeConstraint',
		    'type' => 'Faultier\FileUpload\Constraint\TypeConstraint',
		    'bar' => 'Faultier\FileUpload\Constraint\SizeConstraint'
		  ), $this->up->getConstraintNamespaces());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The constraint "foo" does not exist
		 */
		public function constraintRegisterInvalidClass() {
		  $this->up->registerConstraintNamespace('bar', 'foo');
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The "Faultier\FileUpload\FileUpload" must implement "Faultier\FileUpload\Constraint\ConstraintInterface"
		 */
		public function constraintRegisterInvalidInterface() {
		  $this->up->registerConstraintNamespace('bar', 'Faultier\FileUpload\FileUpload');
		}
		
		/**
		 * @test
		 */
		public function constructorWithConstraints() {
		  $up = new FileUpload(vfsStream::url($this->dirGood), array(
		    'size' => '< 1024',
		    new SizeConstraint
		  ));
		  $this->assertTrue($up->hasConstraints());
		}
		
		/**
		 * @test
		 */
		public function parseFilesArray() {
			$_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
			
			$up = new FileUpload(vfsStream::url($this->dirGood));
			$this->assertTrue($up->hasFiles());
			$this->assertEquals(1, count($up->getFiles()));
			$this->assertInstanceOf('Faultier\FileUpload\File', $up->getFile('foo'));
		}
		
		/**
		 * @test
		 */
		public function parseMultiFilesArray() {
			$_FILES['foo']['name'] = array('name-bar');
			$_FILES['foo']['tmp_name'] = array('tmp_name-bar');
			$_FILES['foo']['type'] = array('type-bar');
			$_FILES['foo']['size'] = array(1024);
			$_FILES['foo']['error'] = array(UPLOAD_ERR_OK);
			
			$up = new FileUpload(vfsStream::url($this->dirGood));
			$this->assertTrue($up->hasFiles());
			$this->assertEquals(1, count($up->getFiles()));
			$this->assertTrue($up->isMultiFileUpload());
		}
		
		/**
		 * @test
		 * @expectedException \BadMethodCallException
		 */
		public function multiFilesArrayGetFile() {
			$_FILES['foo']['name'] = array('name-bar');
			$_FILES['foo']['tmp_name'] = array('tmp_name-bar');
			$_FILES['foo']['type'] = array('type-bar');
			$_FILES['foo']['size'] = array(1024);
			$_FILES['foo']['error'] = array(UPLOAD_ERR_OK);
			
			$up = new FileUpload(vfsStream::url($this->dirGood));
			$up->getFile('foo');
		}
		
		/**
		 * @test
		 */
		public function saveWithoutFiles() {
		  $up = new FileUpload(vfsStream::url($this->dirGood));
			$up->save();
			$this->assertEquals(array(), $up->getUploadedFiles());
		}
		
		/**
		 * @test
		 */
		public function saveWithFiles() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
			
				// for mockign reasons
	      require 'MovesFilesFileUpload.php';
			
			$up = new MovesFilesFileUpload(vfsStream::url($this->dirGood));
			$up->save();
		}
		
		/**
		 * @test
		 */
		public function saveWithFilesAndClosure() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
			$up->save(function(File $file){
			  $file->setName('foo');
			});
			$this->assertEquals('foo', $up->getFile('foo')->getName());
		}
		
		/**
		 * @test
		 */
		public function saveWithFilesAndClosureWithReturn() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
		  
		  $path = vfsStream::url($this->dirGood);
		  
			$up->save(function(File $file) use ($path) {
			  $file->setName('foo');
			  return $path;
			});
			$this->assertEquals('foo', $up->getFile('foo')->getName());
		}
		
		/**
		 * @test
		 */
		public function saveWithFilesAndClosureWithInvalidReturn() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));		  
			$up->save(function(File $file) {
			  $file->setName('foo');
			  return '';
			});
			$this->assertEquals('foo', $up->getFile('foo')->getName());
		}
		
		/**
		 * @test
		 */
		public function saveWithFilesAndErrorClosure() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
		  
		  $fileName = null;
		  $up->error(function(UploadError $error) use (&$fileName) {
		    $fileName = $error->getFile()->getName();
		  });
			$up->save();
			
			$this->assertEquals('tmp_name-bar', $fileName);
		}
		
		/**
		 * @test
		 */
		public function saveOneFile() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
			$up->saveFile($up->getFile('foo'));
		}
		
		/**
		 * @test
		 */
		public function saveFilesWithError() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_INI_SIZE;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
		  $type = null;
		  $up->error(function(UploadError $error) use (&$type) {
		    $type = $error->getType();
		  });
			$up->save();
			
			$this->assertEquals(UploadError::ERR_PHP_UPLOAD, $type);
		}
		
		/**
		 * @test
		 */
		public function saveFilesWithConstaintNotHolding() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood), array(
		    'size' => '< 1024'
		  ));
		  $type = null;
		  $up->error(function(UploadError $error) use (&$type) {
		    $type = $error->getType();
		  });
			$up->save();
			
			$this->assertEquals(UploadError::ERR_CONSTRAINT, $type);
		}
		
		/**
		 * @test
		 */
		public function uploadedFiles() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
			
			foreach ($up->getFiles() as $file) {
			  $file->setUploaded(true);
			}
			
			$this->assertTrue(in_array($up->getFile('foo'), $up->getUploadedFiles()));
		}
		
		/**
		 * @test
		 */
		public function aggregatedFileSize() {
		
		  $_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
		
		  $up = new FileUpload(vfsStream::url($this->dirGood));
			$this->assertEquals(1024, $up->getAggregatedFileSize());
		}
		
		/**
		 * @test
		 */
		public function humanReadableSize() {
			$this->assertEquals('1.02 K', $this->up->getHumanReadableSize(1025));
		}
		
		/**
		 * @test
		 */
		public function humanReadableSizeWithMax() {
			$this->assertEquals('1025.00 B', $this->up->getHumanReadableSize(1025, 'B'));
		}
	}

?>