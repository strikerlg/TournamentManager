<?php
set_include_path("lib" . PATH_SEPARATOR . "src");

require_once "Kint/Kint.class.php";
require_once "Twig/Autoloader.php";
require_once "Repository/Database.php";

//$DEV_MODE = "release";
$DEV_MODE = "debug";
Kint::enabled(false);

if ($DEV_MODE === "debug")
{
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  Kint::enabled(true);
}

Twig_Autoloader::register();

$db = $GLOBALS["DB"] = new Database();
$db->connect();

echo "<p>Simple 'describe MatchInfo' Query:<br>";
d($db->query("describe MatchInfo"));

echo "<p>Select-Query mit Argumenten, sicher gegen SQL-Injection<br>";
d($db->query("select * from Admin where id = ? and username = ?", array(sqlInt(1), sqlString("bob"))));

// render page

$db->close();
