<?php
require_once "Model/Match.php";
require_once "Model/Group.php";
require_once "Model/Player.php";
require_once "Model/Team.php";
require_once "Model/Tournament.php";
require_once "Repository/Database.php";
require_once "Repository/DatabaseRepository.php";

class LiveController {
  private $dbRepo;

  public function __construct(DatabaseRepository $dbRepo) {
    $this->dbRepo = $dbRepo;
  }

  public function getData() {
    $tournaments = $this->dbRepo->getLiveTournaments();
    $data = array();

    foreach ($tournaments as $t) {
      $data[$t->name] = array("running" => $this->dbRepo->getAllRunningMatchesForTournament($t->id),
        "completed" => $this->dbRepo->getAllCompletedMatchesForTournament($t->id), "TournamentMatchPoints" => $this->getTournamentData($t->id));
    }

    return $data;
  }

  private function getTournamentData($activeTournamentId) {
    $groups = $this->dbRepo->getAllGroupsForTournament($activeTournamentId);

    foreach ($groups as $group) {
      $group->teams = $this->dbRepo->getAllTeamsForGroup($group->id);

      foreach ($group->teams as $team) {
        $team->matchPoints = $this->calcMatchPointsOfTeam($group->id, $team->id);
        $team->groupName = $group->name;
      }
    }

    return $groups;
  }

  private function calcMatchPointsOfTeam($groupId, $teamId) {
    $teamAllMatchPoints = 0;
    $matches = $this->dbRepo->getMatchesFromGroup($groupId);

    foreach ($matches as $i => $match) {
      if ($match->teamFirstId !== $teamId && $match->teamSecondId !== $teamId) {
        unset($matches[$i]);
      }
    }

    foreach ($matches as $match) {
      $searchedTeamPoints = 0;
      $otherTeamPoints = 0;

      if ($match->teamFirstId === $teamId) {
        $searchedTeamPoints = $match->teamFirstPoints;
        $otherTeamPoints = $match->teamSecondPoints;
      } else {
        $searchedTeamPoints = $match->teamSecondPoints;
        $otherTeamPoints = $match->teamFirstPoints;
      }

      if ($searchedTeamPoints > $otherTeamPoints) {
        $teamAllMatchPoints = $teamAllMatchPoints + 2;
      } else if ($searchedTeamPoints === $otherTeamPoints) {
        $teamAllMatchPoints = $teamAllMatchPoints + 1;
      }
    }

    return $teamAllMatchPoints;
  }
}