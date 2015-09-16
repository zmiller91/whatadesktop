<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Connection {
    private $conn;
    
    private function __construct($user, $password) {
        $this->conn = new mysqli("localhost", $user, $password);
        $this->conn->select_db("backgrounds");
        if ($this->conn->connect_error){
            die("Connection failed because: " . $this->conn->connect_error);
        }
    }
    
    /*
     * Initializes a new connection to the DB. Returns a mysqli connection.
     */
    public static function getConnection($user, $password){
        $conn = new Connection($user, $password);
        $conn->conn->query("START TRANSACTION");
        return $conn->conn;
    }
    
    public static function closeConnection($conn){
        $conn->query("COMMIT");
    }
}

function execute($conn, $strQuery){

    $result = $conn->query($strQuery);

    //If there's an error in the query then die
    if(!$result){
        die("Query: $strQuery, Error: ");
    }

    //If there is a mysqli_result then return it
    if($result instanceof mysqli_result){
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
    
$conn = Connection::getConnection('ima_user', 'fotbaltym9');

execute($conn,
<<<EOD
    UPDATE images
    SET saved = 1
    WHERE root = "{$_GET['root']}";
EOD
);
Connection::closeConnection($conn);