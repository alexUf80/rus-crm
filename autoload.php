<?php

function core_autoload($classname)
{
    if (file_exists(dirname(__FILE__).'/core/'.$classname.'.php'))
        require dirname(__FILE__).'/core/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/models/'.$classname.'.php'))
        require dirname(__FILE__).'/models/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/controllers/'.$classname.'.php'))
        require dirname(__FILE__).'/controllers/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/scorings/'.$classname.'.php'))
        require dirname(__FILE__).'/scorings/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/api/'.$classname.'.php'))
        require dirname(__FILE__).'/api/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/tools/'.$classname.'.php'))
        require dirname(__FILE__).'/tools/'.$classname.'.php';
    if (file_exists(dirname(__FILE__).'/vendor/autoload.php'))
        require dirname(__FILE__).'/vendor/autoload.php';
}
spl_autoload_register('core_autoload');
