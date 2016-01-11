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
        $temp = $this->calcMatchPointsOfTeam($group->id, $team->id);
        $team->matchPoints = $temp["teamAllMatchPoints"];
        $team->wonPoints = $temp["teamWonPoints"];
        $team->lostPoints = $temp["teamLostPoints"];
        $team->groupName = $group->name;
      }

      usort($group->teams, "cmp");

    }

    return $groups;
  }

  private function calcMatchPointsOfTeam($groupId, $teamId) {
    $teamAllMatchPoints = 0;
    $teamAllWonPoints = 0;
    $teamAllLostPoints = 0;
    $matches = $this->dbRepo->getMatchesFromGroup($groupId);

    foreach ($matches as $i => $match) {
      if ($match->teamFirstId !== $teamId && $match->teamSecondId !== $teamId) {
        unset($matches[$i]);
      }
    }

    foreach ($matches as $match) {
      if ($match->isCompleted === 1) {
        $searchedTeam = 0;
        $otherTeamPoints = 0;

        if ($match->teamFirstId === $teamId) {
          $searchedTeamPoints = $match->teamFirstPoints;
          $otherTeamPoints = $match->teamSecondPoints;
        } else {
          $searchedTeamPoints = $match->teamSecondPoints;
          $otherTeamPoints = $match->teamFirstPoints;
        }

        $teamAllWonPoints = $teamAllWonPoints + $searchedTeamPoints;
        $teamAllLostPoints = $teamAllLostPoints + $otherTeamPoints;

        if ($searchedTeamPoints > $otherTeamPoints) {
          $teamAllMatchPoints = $teamAllMatchPoints + 3;
        } else if ($searchedTeamPoints === $otherTeamPoints) {
          $teamAllMatchPoints = $teamAllMatchPoints + 1;
        }
      }
    }

    $arrayToReturn = array("teamAllMatchPoints" => $teamAllMatchPoints, "teamWonPoints" => $teamAllWonPoints, "teamLostPoints" => $teamAllLostPoints);

    return $arrayToReturn;
  }
}

function cmp($a, $b)
{
  if ($a->matchPoints == $b->matchPoints) {
    return 0;
  }
  return ($a->matchPoints > $b->matchPoints) ? -1 : 1;
}