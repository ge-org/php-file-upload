<?php

require_once(__DIR__."/../src/autoload.php");

use Faultier\FileUpload\FileUpload;
use Faultier\FileUpload\UploadError;
use Faultier\FileUpload\File;

if (isset($_FILES) and !empty($_FILES)) {
    $fileUploader = new FileUpload("uploads/");
    $fileUploader->setAllowedFields(array("abcd"));
    $fileUploader->error(function (UploadError $error) {
        foreach ($error->getMessages() as $message) {
            echo $message;
        }
    });
    $fileUploader->addSaveCallback(function (File $file) {
        echo "Success";
    });
    $fileUploader->save();
} else {
    ?>

        <form method="post" action="" enctype="multipart/form-data">
            <input type="file" name="abcd">
            <input type="submit" value="Upload">
        </form>

    <?php
}
