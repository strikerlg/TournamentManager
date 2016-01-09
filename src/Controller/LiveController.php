<?php
require_once "Model/Match.php";
require_once "Model/Group.php";
require_once "Model/Player.php";
require_once "Model/Team.php";
require_once "Model/Tournament.php";
require_once "Repository/Database.php";
require_once "Repository/DatabaseRepository.php";

class LiveController
{
    private $db;
    private $dbRepo;

    public function __construct()
    {
        $this->db = $this->initDatabase();
        $this->dbRepo = new DatabaseRepository($this->db);

        session_start();
    }

    public function GetData($activeTournamentId)
    {
        $groups = $this->dbRepo->getAllGroupsfromTournament($activeTournamentId);
        $currentMatch = new Match();
        $nextmatch = new Match();
        foreach($groups as $group)
        {
            $group->teams = $this->dbRepo->getAllTeamsFromGroup($group->id);

            foreach($group->teams as $team)
            {
                $team->matchPoints = $this->calcMatchPointsOfTeam($group->id, $team->id);
            }
        }

        $arrToReturn = array(TeamMatchPoints => $groups, currentMatch => $currentMatch, nextmatch => $nextmatch);

        json_encode($arrToReturn);
    }

    private function calcMatchPointsOfTeam($groupId, $teamId)
    {
        $teamAllMatchPoints = 0;
        $matches = $this->dbRepo->getMatchesFromGroup($groupId);

        foreach($matches as $i => $match)
        {
            if($match->teamFirstId !== $teamId && $match->teamSecondId !== $teamId)
            {
                unset($match[$i]);
            }
        }

        foreach($matches as $match)
        {
            $searchedTeamPoints = 0;
            $otherTeamPoints = 0;

            if($match->teamFirstId === $teamId)
            {
                $searchedTeamPoints = $match->teamFirstPoints;
                $otherTeamPoints = $match->teamSecondPoints;
            }
            else
            {
                $searchedTeamPoints = $match->teamSecondPoints;
                $otherTeamPoints = $match->teamFirstPoints;
            }

            if($searchedTeamPoints > $otherTeamPoints)
            {
                $teamAllMatchPoints = $teamAllMatchPoints + 2;
            }
            else if($searchedTeamPoints === $otherTeamPoints)
            {
                $teamAllMatchPoints = $teamAllMatchPoints + 1;
            }
        }

        return $teamAllMatchPoints;

    }
}