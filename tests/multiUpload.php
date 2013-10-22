<?php
    
require_once(__DIR__."/../src/autoload.php");

use Faultier\FileUpload\FileUpload;
use Faultier\FileUpload\UploadError;
use Faultier\FileUpload\File;

if(isset($_FILES) and !empty($_FILES)){
    $fileUploader = new FileUpload("uploads/");  
    $fileUploader->setAllowedFields(array("abcd"));
    $fileUploader->error(function(UploadError $error){
        foreach($error->getMessages() as $message){
            echo $message;
        }
    });
    $fileUploader->addSaveCallback(function (File $file){
        echo "Success"; 
    });
    $fileUploader->save();      
}else{
    ?>

        <form method="post" action="" enctype="multipart/form-data">
            <input type="file" name="abcd">
            <input type="submit" value="Upload">
        </form>

    <?php
}


?>
<?php
require_once(__DIR__."/../src/autoload.php");

use Faultier\FileUpload\FileUpload;
use Faultier\FileUpload\UploadError;
use Faultier\FileUpload\File;
?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="file" name="abcd[]" multiple>
    <input type="file" name="hello" multiple>
    <input type="submit" value="Upload">
</form>
<?php
if(isset($_FILES) and !empty($_FILES)){
    $fileUploader = new FileUpload("uploads/");  
    $fileUploader->setAllowedFields(array("abcd","hello"));
    $fileUploader->error(function(UploadError $error){
        foreach($error->getMessages() as $message){
            echo $message."<br/>";
        }
    });
    $fileUploader->addSaveCallback(function (File $file){
        echo "Success<br/>"; 
    });
    $fileUploader->save(); 
    try{
        $fileUploader->getFile("abcd");    
    }catch(\BadMethodCallException $e){
        echo $e->getMessage()."<br/>";
    }
    //print_r($fileUploader->getFiles());
    var_dump($fileUploader->isMultiFileUpload("abcd"));
    var_dump($fileUploader->isMultiFileUpload("hello"));
    
         
}

?>
