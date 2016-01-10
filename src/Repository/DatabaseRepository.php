<?php

require_once "Repository/Database.php";
require_once "Model/Player.php";
require_once "Model/Tournament.php";
require_once "Model/Admin.php";
require_once "Model/Team.php";
require_once "Model/Group.php";
require_once "Model/Match.php";
require_once "Model/GroupTeamMapping.php";

class DatabaseRepository {
  private $db;

  public function __construct(Database $db) {
    $this->db = $db;
  }

  /**
   * Fï¿½gt einen neuen Spieler in der Datenbank hinzu
   * @param Player $player Der neue, hinzuzufï¿½gende Spieler
   * @return bool Der Rï¿½ckgabewert gibt an, ob das Hinzufï¿½gen erfolgreich abgeschlossen wurde
   */
  public function addPlayer(Player $player) {
    $result = $this->db->query("insert into Player (Team_Id, Name, Vorname) values (?, ?, ?)",
                               array(sqlInt($player->teamId), sqlString($player->lastName), sqlString($player->name)));
    if ($result !== false) {
      $player->id = $this->db->lastInsertedID();
      return true;
    }

    return false;
  }

  public function updatePlayer(Player $player) {
    $result = $this->db->query("update player set teamid = (?), vorname = (?), name = (?) where id = (?)",
                               array(sqlInt($player->teamId), sqlString($player->name), sqlString($player->lastName), sqlInt($player->id)));

    return $result;
  }

  public function removePlayer($id)
  {
    $result = $this->db->query("delete from player where id = (?)", array(sqlInt($id)));

    return $result;
  }

  /**
   * Fï¿½gt ein neues Turnier in der Datenbank hinzu
   * @param Tournament $tournament Das neu hinzuzufï¿½gende Turnier
   * @return bool Der Rï¿½ckgabewert gibt an, ob das Hinzufï¿½gen erfolgreich abgeschlossen wurde
   */
  public function addTournament(Tournament $tournament) {
    $result = $this->db->query("insert into Tournament (Name) values (?)",
                               array(sqlString($tournament->name)));
    if ($result !== false) {
      $tournament->id = $this->db->lastInsertedID();
      return true;
    }

    return false;
  }

  /**
   * Updated ein Tournament auf/in der DB
   * @param $Id id des Tournaments $name name des Tournaments
   * @return bool ob peration Ok
   */
  public function updateTournament(Tournament $tournament) {
    $result = $this->db->query("update tournament set name = (?) where id = (?)",
                               array(sqlString($tournament->name), sqlInt($tournament->id)));

    return $result;
  }

  /**
   * LÃ¶scht ein Tournament aus der DB
   * @param $Id id des zu lÃ¶schenden Tournaments
   * @return bool ob peration Ok
   */
  public function removeTournament($id) {
    $result = $this->db->query("delete from tournament where id = (?)",
                               array(sqlInt($id)));

    return $result;
  }

  public function addTeam(Team $team) {
    $result = $this->db->query("insert into team(name, tournamentid) values(?,?);",
                               array(sqlString($team->name), sqlInt($team->tournamentId)));

    return $result;
  }

  public function updateTeam(Team $team) {
    $result = $this->db->query("update team set name = ?, tournamentid = ? where id = ?",
                               array(sqlString($team->name), sqlInt($team->tournamentId), sqlInt($team->id)));

    if ($result !== false) {
      $team->id = $this->db->lastInsertedID();
      return true;
    }

    return false;
  }

  public function deleteTeam($id) {
    $result = $this->db->query("delete from team where id = ?",
                               array(sqlInt($id)));

    return $result;
  }

  public function addMatch(Match $match)
  {
    $result = $this->db->query("insert into matchinfo(GroupId, TeamFirstId, TeamSecondId, TeamFirstPoints, TeamSecondPoints, MatchTime, IsRunning, IsCompleted) values(?,?,?,?,?,?,?,?)",
                              array(sqlInt($match->groupId), sqlInt($match->teamFirstId), sqlInt($match->teamSecondId), sqlInt($match->teamFirstPoints), sqlInt($match->teamSecondPoints), sqlString($match->matchTime), sqlInt($match->isRunning), sqlInt($match->isCompleted)));

    return $result;
  }

