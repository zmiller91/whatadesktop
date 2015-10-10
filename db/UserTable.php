<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserTable
 *
 * @author zmiller
 */
class UserTable extends BaseTable{
    
    public function __construct($conn) {
        parent::__construct($conn);
    }
    
    public function deleteAllSessions($iUser){
        $strQuery = 
<<<EOD
    DELETE FROM user_sessions 
    WHERE user = $iUser;
EOD;
        $this->execute($strQuery);
    }
    
    //return false if user doesnt exist
    public function getUser($strUser){
        $strQuery = 
<<<EOD
    SELECT * 
    FROM user
    WHERE user_name = $strUser;
EOD;
        $oUser = $this->execute($strQuery)[0];
        return !empty($oUser) ? $oUser : false;
    }
    
    public function createUser($strUser, $strPass){
        $strQuery = 
<<<EOD
    INSERT IGNORE INTO user 
    (username, password, created_date)
    VALUES ($strUser, $strPass, NOW());
EOD;
        $this->execute($strQuery);
        return $this->selectLastInsertID();
    }

    public function createUserSession($strUser, $strToken, $strExpiration){
        $strQuery = 
<<<EOD
    INSERT IGNORE INTO user 
    (user, selector, created_date)
    VALUES ($strUser, $strPass, NOW());
EOD;
        $this->execute($strQuery);
        return $this->selectLastInsertID();
    }    
    
    //returns false if session doesnt exist
    public function getUserSession($iUser, $iSelector){
        $strQuery = 
<<<EOD
    SELECT * 
    FROM user_sessions
    WHERE id = $iSelector
    AND user = $iUser;
EOD;
        $oUser = $this->execute($strQuery)[0];
        return !empty($oUser) ? $oUser : false;
    }
    
    public function updateUserSession($iUser, $iSelector, $strToken, $strExpiration){
        $strQuery = 
<<<EOD
    UPDATE user_sessions 
    SET token = $strToken
    expiration = $strExpiration
    WHERE id = $iSelector
    AND user = $iUser;
EOD;
        $this->execute($strQuery);
    }
}
