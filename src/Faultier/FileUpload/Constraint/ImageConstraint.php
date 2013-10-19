<?php

namespace Faultier\FileUpload\Constraint;
	
use \Faultier\FileUpload\File;

class ImageConstraint extends baseConstraint{

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
    **  @param imageMimeTypes  -- mime types of all types of images for advanced images
    */
    protected $imageMimeTypes = array(
        'application/cdf',
        'application/dicom',
        'application/fractals',
        'application/postscript',
        'application/vnd.hp-hpgl',
        'application/vnd.oasis.opendocument.graphics',
        'application/x-cdf',
        'application/x-cmu-raster',
        'application/x-ima',
        'application/x-inventor',
        'application/x-koan',
        'application/x-portable-anymap',
        'application/x-world-x-3dmf',
        'image/bmp',
        'image/c',
        'image/cgm',
        'image/fif',
        'image/gif',
        'image/jpeg',
        'image/jpm',
        'image/jpx',
        'image/jp2',
        'image/naplps',
        'image/pjpeg',
        'image/png',
        'image/svg',
        'image/svg+xml',
        'image/tiff',
        'image/vnd.adobe.photoshop',
        'image/vnd.djvu',
        'image/vnd.fpx',
        'image/vnd.net-fpx',
        'image/x-cmu-raster',
        'image/x-cmx',
        'image/x-coreldraw',
        'image/x-cpi',
        'image/x-emf',
        'image/x-ico',
        'image/x-icon',
        'image/x-jg',
        'image/x-ms-bmp',
        'image/x-niff',
        'image/x-pict',
        'image/x-pcx',
        'image/x-png',
        'image/x-portable-anymap',
        'image/x-portable-bitmap',
        'image/x-portable-greymap',
        'image/x-portable-pixmap',
        'image/x-quicktime',
        'image/x-rgb',
        'image/x-tiff',
        'image/x-unknown',
        'image/x-windows-bmp',
        'image/x-xpmi',
    );


    public function __construct(){
        
     
    }
    
    /*
    **  @function getImageExtensions    --  returns property,$this->imageExtensions
    */
    public function getImageExtensions(){
        return $this->imageExtensions();
    } 
    

    /*
    **  @function addImageExtension    --  adds one image extension
    */
    public function addImageExtension($extension){
        $this->imageExtensions[]=$extension;
    }


    /*
    **  @function addImageExtensions    --  adds one or more image extension
    */
    public function addImageExtensions($extensions){
        foreach($extensions as $extension){
            $this->addImageExtension($extension);
        }    
    }
    
    public function setOptions($options){
        if(isset($options['value'])){
            $this->isImage($options['value']);
        }

        if(isset($options['validation-level'])){
            $this->setValidationLevel($options['validation-level']);
        }
        
    }

    public function setValidationLevel($level){
        $this->validationLevel=strtolower($level);
    }

    /*
    **  @function isImage    --  sets if user wants image or just opposite(everything except image)
    */    
    public function isImage($isImage){
        $this->isImage=(bool) $isImage;
    } 

    /*
    **  @function isValid    --  checks if uploaded file is valid
    **  @param $file -- instance of class,\Faultier\FileUpload\File
    **  returns true if
    **                  1)user wants image and uploaded file is image
    **                  2)user wants just opposite(everything except image) and uploaded file is not image
    */    
    public function isValid(File $file){
        if($this->validationLevel==self::validationLevelSimple){
            $this->validator=new TypeConstraint();
            $this->validator->setFileTypes($this->imageExtensions);            
        }else{

            $this->validator=new MimeTypeConstraint();
            $this->validator->setMimeTypes($this->imageMimeTypes);            
        }

        if($this->isImage){
            if(!$this->validator->isValid($file)){
                $this->addError($this->messageTemplates['fileIsNotImage']);
                return FALSE;
            }
            return TRUE;
        }else{
            if($this->validator->isValid($file)){
                $this->addError($this->messageTemplates['fileIsImage']);
                return FALSE;
            }
            return TRUE;            
        }

    }
    
    public function getConstraintType(){
        return "Image";
    }
         
}
