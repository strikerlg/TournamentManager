<?php

class Database
{
  // Members
  private $mConnection = null;
  private $mIsConnected = false;
  private $mConnectionErrorStr = "";

  // Getters & setters
  public function isConnected() { return $this->mIsConnected; }
  public function errorStr() { return $this->mConnectionErrorStr; }
  public function lastInsertedID() { return $this->mConnection->insert_id; }

  // Methods
  /**
   * Stellt Verbindung zur Datenbank her.
   * @return boolean true falls erfolgreich verbunden, ansonnsten false.
   */
  public function connect($dbIp, $dbUsername, $dbPassword, $dbDb)
  {
    if($this->isConnected())
      $this->close();

    $this->mConnection = new \mysqli($dbIp, $dbUsername, $dbPassword, $dbDb);

    if($this->mConnection->connect_errno != 0)
    {
      $this->mConnectionErrorStr = $this->mConnection->connect_error;
    }
    else
    {
      $this->mIsConnected = true;
    }

    return $this->isConnected();
  }

  /**
   * Schließt die Verbindung zur Datenbank.
   */
  public function close()
  {
    if($this->mConnection != null)
      $this->mConnection->close();

    $this->mIsConnected = false;
  }

  /**
   * Wandelt ein Wertarray in ein Referenzarray um.
   * PHP sucks!
   * @param type $arr
   * @return type
   */
  private function makeValuesReferences($arr)
  {
    $refs = array();
    foreach($arr as $key => $value)
      $refs[$key] = &$arr[$key];
    return $refs;
  }

  /**
   * Zentral Methode für SQL-Queries.
   * @param type $queryStr Das SQL-Statement
   * @param type $arguments Optionale Argumente, um SQL-Injection zu vermeiden.
   * @return null|array null bei Fehler, ansonnsten die empfangenen Daten.
   */
  public function query($queryStr, $arguments = array())
  {
    if(!$this->isConnected())
    {
        return null;
    }

    $query = $this->mConnection->prepare($queryStr);
    if(!$query)
    {
      echo "<span style='font-weight:bold;color:red;font-size:24px;'>Oha... - </span><span style='font-size:20px;'>" . $this->mConnection->error . "</span><br>";
      return null;
    }

    $types = "";
    $argVals = array();
    foreach($arguments as $argEntry)
    {
      $types .= $argEntry[1];
      array_push($argVals, (string)$argEntry[0]);
    }

    $typesArr = ($types === "") ? array() : (array)$types;
    $params = array_merge($typesArr, $argVals);

    if(count($params) > 0)
    {
      call_user_func_array(array($query, "bind_param"), $this->makeValuesReferences($params));
    }
    $query->execute();

    $rows = array();
    $result = $query->get_result();
    while($result && $row = $result->fetch_assoc())
    {
      array_push($rows, $row);
    }

    return $rows;
  }
}

function pair($a, $b)
{ return array($a, $b); }

function sqlArg($value, $type)
{ return pair($value, $type); }

function sqlInt($value)
{ return sqlArg($value, "i"); }

function sqlString($value)
{ return sqlArg($value, "s"); }
