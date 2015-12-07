<?php
set_include_path("../lib" . PATH_SEPARATOR . "./");
require_once "Twig/Autoloader.php";
Twig_Autoloader::register();

$db = $GLOBALS["DB"] = new Database();
$db->connect();