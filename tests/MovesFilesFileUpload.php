<?php

  class MovesFilesFileUpload extends Faultier\FileUpload\FileUpload {
  
    protected function moveUploadedFile($name, $directory) {
      return true;
    }
  
  }

?>