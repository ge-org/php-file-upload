<?php

	use Faultier\FileUpload\File;

	use Faultier\FileUpload\Constraint\ConstraintInterface;
	use Faultier\FileUpload\Constraint\TypeConstraint;

	class TypeConstraintTest extends \PHPUnit_Framework_TestCase {
	
		private $constraint;
		
		public function setUp() {
			$this->constraint = new TypeConstraint();
		}
	
		/**
		 * @test
		 */
		public function instanceIsCreated() {
			$this->assertInstanceOf('Faultier\FileUpload\Constraint\TypeConstraint', $this->constraint);
			$this->assertInstanceOf('Faultier\FileUpload\Constraint\ConstraintInterface', $this->constraint);
		}
		
		/**
		 * @test
		 */
		public function constraintType() {
			$this->assertEquals('type', $this->constraint->getConstraintType());
		}
		
		/**
		 * @test
		 */
		public function mode() {
			$this->constraint->setMode(TypeConstraint::EQUAL);
			$this->assertEquals(TypeConstraint::EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::NOT_EQUAL);
			$this->assertEquals(TypeConstraint::NOT_EQUAL, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::CONTAINS);
			$this->assertEquals(TypeConstraint::CONTAINS, $this->constraint->getMode());
			
			$this->constraint->setMode(TypeConstraint::CONTAINS_NOT);
			$this->assertEquals(TypeConstraint::CONTAINS_NOT, $this->constraint->getMode());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 */
		public function modeException() {
			$this->constraint->setMode('Georg');
		}
		
		/**
		 * @test
		 */
		public function types() {
			$types = array();
			$this->constraint->setTypes($types);
			$this->assertEquals($types, $this->constraint->getTypes());
		}
		
		/**
		 * @test
		 */
		public function parse() {
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
		
		/**
		 * @test
		 */
		public function parseFalseValues() {
			$this->constraint->parse('! abc,abc');
			$this->assertEquals(null, $this->constraint->getMode());
			$this->assertEquals(array(), $this->constraint->getTypes());
		}
		
		/**
		 * @test
		 */
		public function holds() {
			$file = new File();
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('= image/jpg');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('= image/jpeg');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setMimeType('image/jpeg');
			$this->constraint->parse('!= image/jpg');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('!= image/jpg');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('~ image');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('application/pdf');
			$this->constraint->parse('~ image');
			$this->assertFalse($this->constraint->holds($file));
			
			$file->setMimeType('application/pdf');
			$this->constraint->parse('!~ image');
			$this->assertTrue($this->constraint->holds($file));
			
			$file->setMimeType('image/jpg');
			$this->constraint->parse('!~ image');
			$this->assertFalse($this->constraint->holds($file));
		}
		
	}

?>