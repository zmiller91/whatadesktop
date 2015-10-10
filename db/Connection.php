<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Connection
 *
 * @author Miller
 */
class Connection {
    private $conn;
    
    private function __construct($user, $password) {
        $this->conn = new mysqli("localhost", $user, $password);
        $this->conn->select_db("website");
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