  public function updateMatch(Match $match)
  {
    $result = $this->db->query("update matchinfo set matchTime = ?, teamFirstPoints = ?, teamSecondPoints = ?, isRunning = ?, isCompleted = ?
                                where id = ?",
                               array(sqlString($match->matchTime), sqlInt($match->teamFirstPoints), sqlInt($match->teamSecondPoints), sqlInt($match->isRunning), sqlInt($match->isCompleted)));

    if ($result !== false) {
      $match->id = $this->db->lastInsertedID();
      return true;
    }
  }

  public function deleteMatch($id)
  {
    // TODO: eventuell muss noch aus der zuordnungstabelle gelöscht werden
    $result = $this->db->query("delete from matchinfo where id = ?", array(sqlInt($id)));

    return $result;
  }

    /**
     * Suche in der Datenbank nach dem Spieler mit der ID und gibe diesen zurï¿½ck
     * @param $id Die ID des zu suchenden Spielers
     * @return bool|Player Die Rückgabewert ist entweder der gefundene Spieler, oder false
     *                        bei Auftritt eines Fehlers
     */
  public function getPlayerById($id) {
    $result = $this->db->query("select player.id, player.name, player.vorname, player.teamid, mydb.team.name as 'TeamName', mydb.Group.Name as 'GroupName' from player, team, mydb.Group where player.teamid = team.id and team.tournamentid = Group.tournamentid and player.Id = ?",
        array(sqlInt($id)));
    if ($result !== false && count($result) > 0) {
      $player = new Player();
      $player->id = $result[0]["Id"];
      $player->name = $result[0]["Vorname"];
      $player->lastName = $result[0]["Name"];
      $player->teamId = $result[0]["TeamId"];
      $player->teamName = $result[0]["TeamName"];
      return $player;
    }

    return false;
  }

  /**
   * Gibt alle Spieler in der Datenbank zurück
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Spieler beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllPlayers() {
    //$queryString = "select Player.Id, Player.Name, Player.Vorname, Player.TeamId, mydb.Team.Name as 'TeamName', mydb.Group.Name as 'GroupName' from Player, Team, mydb.Group where Player.TeamId = Team.Id and Team.TournamentId = Group.TournamentId";
    $queryString = "select Player.Id as Id, Player.Vorname as Vorname, Player.Name as Name, Player.TeamId as TeamId, Team.Name as TeamName from Player join Team where Team.Id = Player.TeamId";
    $result = $this->db->query($queryString);
    if ($result !== false) {
      $allPlayers = array();

      foreach ($result as $r) {
        $player = new Player();
        $player->id = $r["Id"];
        $player->name = $r["Vorname"];
        $player->lastName = $r["Name"];
        $player->teamId = $r["TeamId"];
        $player->teamName = $r["TeamName"];
        array_push($allPlayers, $player);
      }

      return $allPlayers;
    }

    return false;
  }

  /**
   * Gibt alle Teams von der Datenbank zurï¿½ck
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Teams beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllTeams() {
    $queryString = "select Team.Id, Team.Name, Team.TournamentId, Tournament.Name as 'TournamentName' from Team, Tournament where Team.TournamentId = Tournament.Id";

    $result = $this->db->query($queryString);

    if ($result !== FALSE) {
      $allTeams = array();

      foreach ($result as $r) {
        $team = new Team();
        $team->id = $r["Id"];
        $team->name = $r["Name"];
        $team->tournamentId = $r["TournamentId"];
        $team->tournamentName = $r["TournamentName"];
        array_push($allTeams, $team);
      }

      return $allTeams;
    }
    return false;
  }

  /**
 * Gibt alle Teams in einem bestimmten Tournament zurück
 * @param $tournamentId Die ID des Tournaments
 * @return bool|Team Die Treams, false bei Fehler
 */
  public function getAllTeamsInTournament($tournamentId) {
    $queryString = "select Team.Id as 'Id', Team.Name as 'Name', Team.TournamentId as 'TournamentId',
                      Tournament.Name as 'TournamentName'
                      from Team
                      join Tournament on Tournament.Id = Team.TournamentId
                      where TournamentId = ?";
    $result = $this->db->query($queryString, array(sqlInt($tournamentId)));

    if ($result === false) {
      return false;
    }

    $teams = array();

    foreach ($result as $r) {
      $t = new Team();
      $t->id = $r["Id"];
      $t->name = $r["Name"];
      $t->tournamentId = $r["TournamentId"];
      $t->tournamentName = $r["TournamentName"];
      $teams[] = $t;
    }

    return $teams;
  }

