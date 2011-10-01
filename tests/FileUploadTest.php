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
		protected $up;
		
		public function setUp() {
			vfsStream::setup($this->dir);
			$this->up = new FileUpload(vfsStream::url($this->dir));
		}
		
		public function testInstanceCreated() {
			$this->assertInstanceOf('Faultier\FileUpload\FileUpload', $this->up);
		}
		
		public function testUploadDirectoryIsValid() {
			$this->up->setUploadDirectory(vfsStream::url($this->dir));
			$this->assertEquals(vfsStream::url($this->dir), $this->up->getUploadDirectory());
		}
		
		public function testUploadDirectoryDoesNotExist() {
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory does not exist');
			$this->up->setUploadDirectory(null);
		}
		
		/*
		public function testUploadDirectoryIsFile() {
			
			vfsStream::inspect(new vfsStreamPrintVisitor);
			
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory is not a directory');
			$this->up->setUploadDirectory(vfsStream::url($this->dir.'/foo.txt'));
		}
		*/
		
		/*
		public function testConstructorWithNotWritableDirectory() {
			$dir = vfsStream::newDirectory('notWritable', 0);
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory is not writable');
			$up = new FileUpload(vfsStream::url($this->dir.'/notWritable'));
		}
		*/
		
		public function testUploadDirectory() {
			$this->up->setUploadDirectory(vfsStream::url($this->dir));
			$this->assertEquals(vfsStream::url($this->dir), $this->up->getUploadDirectory());
		}
		
		public function testFilesEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(array(), $up->getFiles());
			$this->assertFalse($up->hasFiles());
			$this->setExpectedException('\InvalidArgumentException');
			$up->getFile('');
		}
		
		public function testConstraintsEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir), array());
			$this->assertEquals(array(), $up->getConstraints());
			$this->assertFalse($up->hasConstraints());
		}
		
		public function testUploadedFilesEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(array(), $up->getUploadedFiles());
			$this->assertEquals(array(), $up->getNotUploadedFiles());
		}
		
		public function testAggregatedFileSizeEmpty() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertEquals(0, $up->getAggregatedFileSize());
			$this->assertEquals('0 B', $up->getReadableAggregatedFileSize());
		}
		
		public function testRemoveConstraints() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->removeConstraints();
			$this->assertFalse($up->hasConstraints());
		}
		
		public function testHumanReadableSize() {
			$this->assertEquals('1 KB', $this->up->getHumanReadableSize(1025));
		}
		
		public function testConstructorWithValidConstraints() {
		
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
		
		public function testConstructorWithInvalidConstraints() {
		
			$constraints = array(
				'foo' => '< 1024'
			);
		
			$this->setExpectedException('\InvalidArgumentException');
			$up = new FileUpload(vfsStream::url($this->dir), $constraints);
		}
		
		public function testErrorClosure() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->error(function($t, $m, Faultier\FileUpload\File $f) {});
		}
		
		public function testConstraintClosure() {
			$up = new FileUpload(vfsStream::url($this->dir));
			$up->errorConstraint(function(Faultier\FileUpload\ConstraintInterface $c, Faultier\FileUpload\File $f){});
		}
		
		public function testFilesArray() {
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
		
		public function testMultiFilesArray() {
			$_FILES['foo']['name'] = array('name-bar');
			$_FILES['foo']['tmp_name'] = array('tmp_name-bar');
			$_FILES['foo']['type'] = array('type-bar');
			$_FILES['foo']['size'] = array(1024);
			$_FILES['foo']['error'] = array(UPLOAD_ERR_OK);
			
			$up = new FileUpload(vfsStream::url($this->dir));
			$this->assertTrue($up->hasFiles());
			$this->assertTrue($up->isMultiFileUpload());
			
			$this->setExpectedException('\BadMethodCallException');
			$up->getFile('foo');
		}
		
		public function testSave() {
			$this->up->error(function($t, $m, $f){});
			$this->up->save();
			$this->up->save(function(Faultier\FileUpload\File $f){});
		}
		
	}

?>