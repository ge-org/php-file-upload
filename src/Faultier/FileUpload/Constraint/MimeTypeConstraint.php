<?php

namespace Faultier\FileUpload\Constraint;

use \Faultier\FileUpload\File;

class MimeTypeConstraint  extends baseConstraint
{
        protected $types=array();

        const invalidFileType="invalidFileType";

        protected $messageTemplates=array(
            self::invalidFileType=>"Invalid File Type",
        );

        public function setMimeTypes(array $types=array())
        {
            $this->types = $types;
        }

        public function getMimeTypes()
        {
            return $this->types;
        }

        public function setOptions($options)
        {
            if (isset($options['value'])) {
                $this->setMimeTypes($options['value']);
            }
        }

        public function getConstraintType()
        {
            return 'Mimetype';
        }

        public function isValid(File $file)
        {
            if (in_array($file->getMimeType(),$this->getMimeTypes())) {
                return TRUE;
            }
            $this->addErrorMessage(self::invalidFileType);

            return FALSE;
        }
}
