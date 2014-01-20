<?php

namespace Faultier\FileUpload\Constraint;

use \Faultier\FileUpload\File;

class ImageConstraint extends baseConstraint
{
    const fileIsNotImage="fileIsNotImage";

    const fileIsImage="fileIsImage";

    const validationLevelSimple="simple";
    const validationLevelAdvanced="advanced";

    /*
    **  @param imageExtensions  -- extensions of images for simple image validation
    */
    protected $imageExtensions=array("jpeg","jpg","png","gif");

    /*
    **  @param isImage  -- used to check image if set to true
    */
    protected $isImage=TRUE;
    /*
    **  @param $validator
    **  instance of class, TypeConstraint or MimeType,Constraint;
    */
    protected $validator;

    protected $messageTemplates=array(
        self::fileIsNotImage=>"The uploaded file is not a valid image!",
        self::fileIsImage=>"The uploaded file is a image!",
    );

    protected $validationLevel=self::validationLevelSimple;

    /*
    **  @param imageMimeTypes  -- mime types of all types of images for advanced and simple images
    */
    protected $imageMimeTypes = array(
        self::validationLevelAdvanced=>array(
            IMAGETYPE_GIF,
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_SWF,
            IMAGETYPE_PSD,
            IMAGETYPE_BMP,
            IMAGETYPE_TIFF_II ,
            IMAGETYPE_TIFF_MM,
            IMAGETYPE_JP2,
            IMAGETYPE_JPX,
            IMAGETYPE_JB2,
            IMAGETYPE_SWC,
            IMAGETYPE_IFF,
            IMAGETYPE_WBMP,
            IMAGETYPE_XBM,
            IMAGETYPE_ICO
        ),self::validationLevelSimple=>array(
            IMAGETYPE_GIF,
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
        )

    );

    /*
    **  @function getImageExtensions    --  returns property,$this->imageExtensions
    */
    public function getImageExtensions()
    {
        return $this->imageExtensions();
    }

    /*
    **  @function addImageExtension    --  adds one image extension
    */
    public function addImageExtension($extension)
    {
        $this->imageExtensions[]=$extension;
    }

    /*
    **  @function addImageExtensions    --  adds one or more image extension
    */
    public function addImageExtensions($extensions)
    {
        foreach ($extensions as $extension) {
            $this->addImageExtension($extension);
        }
    }

    /*
    **  @function setOptions used to set Options
    **  @param $options --  Options
    */
    public function setOptions($options)
    {
        if (isset($options['value'])) {
            $this->isImage($options['value']);
        }

        if (isset($options['validation-level'])) {
            $this->setValidationLevel($options['validation-level']);
        }

    }

    /*
    **  @function setValidationLevel    -- used to set simple or advanced level validation
    */
    public function setValidationLevel($level)
    {
        $this->validationLevel = strtolower($level);
    }

    /*
    **  @function isImage    --  sets if user wants image or just opposite(everything except image)
    */
    public function isImage($isImage)
    {
        $this->isImage=(bool) $isImage;
    }

    /*
    **  @function isValid    --  checks if uploaded file is valid
    **  @param $file -- instance of class,\Faultier\FileUpload\File
    **  returns true if
    **                  1)user wants image and uploaded file is image
    **                  2)user wants just opposite(everything except image) and uploaded file is not image
    */
    public function isValid(File $file)
    {
        $file_type=\exif_imagetype($file->getTemporaryName());
        if ($this->isImage) {
            if (!in_array($file_type,$this->imageMimeTypes[$this->validationLevel])) {
                 $this->addErrorMessage(self::fileIsNotImage);

                return FALSE;
            }

            return TRUE;
        } else {
            if (in_array($file_type,$this->imageMimeTypes[$this->validationLevel])) {
                 $this->addErrorMessage(self::fileIsImage);

                return FALSE;
            }

            return TRUE;
        }

    }

}
