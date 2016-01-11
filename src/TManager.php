<?php

require_once "Twig/Autoloader.php";
require_once "Repository/Database.php";
require_once "Repository/DatabaseRepository.php";
require_once "Controller/LiveController.php";

/**
 * Class TManager - Hauptklasse der TournamentManager Website.
 */
class TManager
{
  private $twig;
  private $db;
  private $dbRepo;
  private $config;

  public function __construct()
  {
    session_start();

    $this->parseConfig();
    $this->twig = $this->initTwig();
    $this->db = $this->initDatabase();
    $this->dbRepo = new DatabaseRepository($this->db);
  }

  public function __destruct()
  {
    if ($this->db) {
      $this->db->close();
    }
  }

  private function parseConfig() {
    $confFilePath = "conf.ini";

    if (file_exists($confFilePath)) {
      $this->config = parse_ini_file($confFilePath, true);
      if ($this->config === false) {
        $errmsg = "Beim Parsen der Konfigurationsdatei ($confFilePath) ist ein Fehler aufgetreten.<br>
                   Überprüfen Sie ob die Datei existiert und ob alle zwingenden Einstellungen angeführt sind.";
        echo "<span style='color: red; font-weight: bold;'>$errmsg</span>";
        exit(0);
      }
    }
    else {
      $errmsg = "Die Konfigurationsdatei ($confFilePath) wurde nicht gefunden.<br>
                   Überprüfen Sie ob die Datei existiert.";
      echo "<span style='color: red; font-weight: bold;'>$errmsg</span>";
      exit(0);
    }
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

    $dumpFunc = new Twig_SimpleFunction("dump", function($obj) {
      d($obj);
    });

    $twig->addFunction($assetFunc);
    $twig->addFunction($dumpFunc);
    return $twig;
  }

  /**
   * Erstellt und Initialisiert die Datenbank und gibt diese zurück.
   * @return Database
   */
  private function initDatabase()
  {
    $db = $GLOBALS["DB"] = new Database();
    $db->connect($this->config["database"]["ip"], $this->config["database"]["username"],
      $this->config["database"]["password"], $this->config["database"]["database"]);
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
      case "live-matches":
        //$this->httpRefresh(15);
        $liveData = (new LiveController($this->dbRepo))->getData();
        $this->renderTemplate("live_matches.html", array_merge($commonArgs, array("liveData" => $liveData)));
        break;
      case "live-punktestand":
        $this->httpRefresh(15);
        $liveData = (new LiveController($this->dbRepo))->getData();
        $this->renderTemplate("live_punktestand.html", array_merge($commonArgs, array("liveData" => $liveData)));
        break;
      case "admin":
        $this->renderTemplate("admin.html", array_merge($commonArgs, array("incorrectLogin" => false)));
            break;
      case "login":
        $result = $this->adminLogin();
        if ($result === false)
        {
          if (!isset($_POST["username"]) || !isset($_POST["password"]))
          {
            $this->renderTemplate("admin.html", array_merge($commonArgs, array("incorrectLogin" => false)));
          }
          else
          {
            $this->renderTemplate("admin.html", array_merge($commonArgs, array("incorrectLogin" => true)));
          }
        }
        else
        {
          $this->handlePage("live-punktestand");
        }
        break;
      case "logout":
        $this->adminLogout();
        $this->handlePage("live-punktestand");
        break;
      case "gruppen":
        $groups = $this->dbRepo->getAllGroups();
        $turniere = $this->dbRepo->getAllTournaments();
        $this->authorizedPage("gruppen.html", array_merge($commonArgs, array("turniere" => $turniere, "groups" => $groups)));
        break;
      case "matches":
        $matches = $this->dbRepo->getAllMatches();
        $groups = $this->dbRepo->getAllGroups();
        $teams = $this->dbRepo->getAllTeams();
        $this->authorizedPage("matches.html", array_merge($commonArgs, array("matches" => $matches, "gruppen" => $groups, "teams" => $teams)));
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
      case "teams-zuweisen":
        $groupId = null;
        if (isset($_GET["GroupId"]) && is_numeric($_GET["GroupId"])) {
          $groupId = intval($_GET["GroupId"]);
        }
        if ($groupId === null) {
          $this->httpRedirect("/gruppen");
        }
        else {
          $group = $this->dbRepo->getGroupWithId($groupId);
          $allTeams = $this->dbRepo->getAllTeamsInTournament($group->tournamentId);
          $mappings = $this->dbRepo->getAllGroupTeamMappingsForGroup($groupId);
          $this->authorizedPage("gruppe_has_teams.html", array_merge($commonArgs,
            array("group" => $group, "teams" => $mappings, "allTeams" => $allTeams)));
        }
        break;
      case "matchmaker":
        if ($this->ensureLoggedIn()) {
          $groupId = null;
          if (isset($_GET["GroupId"]) && is_numeric($_GET["GroupId"])) {
            $groupId = intval($_GET["GroupId"]);
          }
          if ($groupId === null) {
            $this->httpRedirect("/gruppen");
          }

          $teams = $this->dbRepo->getAllTeamsForGroup($groupId);

          for($i = 0; $i < count($teams); $i++)
          {
            for($j = $i + 1; $j < count($teams); $j++)
            {
              $matchNew = new Match();
              $matchNew->teamFirstId = $teams[$i]->id;
              $matchNew->teamSecondId = $teams[$j]->id;
              $matchNew->teamFirstPoints = 0;
              $matchNew->teamSecondPoints = 0;
              $matchNew->isRunning = 0;
              $matchNew->isCompleted = 0;
              $matchNew->groupId = $groupId;
              $matchNew->matchTime = date('Y-m-d H:i:s');

              $this->dbRepo->addMatch($matchNew);
            }
          }

          $this->httpRedirect("/gruppen");
        }
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
   * Zeigt das Login-Formular an, wenn der Benutzer nicht als Admin eingeloggt ist
   * @return bool Gibt an ob der Benutzer eingeloggt ist
   */
  private function ensureLoggedIn() {
    if ($this->loggedInAdmin() === false) {
      $this->handlePage("login");
      return false;
    }

    return true;
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
    echo $queryString . "<br>";
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
      echo $queryString . "<br>";
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

  /**
   * Den Browser des Client anweisen, die angegebene Seite anzusteuern
   * @param $url Die anzusteuernde Seite
   */
  public function httpRedirect($url) {
    header("Location: $url");
  }

  /**
   * Den Browser des Clients anweisen, die aktualle Seite nach einer bestimmten Zeit neu nachzuladen
   * @param $seconds Die Zeit in Sekunden die gewartet wird, bis nachgeladen wird
   */
  public function httpRefresh($seconds) {
    header("Refresh: $seconds");
  }
}