  public function getAllTeamsForGroup($groupId) {
    $queryString = "select Team.Id as 'Id', Team.Name as 'Name', Team.TournamentId as 'TournamentId',
                      Tournament.Name as 'TournamentName'
                      from Team
                      join tournament on tournament.Id = team.TournamentId
                      join group_has_team on group_has_team.Team_Id = Team.Id
                      where group_has_team.Group_Id = ?";
    $result = $this->db->query($queryString, array(sqlInt($groupId)));

    if ($result === false) {
      return false;
    }

    $teams = array();

    foreach ($result as $r) {
      $t = new Team();
      $t->id = $r["Id"];
      $t->name = $r["Name"];
      $t->tournamentId = $r["TournamentId"];
      $t->tournamentName = $r["TournamentName"];
      $teams[] = $t;
    }

    return $teams;
  }

  public function getMatchesFromGroup($groupId)
  {
    $result = $this->db->query("select matchinfo.Id as 'id', matchinfo.GroupId as 'groupId', mydb.group.Name as 'groupName',
                                matchinfo.TeamFirstId as 'teamFirstId', teamFirst.Name as 'teamFirstName', matchinfo.TeamFirstPoints as 'teamFirstPoints',
                                matchinfo.TeamSecondId as 'teamSecondId', teamSecond.Name as 'teamSecondName', matchinfo.TeamSecondPoints as 'teamSecondPoints',
                                matchinfo.MatchTime as 'matchTime', matchinfo.IsRunning as 'isRunning', matchinfo.IsCompleted as 'isCompleted'
                                from matchinfo, mydb.group, team teamFirst, team teamSecond
                                where matchinfo.GroupId = mydb.group.Id
                                and matchinfo.TeamFirstId = teamFirst.Id
                                and matchinfo.TeamSecondId = teamSecond.Id
                                and matchinfo.GroupId = ?;",
                               array(sqlInt($groupId)));

    if ($result !== false) {
      $allMatches = array();

      foreach ($result as $r) {
        $match = new Match();
        $match->id = $r["id"];
        $match->groupId = $r["groupId"];
        $match->groupName = $r["groupName"];
        $match->teamFirstId = $r["teamFirstId"];
        $match->teamFirstName = $r["teamFirstName"];
        $match->teamFirstPoints = $r["teamFirstPoints"];
        $match->teamSecondId = $r["teamSecondId"];
        $match->teamSecondName = $r["teamSecondName"];
        $match->teamSecondPoints = $r["teamSecondPoints"];
        $match->matchTime = $r["matchTime"];
        $match->isRunning = $r["isRunning"];
        $match->isCompleted = $r["isCompleted"];
        array_push($allMatches, $match);
      }

      return $allMatches;
    }

    return false;
  }

