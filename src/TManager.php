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
   * @param $pageName Der Name der anzuzeigenden Seite
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
      case "groups":
        $teams = $this->dbRepo->getAllTeams();
        $turniere = $this->dbRepo->getAllTournaments();
        $this->authorizedPage("groups.html", array_merge($commonArgs, array("turniere" => $turniere, "teams" => $teams)));
        break;
      case "matches":
        $this->authorizedPage("matches.html", array_merge($commonArgs, array()));
        break;
      case "teams":
        $teams = $this->dbRepo->getAllTeams();
        $turniere = $this->dbRepo->getAllTournaments();
        $this->authorizedPage("teams.html", array_merge($commonArgs, array("turniere" => $turniere, "teams" => $teams)));
        break;
      case "turniere":
        $turniere = $this->dbRepo->getAllTournaments();
        $this->authorizedPage("turniere.html", array_merge($commonArgs, array("turniere" => $turniere)));
        break;
      case "spieler":
        $players = $this->dbRepo->getAllPlayers();
        $teams = $this->dbRepo->getAllTeams();
        $this->authorizedPage("spieler.html", array_merge($commonArgs, array("players" => $players, "teams" => $teams)));
        break;
      default:
        $this->renderTemplate("404.html", array_merge($commonArgs, array("requestedPage" => '"' . $pageName . '"')));
    }
  }

  private function authorizedPage($template, $args)
  {
    if ($this->loggedInAdmin() !== false)
    {
      $this->renderTemplate($template, $args);
    }
    else
    {
      $this->handlePage("login");
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

  /**
   * Angegebene Action ausführen
   * @param $action Die Action
   */
  public function handleAction($action)
  {
    $table = $_POST["table"];

    switch ($action)
    {
      case "saveAll":
        $this->insertAll($table, $_POST["forms"]);
        break;
      case "updateAll":
        $this->updateAll($table, $_POST["forms"]);
        break;
      case "deleteRecord":
        $this->deleteRecord($table, $_POST["id"]);
        break;
    }
  }

  /**
   * Inserte die übergebenen Datensätze in die angegebene Tabelle
   * @param $table Die Tabelle in die inserted wird
   * @param $records Die Datensätze die zu inserten sind
   * @return array|null Das Result der Datenbank-Query Methode
   */
  private function insertAll($table, $records) {
    if (count($records) <= 0) {
      return;
    }

    $columnNames = array_column($records[0], "name");
    $values = '(' . implode(", ", $columnNames) . ')';

    $data = [];
    foreach ($records as $record) {
      $data[] = '(' . implode(", ", array_map(function ($i) { return '\'' . $i . '\''; }, array_column($record, "value"))) . ')';
    }

    $queryString = "insert into $table $values values " . implode(", ", $data);
    //echo $queryString . "<br>";
    $result = $this->db->query($queryString);
  }

  /**
   * Aktuallisiert die übergebenen Datensätze in der angegebenen Tabelle
   * @param $table Die Tabelle
   * @param $records Die Datensätze
   */
  private function updateAll($table, $records) {
    if (count($records) <= 0) {
      return;
    }

    foreach ($records as $record) {
      $sets = [];
      $recordId = $record[0]["value"];

      foreach ($record as $field) {
        $sets[] = array("name" => $field["name"], "value" => $field["value"]);
      }

      $queryString = "update $table set " . implode(", ", array_map(function ($i) { return $i["name"] . '=\'' . $i["value"] . '\''; }, $sets)) . " where Id = $recordId";
      $this->db->query($queryString);
    }
  }

  /**
   * Löscht den übergebenen Datensatz in der übergebenen Tabelle
   * @param $table Die Tabelle
   * @param $recordId Die ID des zu löschende Datensatz
   */
  private function deleteRecord($table, $recordId) {
    $this->db->query("delete from $table where Id = ?", array(sqlInt($recordId)));
  }
}