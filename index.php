<?php

set_include_path("lib" . PATH_SEPARATOR . "src");

require_once "Kint/Kint.class.php";

//define("DEV_MODE", "release");
define("DEV_MODE", "debug");
Kint::enabled(false);

if (DEV_MODE === "debug")
{
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  Kint::enabled(true);
}

require_once "TManager.php";

$tManager = new TManager();
if (isset($_POST["action"]))
{
  d($_POST);
}
else
{
  $page = isset($_GET["page"]) ? $_GET["page"] : "live";
  $tManager->handlePage($page);
}