  /**
   * @param $groupId
   * @return array|bool
   */
  public function getAllGroupTeamMappingsForGroup($groupId) {
    $queryString = "select Id, Group_Id, Team_Id from Group_has_Team where Group_Id = ?";
    $result = $this->db->query($queryString, array(sqlInt($groupId)));

    if ($result === false) {
      return false;
    }

    $mappings = array();

    foreach ($result as $r) {
      $m = new GroupTeamMapping();
      $m->id = $r["Id"];
      $m->groupId = $r["Group_Id"];
      $m->teamId = $r["Team_Id"];
      $mappings[] = $m;
    }

    return $mappings;
  }

  /**
   * Gibt alle Gruppen von der Datenbank zurï¿½ck
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Gruppen beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllGroups() {
    $queryString = "select Id, Name, TournamentId from mydb.Group where TournamentId is not null";

    $result = $this->db->query($queryString);

    if ($result !== FALSE) {
      $allGroups = array();

      foreach ($result as $r) {
        $group = new Group();
        $group->id = $r["Id"];
        $group->name = $r["Name"];
        $group->tournamentId = $r["TournamentId"];
        $allGroups[] = $group;
      }

      return $allGroups;
    }

    return false;
  }

  /**
   * Gibt alle Gruppen von der Datenbank zurï¿½ck
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Gruppen beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllGroupsForTournament($tournamentId) {
    $queryString = "select Id, Name, TournamentId from mydb.Group where TournamentId = ?";

    $result = $this->db->query($queryString, array(sqlInt($tournamentId)));

    if ($result !== FALSE) {
      $allGroups = array();

      foreach ($result as $r) {
        $group = new Group();
        $group->id = $r["Id"];
        $group->name = $r["Name"];
        $group->tournamentId = $r["TournamentId"];
        $allGroups[] = $group;
      }

      return $allGroups;
    }

    return false;
  }

  /**
   * Gibt die Gruppe mit der angegebenen ID zurück
   * @param $id Die ID der Gruppe
   * @return bool|Group Die gefundene Gruppe, false bei Fehler
   */
  public function getGroupWithId($id) {
    $queryString = "select * from mydb.Group where Id = ?";
    $result = $this->db->query($queryString, array(sqlInt($id)));

    if ($result === false || count($result) <= 0) {
      return false;
    }

    $group = new Group();
    $group->id = $result[0]["Id"];
    $group->name = $result[0]["Name"];
    $group->tournamentId = $result[0]["TournamentId"];
    return $group;
  }

