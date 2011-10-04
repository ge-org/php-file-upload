# README

## What is it?

`FileUpload` is a PHP class library that offers an easy yet powerful way to handle file uploads.
It offers an intuitive way to set constrains to control which files are allowed and which not.

## Requirements

`FileUpload` requires PHP version 5.3 or higher.

## How-To

Here is a typical piece of code to utilize the library.
The example is also available as a [gist on github][1].

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
    
It should be pretty self explanatory. Here is what happens:
    
When creating a new instance you set the default upload directory as the first argument and an array of constraints as the second argument.

You then register a closure that will be called if any error occurrs while uploading and one closure that will be called if any constraint does not hold.

After these steps you start the upload process. You may pass the `save()` method another closure. This way you can manipulate properties of the currently processed file. For example you can set its new name.
If you want the file to be saved in a different directory than the default, then the closure must return the directory to use for the current file.

## Closures

## Constraints

Constraints are used to control that the uploaded files comply with rules that you want to enforce. For example limiting the file size or restricting the mime type to images.

`FileUpload` comes with two built-in constraints. `SizeConstraint` and `TypeConstraint`.
Like their name suggests they can control the file's size and the file's type.

As you have seen above constraints can easily be added when creating a `FileUpload` instance.
You just pass the constructor an associative array with the keys containing the constraint's alias and the value being the rules. You can also just pass instances of the constraints.

### SizeConstraint

The `SizeConstraint` goes by the alias `size`. It has several options to restrict the file size:

* < (less)
* = (equal)
* > (greater)
* <= (less equal)
* >= (greater equal)

So you could, for example, limit the file size to be between 1MB and 2MB like so:

    <?php
      use Faultier\FileUpload\FileUpload;
      
      $up = new FileUpload(__DIR__, array(
        'size' => '>= 1024',
        'size' => '<= 2048'
      ));
    ?>

### TypeConstraint

### Custom constraints

If you need additional constraints you can easily create your own ones.

All you have to do is implement the `Faultier\FileUpload\Constraint\ConstraintInterface` and register the namespace and alias of your constraint calling the `registerConstraintNamespace($namespace, $alias)` method on the `FileUpload` instance.

Here is an example:

    FooConstraint.php
    <?php
      namespace My\Namespace;
      use Faultier\FileUpload\Constraint\ConstraintInterface;
      
      class FooConstraint implements ConstraintInterface { ... }
    ?>

    upload.php
    <?php
      use Faultier\FileUpload\FileUpload;
      $up = new FileUpload(__DIR__);
      
      $up->registerConstraintNamespace('My\Namespace\FooConstraint', 'foo');
      $up->setConstraints(array(
        'foo' => 'some stuff'
      ));
    ?>

## Autoloader

The library adopts the [PSR-0][2] namespace convention.
This means you can use any autoloader that can handle the convention.
You can also use the autoloader that comes with the library:

    <?php
      require_once 'path/to/lib/Faultier/FileUpload/Autoloader.php';
      Faultier\FileUpload\Autoloader::register();
    ?>


## API

[1]: https://gist.github.com/1258900
[2]: https://gist.github.com/1234504