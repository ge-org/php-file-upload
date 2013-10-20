php-file-upload
=========

100% Object Oriented PHP File Upload Library

#Why use it:
<ul>
  <li>Object Oriented and Easy-to-Use</li>
  <li>Very PowerFull File Validation</li>
  <li>Customizable File Validation</li>
  <li>Event handing(Error,Save etc.) with Closures</li>  
</ul>

#Requirements
<ul>
  <li>PHP version 5.3.3 or later</li>
</ul>


#Usage

### Initializing Upload

```php
use Faultier\FileUpload\FileUpload;
use Faultier\FileUpload\UploadError;
use Faultier\FileUpload\File;

$fileUploader = new FileUpload("path/to/upload/directory");
```


### Setting Upload Fields
```php
//For example, you form is like this :: <input type='file' name='abcd'>
$fileUploader->setAllowedFields(array("abcd"));
```
If you donot set upload fields, it will set all the fields found in array `$_FILES`.


### Adding Constraint
Constraint programming is an emergent software technology for declarative description and effective solving of large, particularly combinatorial, problems especially in areas of planning and scheduling.
I have done file upload validation in form of Constraints, adding constraint for each one!

#### File Validations
```php
$fileUploader->addConstraints(array(
            "type"=>array(
                "value"=>array('xlsx','csv')
            ),"size"=>array(
                "value"=>'<= 2048'
            ) 
    )
);
```
This is simple example of how to validate a uploaded file based on its type (extension) and its size!

##### Validation by Size

```php
$fileUploader->addConstraints(array(
            "size"=>array(
                "value"=>'<= 2048'
            )   
    )
);
```
####### Options

* < (less)
* = (equal)
* > (greater)
* <= (less equal)
* >= (greater equal)



##### Validation by Mime Type

```php
$fileUploader->addConstraints(array(
            "mime-type"=>array(
                "value"=>array("application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"),
            ) 
    )              
);
```
##### Validation by File Type

```php
$fileUploader->addConstraints(array(
            "type"=>array(
                "value"=>array('xlsx','csv')
            )
    )               
);
```

##### Validation by Image

To allow only image to be uploaded:
```php
$fileUploader->addConstraints(array(
            "image"=>array(
                "value"=>true,
            )
    )               
);
```
To allow all files types except image, set value to false
```php
$fileUploader->addConstraints(
            "image"=>array(
                "value"=>false,
            ) 
    )              
);
```

### Handling Errors
Error handing is done by passing a closure to `error` method.
For example,
```php
$fileUploader->error(function(UploadError $error) {
    $messages =  $error->getMessages(); 
    foreach($messages as $message){
        echo $message;
    }
   
});
```
The `error()` method accepts a closure that will be called if any error occurrs while uploading the file. It will be passed the an UploadError object that contains the type of error, the error message, the affected file!

### Finally Saving Files
```php
$fileUploader->save(function(File $file) {
    return 'some/other/dir';
});
```
The `save()` method accepts a closure that will be called for each file before it will be uploaded. It will be passed the `File` object that will be uploaded. If this method returns a string it will be used as the directory to which the file will be uploaded.
While, saving uploaded files, the class calls added constraint to see if uploaded file is valid!
If false, `error()` closure is called!


### Adding Save Callback  
You can add multiple calbacks to be executed just after a file is uploaded.
For example you can crop image for thumbnail!
```php
$fileUploader->addSaveCallback(function(File $file){
    $ImageCropper=new ImageCropper($file->getFilePath());
    $ImageCropper->resizeAndCropFromCenter(PREVIEW_WIDTH,PREVIEW_HEIGHT);
    $ImageCropper->save();            
});
```
I have used ImageCropper in the example(just for example. You have to use your own because it does not come with ImageCropper).

Note: `addSaveCallback()` must be called before `save()` is called!

### Setting Custom Messsages
To set Custom message, you need to pass message along with value while calling `addConstraints`.
For example,
```php
$fileUploader->addConstraints(array(
            "size"=>array(
                "value"=>'<= 2048',
                "message"=>array(
                    "fileIsLarger"=>'The file you uploaded is very large in size. Try uploading a small sized file!',
                )
            ),"image"=>array( 
                "value"=>true,
                "message"=>array(
                    "fileIsNotImage"=>'The file you uploaded is not a valid image! '
                )
            ),   
    )
);

$fileUploader->error(function(UploadError $error) {
    $messages =  $error->getMessages(); 
    foreach($messages as $message){
        //handle error messages here
    }
   
});

```


### Custom Constraints

If you need additional constraints you can easily create your own ones.

All you have to do is implement the `Faultier\FileUpload\Constraint\ConstraintInterface` or extend abstract class,`Faultier\FileUpload\Constraint\baseConstraint`  and register the namespace and alias of your constraint calling the `registerConstraintNamespace($alias, $namespace)` method on the `FileUpload` instance.

Here is an example:
```php
FooConstraint.php

<?php
  namespace My\Namespace;

  use Faultier\FileUpload\Constraint\ConstraintInterface;

  class FooConstraint implements ConstraintInterface { ... }
  // or you can do class 

  use Faultier\FileUpload\Constraint\baseConstraint;

  class FooConstraint extends baseConstraint { ... }
?>
```

```php
upload.php
<?php
$up = new FileUpload("path/to/upload/directory");
$up->registerConstraintNamespace('foo','My\Namespace\FooConstraint');
$up->setConstraints(array(
    'foo' => //foo options here. This part will be passed to FooConstraint
));
?>
```
You can find more details on this <a href='https://github.com/ojhaujjwal/php-file-upload/wiki/Creating-Custom-Constraints'>page</a> on creating custom constraints!


### Autoloader
The library adopts the <a href='https://gist.github.com/1234504'>PSR-0</a> namespace convention. This means you can use any autoloader that can handle the convention. You can also use the autoloader that comes with the library:
```php
<?php
require_once 'path/to/lib/Faultier/FileUpload/Autoloader.php';
Faultier\FileUpload\Autoloader::register();
?>
```