  /**
   * Gibt alle Turniere in der Datenbank zurï¿½ck
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Turniere beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllTournaments() {
    $result = $this->db->query("select * from Tournament");

    if ($result !== false) {
      $allTournaments = array();

      foreach ($result as $r) {
        $tournament = new Tournament();
        $tournament->id = $r["Id"];
        $tournament->name = $r["Name"];
        $tournament->isLive =$r["IsLive"];
        array_push($allTournaments, $tournament);
      }

      return $allTournaments;
    }

    return false;
  }

  /**
   * Gibt alle Turniere in der zurück, welche als live markiert sind
   * @return array|bool Der Rückgabewert ist entweder ein Array welches alle Turniere beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getLiveTournaments() {
    $result = $this->db->query("select * from Tournament where IsLive = 1");

    if ($result !== false) {
      $allTournaments = array();

      foreach ($result as $r) {
        $tournament = new Tournament();
        $tournament->id = $r["Id"];
        $tournament->name = $r["Name"];
        $tournament->isLive =$r["IsLive"];
        array_push($allTournaments, $tournament);
      }

      return $allTournaments;
    }

    return false;
  }

  /**
   * Gibt alle Matches in der Datenbank zurï¿½ck
   * @return array|bool Der Rï¿½ckgabewert ist entweder ein Array welches alle Matches beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllMatches() {
    $result = $this->db->query("select matchinfo.Id as 'id', matchinfo.GroupId as 'groupId', mydb.group.Name as 'groupName',
                                matchinfo.TeamFirstId as 'teamFirstId', teamFirst.Name as 'teamFirstName', matchinfo.TeamFirstPoints as 'teamFirstPoints',
                                matchinfo.TeamSecondId as 'teamSecondId', teamSecond.Name as 'teamSecondName', matchinfo.TeamSecondPoints as 'teamSecondPoints',
                                matchinfo.MatchTime as 'matchTime', matchinfo.IsRunning as 'isRunning', matchinfo.IsCompleted as 'isCompleted'
                                from matchinfo, mydb.group, team teamFirst, team teamSecond
                                where matchinfo.GroupId = mydb.group.Id
                                and matchinfo.TeamFirstId = teamFirst.Id
                                and matchinfo.TeamSecondId = teamSecond.Id
                                order by GroupId,Id;");

    if ($result !== false) {
      $allMatches = array();

      foreach ($result as $r) {
        $match = new Match();
        $match->id = $r["id"];
        $match->groupId = $r["groupId"];
        $match->groupName = $r["groupName"];
        $match->teamFirstId = $r["teamFirstId"];
        $match->teamFirstName = $r["teamFirstName"];
        $match->teamFirstPoints = $r["teamFirstPoints"];
        $match->teamSecondId = $r["teamSecondId"];
        $match->teamSecondName = $r["teamSecondName"];
        $match->teamSecondPoints = $r["teamSecondPoints"];
        $match->matchTime = $r["matchTime"];
        $match->isRunning = $r["isRunning"];
        $match->isCompleted = $r["isCompleted"];
        array_push($allMatches, $match);
      }

      return $allMatches;
    }

    return false;
  }

  /**
   * Gibt alle aktuell laufenden Matches des Turniers zurück
   * @return array|bool Der Rückgabewert ist entweder ein Array welches alle Matches beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllRunningMatchesForTournament($tournamentId) {
    $result = $this->db->query("select matchinfo.Id as 'id', matchinfo.GroupId as 'groupId', mydb.group.Name as 'groupName',
                                matchinfo.TeamFirstId as 'teamFirstId', teamFirst.Name as 'teamFirstName', matchinfo.TeamFirstPoints as 'teamFirstPoints',
                                matchinfo.TeamSecondId as 'teamSecondId', teamSecond.Name as 'teamSecondName', matchinfo.TeamSecondPoints as 'teamSecondPoints',
                                matchinfo.MatchTime as 'matchTime', matchinfo.IsRunning as 'isRunning', matchinfo.IsCompleted as 'isCompleted'
                                from matchinfo, mydb.group, team teamFirst, team teamSecond
                                where matchinfo.GroupId = mydb.group.Id
                                and matchinfo.TeamFirstId = teamFirst.Id
                                and matchinfo.TeamSecondId = teamSecond.Id
                                and matchinfo.IsRunning = 1
                                and mydb.group.TournamentId = ?
                                order by GroupId,Id;", array(sqlInt($tournamentId)));

    if ($result !== false) {
      $allMatches = array();

      foreach ($result as $r) {
        $match = new Match();
        $match->id = $r["id"];
        $match->groupId = $r["groupId"];
        $match->groupName = $r["groupName"];
        $match->teamFirstId = $r["teamFirstId"];
        $match->teamFirstName = $r["teamFirstName"];
        $match->teamFirstPoints = $r["teamFirstPoints"];
        $match->teamSecondId = $r["teamSecondId"];
        $match->teamSecondName = $r["teamSecondName"];
        $match->teamSecondPoints = $r["teamSecondPoints"];
        $match->matchTime = $r["matchTime"];
        $match->isRunning = $r["isRunning"];
        $match->isCompleted = $r["isCompleted"];
        array_push($allMatches, $match);
      }

      return $allMatches;
    }

    return false;
  }

  /**
   * Gibt alle aktuell beendeten Matches des Turniers zurück
   * @return array|bool Der Rückgabewert ist entweder ein Array welches alle Matches beinhaltet,
   *                       oder false bei Auftritt eines Fehlers
   */
  public function getAllCompletedMatchesForTournament($tournamentId) {
    $result = $this->db->query("select matchinfo.Id as 'id', matchinfo.GroupId as 'groupId', mydb.group.Name as 'groupName',
                                matchinfo.TeamFirstId as 'teamFirstId', teamFirst.Name as 'teamFirstName', matchinfo.TeamFirstPoints as 'teamFirstPoints',
                                matchinfo.TeamSecondId as 'teamSecondId', teamSecond.Name as 'teamSecondName', matchinfo.TeamSecondPoints as 'teamSecondPoints',
                                matchinfo.MatchTime as 'matchTime', matchinfo.IsRunning as 'isRunning', matchinfo.IsCompleted as 'isCompleted'
                                from matchinfo, mydb.group, team teamFirst, team teamSecond
                                where matchinfo.GroupId = mydb.group.Id
                                and matchinfo.TeamFirstId = teamFirst.Id
                                and matchinfo.TeamSecondId = teamSecond.Id
                                and matchinfo.IsCompleted = 1
                                and mydb.group.TournamentId = ?
                                order by GroupId,Id;", array(sqlInt($tournamentId)));

    if ($result !== false) {
      $allMatches = array();

      foreach ($result as $r) {
        $match = new Match();
        $match->id = $r["id"];
        $match->groupId = $r["groupId"];
        $match->groupName = $r["groupName"];
        $match->teamFirstId = $r["teamFirstId"];
        $match->teamFirstName = $r["teamFirstName"];
        $match->teamFirstPoints = $r["teamFirstPoints"];
        $match->teamSecondId = $r["teamSecondId"];
        $match->teamSecondName = $r["teamSecondName"];
        $match->teamSecondPoints = $r["teamSecondPoints"];
        $match->matchTime = $r["matchTime"];
        $match->isRunning = $r["isRunning"];
        $match->isCompleted = $r["isCompleted"];
        array_push($allMatches, $match);
      }

      return $allMatches;
    }

    return false;
  }

  public function getAllActiveTournaments()
  {
    $result = $this->db->query("SELECT * FROM Tournament WHERE IsLive = 1");

    if ($result !== false) {
      $allTournaments = array();

      foreach ($result as $r) {
        $tournament = new Tournament();
        $tournament->id = $r["Id"];
        $tournament->name = $r["Name"];
        $tournament->isLive =$r["IsLive"];
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
   * @return Admin|bool Der Rï¿½ckgabewert ist entweder der gefundene Admin, oder false bei Auftritt
   *                        eines Fehlers
   */
  public function getAdminByCredentials($username, $password) {
    $result = $this->db->query("select * from Admin where Username = ? and Password = ?",
                               array(sqlString($username), sqlString($password)));

    if ($result !== false && count($result) > 0) {
      $admin = new Admin();
      $admin->id = $result[0]["Id"];
      $admin->username = $result[0]["Username"];
      $admin->password = $result[0]["Password"];
      return $admin;
    }

    return false;
  }

  /**
   * Gibt alle Teams, die der bestimmten Gruppe zugewisen wurden, zurück
   * @param $groupId Die ID der Gruppe
   * @return array|bool Die Teams der Gruppe, false bei Fehler
   */
  public function getTeamsForGroup($groupId) {
    $queryString = "select Team.Id as 'Id', Team.Name as 'Name', Team.TournamentId as 'TournamentId',
                      Tournament.Name as 'TournamentName'
                      from Group_has_Team as ght
                      join Team on Team.Id = ght.Team_Id
                      join Tournament on Tournament.Id = Team.TournamentId
                      where ght.Group_Id = ?";
    $result = $this->db->query($queryString, array(sqlInt($groupId)));

    if ($result === false) {
      return false;
    }

    $teams = array();

    foreach ($result as $r) {
      $t = new Team();
      $t->id = $r["Id"];
      $t->name = $r["Name"];
      $t->tournamentId = $r["TournamentId"];
      $t->tournamentName = $r["TournamentName"];
      $teams[] = $t;
    }

    return $teams;
  }
}

