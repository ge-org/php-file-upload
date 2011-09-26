<?php

	use Faultier\FileUpload\File;

	use Faultier\FileUpload\Constraint\ConstraintInterface;
	use Faultier\FileUpload\Constraint\SizeConstraint;

	class SizeConstraintTest extends \PHPUnit_Framework_TestCase {
	
		private $constraint;
		
		public function setUp() {
			$this->constraint = new SizeConstraint();
		}
	
		public function testInstanceCreated() {
			$this->assertInstanceOf('Faultier\FileUpload\Constraint\SizeConstraint', $this->constraint);
			$this->assertInstanceOf('Faultier\FileUpload\Constraint\ConstraintInterface', $this->constraint);
		}
		
		public function testConstraintType() {
			$this->assertEquals('size', $this->constraint->getConstraintType());
		}
		
		public function testSize() {
			$this->constraint->setSize(1234);
			$this->assertEquals(1234, $this->constraint->getSize());
		}
		
		public function testSizeException() {
			$this->setExpectedException('\InvalidArgumentException');
			$this->constraint->setSize('Georg');
		}
		
		public function testMode() {
			$this->constraint->setMode(SizeConstraint::LESS);
			$this->assertEquals(SizeConstraint::LESS, $this->constraint->getMode());
			
			$this->constraint->setMode(SizeConstraint::EQUAL);
			$this->assertEquals(SizeConstraint::EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(SizeConstraint::GREATER);
			$this->assertEquals(SizeConstraint::GREATER, $this->constraint->getMode());
			
			$this->constraint->setMode(SizeConstraint::LESS_EQUAL);
			$this->assertEquals(SizeConstraint::LESS_EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(SizeConstraint::GREATER_EQUAL);
			$this->assertEquals(SizeConstraint::GREATER_EQUAL, $this->constraint->getMode());
		}
		
		public function testModeException() {
			$this->setExpectedException('\InvalidArgumentException');
			$this->constraint->setMode('Georg');
		}
		
		public function testParse() {
			$this->constraint->parse('< 1234');
			$this->assertEquals(1234, $this->constraint->getSize());
			$this->assertEquals(SizeConstraint::LESS, $this->constraint->getMode());
			
			$this->constraint->parse('= 1234');
			$this->assertEquals(1234, $this->constraint->getSize());
			$this->assertEquals(SizeConstraint::EQUAL, $this->constraint->getMode());
			
			$this->constraint->parse('> 1234');
			$this->assertEquals(1234, $this->constraint->getSize());
			$this->assertEquals(SizeConstraint::GREATER, $this->constraint->getMode());
			
			$this->constraint->parse('<= 1234');
			$this->assertEquals(1234, $this->constraint->getSize());
			$this->assertEquals(SizeConstraint::LESS_EQUAL, $this->constraint->getMode());
			
			$this->constraint->parse('>= 1234');
			$this->assertEquals(1234, $this->constraint->getSize());
			$this->assertEquals(SizeConstraint::GREATER_EQUAL, $this->constraint->getMode());
		}
		
		public function testParseFalseValues() {
			$this->constraint->parse('! abc');
			$this->assertEquals('', $this->constraint->getSize());
			$this->assertEquals(null, $this->constraint->getMode());
		}
		
		public function testHolds() {
			$file = new File();
			
			$file->setSize(1234);
			$this->constraint->parse('= 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(12345);
			$this->constraint->parse('= 1234');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setSize(123);
			$this->constraint->parse('< 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(1234);
			$this->constraint->parse('< 1234');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setSize(12345);
			$this->constraint->parse('> 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(1234);
			$this->constraint->parse('> 1234');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setSize(1234);
			$this->constraint->parse('<= 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(123);
			$this->constraint->parse('<= 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(12345);
			$this->constraint->parse('<= 1234');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setSize(1234);
			$this->constraint->parse('>= 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(12345);
			$this->constraint->parse('>= 1234');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setSize(123);
			$this->constraint->parse('>= 1234');
			$this->assertFalse($this->constraint->holds($file));
		}
		
	}

?>