<?php

	use Faultier\FileUpload\File;

	use Faultier\FileUpload\Constraint\ConstraintInterface;
	use Faultier\FileUpload\Constraint\TypeConstraint;

	class TypeConstraintTest extends \PHPUnit_Framework_TestCase {
	
		private $constraint;
		
		public function setUp() {
			$this->constraint = new TypeConstraint();
		}
	
		public function testInstanceCreated() {
		$this->assertInstanceOf('Faultier\FileUpload\Constraint\TypeConstraint', $this->constraint);
			$this->assertInstanceOf('Faultier\FileUpload\Constraint\ConstraintInterface', $this->constraint);
		}
		
		public function testConstraintType() {
			$this->assertEquals('type', $this->constraint->getConstraintType());
		}
		
		public function testMode() {
			$this->constraint->setMode(TypeConstraint::EQUAL);
			$this->assertEquals(TypeConstraint::EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::NOT_EQUAL);
			$this->assertEquals(TypeConstraint::NOT_EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::CONTAINS);
			$this->assertEquals(TypeConstraint::CONTAINS, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::CONTAINS_NOT);
			$this->assertEquals(TypeConstraint::CONTAINS_NOT, $this->constraint->getMode());
		}
		
		public function testModeException() {
			$this->setExpectedException('\InvalidArgumentException');
			$this->constraint->setMode('Georg');
		}
		
		public function testTypes() {
			$types = array();
			$this->constraint->setTypes($types);
			$this->assertEquals($types, $this->constraint->getTypes());
		}
		
		public function testParse() {
			$this->constraint->parse('= xyz xyz');
			$this->assertEquals(TypeConstraint::EQUAL, $this->constraint->getMode());
			$this->assertEquals(array('xyz', 'xyz'), $this->constraint->getTypes());
			
			$this->constraint->parse('!= xyz xyz');
			$this->assertEquals(TypeConstraint::NOT_EQUAL, $this->constraint->getMode());
			$this->assertEquals(array('xyz', 'xyz'), $this->constraint->getTypes());
			
			$this->constraint->parse('~ xyz xyz');
			$this->assertEquals(TypeConstraint::CONTAINS, $this->constraint->getMode());
			$this->assertEquals(array('xyz', 'xyz'), $this->constraint->getTypes());
			
			$this->constraint->parse('!~ xyz xyz');
			$this->assertEquals(TypeConstraint::CONTAINS_NOT, $this->constraint->getMode());
			$this->assertEquals(array('xyz', 'xyz'), $this->constraint->getTypes());
		}
		
		public function testParseFalseValues() {
			$this->constraint->parse('! abc,abc');
			$this->assertEquals(null, $this->constraint->getMode());
			$this->assertEquals(array(), $this->constraint->getTypes());
		}
		
		public function testHolds() {
			$file = new File();
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('= image/jpg');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('!= image/jpg');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('~ image');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('!~ image');
			$this->assertFalse($this->constraint->holds($file));
		}
		
	}

?>