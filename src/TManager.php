<?php

require_once "Twig/Autoloader.php";
require_once "Repository/Database.php";

/**
 * Class TManager - Hauptklasse der TournamentManager Website.
 */
class TManager
{
  private $db;
  private $twig;

  public function __construct()
  {
    $this->twig = $this->initTwig();
    $this->db = $this->initDatabase();
  }

  public function __destruct()
  {
    $this->db->close();
  }

  /**
   * Erstellt und initialisiert das TWIG-Subsystem und gibt dieses zurück.
   * @return Twig_Environment
   */
  private function initTwig()
  {
    Twig_Autoloader::register();
    $loader = $GLOBALS["TWIG_LOADER"] = new Twig_Loader_Filesystem("templates");
    $cache = DEV_MODE === "debug" ? false : "cache";
    $twig = $GLOBALS["TWIG"] = new Twig_Environment($loader, array("cache" => $cache));

    $assetFunc = new Twig_SimpleFunction("asset", function($assetPath)
    {
      return DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $assetPath;
    });

    $twig->addFunction($assetFunc);
    return $twig;
  }

  /**
   * Erstellt und Initialisiert die Datenbank und gibt diese zurück.
   * @return Database
   */
  private function initDatabase()
  {
    $db = $GLOBALS["DB"] = new Database();
    $db->connect();
    return $db;
  }

  /**
   * Gibt das angegebene Template mit den Parametern aus.
   * @param $template Das Template, Pfad zu einer .html Datei
   * @param array $args Argumente die an das Template übergeben werden
   */
  public function renderTemplate($template, array $args = array())
  {
    echo $this->twig->render($template, $args);
  }

  /**
   * Angegeben Seite anzeigen.
   * @param $pageName Der Name der Seite
   */
  public function handlePage($pageName)
  {
    switch (strtolower($pageName))
    {
      case "live":
        $this->renderTemplate("live.html");
        break;
      case "admin":
        $this->renderTemplate("admin.html");
        break;
      case "gruppen":
        $this->renderTemplate("gruppen.html");
        break;
      default:
        $this->renderTemplate("404.html", array("requestedPage" => '"' . $pageName . '"'));
    }
  }
}