<?php
    
namespace Faultier\FileUpload\Constraint;

abstract class baseConstraint implements ConstraintInterface{

        /*
        **  @param errors -- file validation messages
        */
        protected $errors;

        /*
        **  @param reflector -- instance of ReflectionClass
        */
        protected $reflector;

        /*
        **  @param constants -- constants of current class given by ReflectionClass
        */
        protected $constants;

        /*
        **  @function addError --  add multiple validations errors
        **  @param $errors  --  array of errors
        */
        public function addError($errors){
            $this->errors[]=$errors;
        }

        /*
        **  @function getErrors --  gets all errors 
        **  @return all errors in array format
        */
        public function getErrors(){
            return $this->errors;
        }

        /*
        **  @function setReflector --  stores reflector in property,$this->reflector of child class
        **  @return property,$this->reflector
        */
        private function setReflector(){
            if(!isset($this->reflector)){
                $this->reflector=new \ReflectionClass(get_class($this));
            }
            return $this->reflector;
            
        }

        /*
        **  @function getConstants --  gets all constants in current class
        **  @return array of constants
        */
        private function getConstants(){
            $this->setReflector();
            if(!isset($this->constants)){
                $this->constants=$this->reflector->getConstants();
            }
            return $this->constants;
        }


        /*
        **  @function setMessage --  sets multiple file validation messages
        **  @return null
        */
        public function setMessage($key,$value){
            
            $constants=$this->getConstants();

            if(isset($constants[$key])){
                $this->messageTemplates[$constants[$key]]=$value;  
                              
            }

        }

        /*
        **  @function setMessages --  sets multiple custom file validation messages
        **  @return null
        */
        public function setMessages($messages){
            foreach($messages as $key=>$value){
                $this->setMessage($key,$value);
            }
        }

        /*
        **  @function parse --  receives user provided options for a constraint and does action according to the options
        **  @return null
        */
		public function parse($options) {
            $this->setOptions($options);
            
            if(isset($options['messages'])){
                $this->setMessages($options['messages']);
            }          
		}


        /*
        **  @function addErrorMessage   -- used to add error message from property, $this->messageTemplates
        */
        public function addErrorMessage($message_key){
            if(!isset($this->messageTemplates[$message_key])){
                throw new \Exception("Message Template does not Exists!");
            }else{
                $this->addError($this->messageTemplates[$message_key]);
            }
        }


        abstract protected  function setOptions($options);
    
}

