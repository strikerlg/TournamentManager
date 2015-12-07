<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
set_include_path("lib" . PATH_SEPARATOR . "src");

require_once "Twig/Autoloader.php";
require_once "Repository/Database.php";

Twig_Autoloader::register();

$db = $GLOBALS["DB"] = new Database();
$db->connect();

// render page

$db->close();
