<?php session_start(); ?>
<?php
error_reporting(E_ALL);
spl_autoload_register('classautoloader');
function classautoloader($className){
    $path = 'model\\';
    include_once $path.$className.'.php';
}
include_once("config.php");
include_once("controller/maincontroller.controller.php");
$mc = new MainController();
$mc->route();
?>
