<?php
error_reporting(-1);
ini_set('display_errors', 'Off');
ini_set('display_startup_errors', 'Off');

session_start();

require 'autoload.php';
require 'vendor/autoload.php';


try {
    $view = new IndexController();
    if (($res = $view->fetch()) !== false) {
        if ($res == 403) {
            header("http/1.0 403 Forbidden");
            $_GET['page_url'] = '403';
            $_GET['module'] = 'ErrorController';
            print $view->fetch();
        } else {
            // Выводим результат
            header("Content-type: text/html; charset=UTF-8");
            print $res;

        }
    } else {
        // Иначе страница об ошибке
        header("http/1.0 404 not found");

        // Подменим переменную GET, чтобы вывести страницу 404
        $_GET['page_url'] = '404';
        $_GET['module'] = 'ErrorController';
        print $view->fetch();
    }
} catch (Exception $e) {
    echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
    var_dump($e);
    echo '</pre><hr />';
}
