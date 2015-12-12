<?php

require_once "Repository/Database.php";
require_once "Model/Player.php";
require_once "Model/Tournament.php";
require_once "Model/Admin.php";

class DatabaseRepository
{
  private $db;

  public function __construct(Database $db)
  {
    $this->db = $db;
  }

  /**
   * Fügt einen neuen Spieler in der Datenbank hinzu
   * @param Player $player Der neue, hinzuzufügende Spieler
   * @return bool Der Rückgabewert gibt an, ob das Hinzufügen erfolgreich abgeschlossen wurde
   */
  public function addPlayer(Player $player)
  {
    $result = $this->db->query("insert into Player (Team_Id, Name, Vorname) values (?, ?, ?)",
                                     array(sqlInt($player->teamId), sqlString($player->lastName), sqlString($player->name)));
    if ($result !== false)
    {
      $player->id = $this->db->lastInsertedID();
      return true;
    }

    return false;
  }

  /**
   * Suche in der Datenbank nach dem Spieler mit der ID und gibe diesen zurück
   * @param $id Die ID des zu suchenden Spielers
   * @return bool|Player Die Rückgabewert ist entweder der gefundene Spieler, oder false
   *                        bei Auftritt eines Fehlers
   */
  public function getPlayerById($id)
  {
    $result = $this->db->query("select * from Player where Id = ?",
                               array(sqlInt($id)));
    if ($result !== false && count($result) > 0)
    {
      $player = new Player();
      $player->id = $result[0]["Id"];
      $player->name = $result[0]["Vorname"];
      $player->lastName = $result[0]["Name"];
      $player->teamId = $result[0]["Team_Id"];
      return $player;
    }

    return false;
  }

  /**
   * Gibt alle Spieler in der Datenbank zurück
   * @return array|bool Der Rückgabewert ist entweder ein Array welches alle Spieler beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllPlayers()
  {
    $result = $this->db->query("select * from Player");
    if ($result !== false)
    {
      $allPlayers = array();

      foreach ($result as $r)
      {
        $player = new Player();
        $player->id = $r["Id"];
        $player->name = $r["Vorname"];
        $player->lastName = $r["Name"];
        $player->teamId = $r["Team_Id"];
        array_push($allPlayers, $player);
      }

      return $allPlayers;
    }

    return false;
  }

  /**
   * Fügt ein neues Turnier in der Datenbank hinzu
   * @param Tournament $tournament Das neu hinzuzufügende Turnier
   * @return bool Der Rückgabewert gibt an, ob das Hinzufügen erfolgreich abgeschlossen wurde
   */
  public function addTournament(Tournament $tournament)
  {
    $result = $this->db->query("insert into Tournament (Name) values (?)",
                               array(sqlString($tournament->name)));
    if ($result !== false)
    {
      $tournament->id = $this->db->lastInsertedID();
      return true;
    }

    return false;
  }

  /**
   * Gibt alle Turniere in der Datenbank zurück
   * @return array|bool Der Rückgabewert ist entweder ein Array welches alle Turniere beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllTournaments()
  {
    $result = $this->db->query("select * from Tournament");

    if ($result !== false)
    {
      $allTournaments = array();

      foreach ($result as $r)
      {
        $tournament = new Tournament();
        $tournament->id = $r["Id"];
        $tournament->name = $r["Name"];
        array_push($allTournaments, $tournament);
      }

      return $allTournaments;
    }

    return false;
  }

  /**
   * Sucht in der Datenbank nach dem Admin mit den angegebenen Anmeldedaten
   * @param $username Der Benuzername des Admins
   * @param $password Das Passwort des Admins
   * @return Admin|bool Der Rückgabewert ist entweder der gefundene Admin, oder false bei Auftritt
   *                        eines Fehlers
   */
  public function getAdminByCredentials($username, $password)
  {
    $result = $this->db->query("select * from Admin where Username = ? and Password = ?",
                               array(sqlString($username), sqlString($password)));

    if ($result !== false && count($result) > 0)
    {
      $admin = new Admin();
      $admin->id = $result[0]["Id"];
      $admin->username = $result[0]["Username"];
      $admin->password = $result[0]["Password"];
      return $admin;
    }

    return false;
  }
}