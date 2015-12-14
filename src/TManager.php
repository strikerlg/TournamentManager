<?php

require_once "Twig/Autoloader.php";
require_once "Repository/Database.php";
require_once "Repository/DatabaseRepository.php";

/**
 * Class TManager - Hauptklasse der TournamentManager Website.
 */
class TManager
{
  private $twig;
  private $db;
  private $dbRepo;

  public function __construct()
  {
    $this->twig = $this->initTwig();
    $this->db = $this->initDatabase();
    $this->dbRepo = new DatabaseRepository($this->db);

    session_start();
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
      return "/templates/" . $assetPath;
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
    $commonArgs = array("adminLoggedIn" => $this->loggedInAdmin() !== false);

    /*
     * TODO: Diesen großen switch durch sauberes Routing ersetzen, sodass auch
     * im page_header.html Partial die aktuelle Seite mitgegeben werden kann (für das Highlighting).
     */

    switch (strtolower($pageName))
    {
      case "live":
        $this->renderTemplate("live.html", array_merge($commonArgs, array()));
        break;
      case "admin":
        $this->renderTemplate("admin.html", array_merge($commonArgs, array()));
        break;
      case "login":
        $result = $this->adminLogin();
        if ($result === false)
        {
          $this->renderTemplate("admin.html", array_merge($commonArgs, array("incorrectLogin" => true)));
        }
        else
        {
          $this->handlePage("live");
        }
        break;
      case "logout":
        $this->adminLogout();
        $this->handlePage("live");
        break;
      case "gruppen":
        if ($this->loggedInAdmin() !== false)
        {
          $this->renderTemplate("gruppen.html", array_merge($commonArgs, array()));
        }
        else
        {
          $this->handlePage("login");
        }
        break;
      default:
        $this->renderTemplate("404.html", array_merge($commonArgs, array("requestedPage" => '"' . $pageName . '"')));
    }
  }

  /**
   * Admin-Einlog Logik - Lest Benuzername und Passwort aus $_POST aus
   * und versucht den Admin anhand dieser Daten einzuloggen
   * @return bool Der Rückgabewert gibt an, ob der Admin erfolgreich eingeloggt wurde
   */
  private function adminLogin()
  {
    // username und password müssen beide logischerweise gesetzt sein
    if (!isset($_POST["username"]) || !isset($_POST["password"]))
    {
      return false;
    }

    // Überprüfe ob die Logindaten korrekt sind
    $admin = $this->dbRepo->getAdminByCredentials($_POST["username"], $_POST["password"]);
    if ($admin === false)
    {
      return false;
    }

    // Admin in Session speichern und fertig
    $_SESSION["admin"] = $admin;
    return true;
  }

  /**
   * Loggt den aktuelle eingeloggten Admin aus
   */
  private function adminLogout()
  {
    unset($_SESSION["admin"]);
  }

  /**
   * Gibt den aktuellen eingeloggten Admin zurück, sofern vorhanden
   * @return Admin|bool Der Rückgabewert enthält entweder den eingeloggten Admin wenn vorhanden,
   * ansonnsten false
   */
  private function loggedInAdmin()
  {
    if (!isset($_SESSION["admin"]))
    {
      return false;
    }

    $admin = $_SESSION["admin"];
    if ($admin instanceof Admin)
    {
      return $admin;
    }

    return false;
  }
}