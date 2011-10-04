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

	class FileUploadTest extends \PHPUnit_Framework_TestCase {
	
		protected $dir = 'exampleDir';
		protected $file = 'foo.txt';
	
		protected $up;
		
		public function setUp() {
			vfsStream::create(array(
				$this->dir => array(
					$this->file => 'foo'
				),
				'notWritable' => array()
			));
			
			$this->up = new FileUpload(vfsStream::url($this->dir));
		}
		
		/**
		 * @test
		 */
		public function instanceIsCreated() {
			$this->assertInstanceOf('Faultier\FileUpload\FileUpload', $this->up);
		}
		
		/**
		 * @test
		 */
		public function uploadDirectoryIsValid() {
			$this->up->setUploadDirectory(vfsStream::url($this->dir));
			$this->assertEquals(vfsStream::url($this->dir), $this->up->getUploadDirectory());
		}
		
		/**
		 * @test
		 * @expectedException					\InvalidArgumentException
		 * @expectedExceptionMessage	The given upload directory does not exist
		 */
		public function uploadDirectoryDoesNotExist() {
			$this->up->setUploadDirectory(null);
		}
		
		/**
		 * @test
		 * @expectedException					\InvalidArgumentException
		 * @expectedExceptionMessage	The given upload directory is not a directory
		 */
		public function uploadDirectoryIsFile() {
			$this->up->setUploadDirectory(vfsStream::url($this->dir.DIRECTORY_SEPARATOR.$this->file));
		}
		
		/**
		 * @test
		 * @expectedException					\InvalidArgumentException
		 * @expectedExceptionMessage	The given upload directory is not writable
		 */
		public function constructorWithNotWritableDirectory() {
			vfsStreamWrapper::getRoot()->getChild('notWritable')->chmod(0000);
			$up = new FileUpload(vfsStream::url('notWritable'));
		}
		
		/**
		 * @test
		 */
		public function uploadDirectory() {
			$this->up->setUploadDirectory(vfsStream::url($this->dir));
			$this->assertEquals(vfsStream::url($this->dir), $this->up->getUploadDirectory());
		}
		
		/**
		 * @test
		 * @expectedException	\InvalidArgumentException
		 */
		public function filesEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(array(), $up->getFiles());
			$this->assertFalse($up->hasFiles());
			$up->getFile('');
		}
		
		/**
		 * @test
		 */
		public function constraintsEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir), array());
			$this->assertEquals(array(), $up->getConstraints());
			$this->assertFalse($up->hasConstraints());
		}
		
		/**
		 * @test
		 */
		public function uploadedFilesEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(array(), $up->getUploadedFiles());
			$this->assertEquals(array(), $up->getNotUploadedFiles());
		}
		
		/**
		 * @test
		 */
		public function aggregatedFileSizeEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(0, $up->getAggregatedFileSize());
			$this->assertEquals('0 B', $up->getReadableAggregatedFileSize());
		}
		
		/**
		 * @test
		 */
		public function removeConstraints() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->removeConstraints();
			$this->assertFalse($up->hasConstraints());
		}
		
		/**
		 * @test
		 */
		public function humanReadableSize() {
			$this->assertEquals('1 KB', $this->up->getHumanReadableSize(1025));
		}
		
		/**
		 * @test
		 */
		public function constructorWithValidConstraints() {
		
			$constraint = new Faultier\FileUpload\Constraint\SizeConstraint();
			$constraint->parse('> 500');
		
			$constraints = array(
				'size' => '< 1024',
				'type' => '~ image',
				'type' => '!~ tiff',
				$constraint
			);
		
			$up = new FileUpload(vfsStream::url($this->dir), $constraints);
			$this->assertTrue($up->hasConstraints());
		}
		
		/**
		 * @test
		 * @expectedException	\InvalidArgumentException
		 */
		public function constructorWithInvalidConstraints() {
		
			$constraints = array(
				'foo' => '< 1024'
			);
		
			$up = new FileUpload(vfsStream::url($this->dir), $constraints);
		}
		
		/**
		 * @test
		 */
		public function errorClosure() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->error(function($t, $m, Faultier\FileUpload\File $f) {});
		}
		
		/**
		 * @test
		 */
		public function constraintClosure() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->errorConstraint(function(Faultier\FileUpload\ConstraintInterface $c, Faultier\FileUpload\File $f){});
		}
		
		/**
		 * @test
		 */
		public function filesArray() {
			$_FILES['foo']['name'] = 'name-bar';
			$_FILES['foo']['tmp_name'] = 'tmp_name-bar';
			$_FILES['foo']['type'] = 'type-bar';
			$_FILES['foo']['size'] = 1024;
			$_FILES['foo']['error'] = UPLOAD_ERR_OK;
			
			$this->up = new FileUpload(vfsStream::url($this->dir));
			$this->assertTrue($this->up->hasFiles());
			$this->assertInstanceOf('Faultier\FileUpload\File', $this->up->getFile('foo'));
			
			$this->assertEquals(1024, $this->up->getAggregatedFileSize());
		}
		
		/**
		 * @test
		 * @expectedException \BadMethodCallException
		 */
		public function multiFilesArray() {
			$_FILES['foo']['name'] = array('name-bar');
			$_FILES['foo']['tmp_name'] = array('tmp_name-bar');
			$_FILES['foo']['type'] = array('type-bar');
			$_FILES['foo']['size'] = array(1024);
			$_FILES['foo']['error'] = array(UPLOAD_ERR_OK);
			
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertTrue($up->hasFiles());
			$this->assertTrue($up->isMultiFileUpload());
			
			$up->getFile('foo');
		}
		
		/**
		 * @test
		 */
		public function save() {
			$this->up->error(function($t, $m, $f){});
			$this->up->save();
			$this->up->save(function(Faultier\FileUpload\File $f){});
		}
		
		/**
		 * @test
		 */
		public function constraintNamespaces() {
		  $constraints = array(
		    'type' => 'Faultier\FileUpload\Constraint\TypeConstraint',
		    'size' => 'Faultier\FileUpload\Constraint\SizeConstraint'
		  );
		  
		  $this->assertEquals($constraints, $this->up->getConstraintNamespaces());
		  
		  $this->assertEquals('Faultier\FileUpload\Constraint\SizeConstraint', $this->up->resolveConstraintAlias('size'));
		  $this->assertNull($this->up->resolveConstraintAlias('foo'));
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The constraint class "foo" does not exist
		 */
		public function constraintNamespaceDoesNotExist() {
		  $this->up->registerConstraintNamespace('foo', 'bar');
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 * @expectedExceptionMessage The class "Faultier\FileUpload\FileUpload" must implement "Faultier\FileUpload\Constraint\ConstraintInterface"
		 */
		public function constraintDoesNotImplementInterface() {
		  $this->up->registerConstraintNamespace('Faultier\FileUpload\FileUpload', 'bar');
		}
	}

?>