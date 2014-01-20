<?php

namespace Faultier\FileUpload;

class Autoloader
{
    public static function register($prepend = false)
    {
        return spl_autoload_register(array(new self, 'autoload'), true, $prepend);
    }

    public static function autoload($class)
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';

        if (is_file($file)) {
            require $file;

            return true;
        } else {
            return false;
        }
    }

}
