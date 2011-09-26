<?php

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
		
	}

?>