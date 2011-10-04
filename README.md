# README

## What is it?

`FileUpload` is ...

## How to use it?

This code is also available as a [gist on github][1].

    <?php

      use Faultier\FileUpload\FileUpload;

      $fileUploader = new FileUpload(__DIR__, array(
        'size' => '<= 2M',
        'type' => '~ image',
        'type' => '!~ jpg tiff'
      ));

      $fileUploader->error(function($type, $message, $file) {
        # omg!
      });

      $fileUploader->errorConstraint(function($constraint, $file) {
        # do something
      });

      $fileUploader->save(function($file) {
        $file->setName('Hello-World');
        return 'some/other/dir';
      });

    ?>

### Closures

### Autoloader

## Constraints

## API

[1]: https://gist.github.com/1258900