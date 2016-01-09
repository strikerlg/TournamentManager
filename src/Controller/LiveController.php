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

    function GetData()
    {
        $arr = array();
        json_encode($arr);
    }
}