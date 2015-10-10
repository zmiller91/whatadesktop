<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseClass
 *
 * @author Miller
 */
class BaseTable {

    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /*
     * Executes a query. Returns array of associative arrays if any 
     * mysqli_results exist.
     */
    public function execute($strQuery){

        $result = $this->conn->query($strQuery);
        
        //If there's an error in the query then die
        if(!$result){
            die("Query: $strQuery, Error: ");
        }
        
        //If there is a mysqli_result then return it
        if($result instanceof mysqli_result){
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }
    
    /*
     * Returns ID of last insert
     */
    public function selectLastInsertID(){
        $result = $this->execute("SELECT LAST_INSERT_ID() as 'id';");
        return $result[0]['id'];
    }
}
