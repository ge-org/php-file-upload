<?php

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
		
	}

?>