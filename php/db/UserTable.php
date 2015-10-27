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
    FROM users
    WHERE username = '$strUser';
EOD;
        $oUser = $this->execute($strQuery);
        return !empty($oUser) ? $oUser[0] : false;
    }
    
    public function createUser($strUser, $strPass){
        $strQuery = 
<<<EOD
    INSERT IGNORE INTO users 
    (username, password, created_date)
    VALUES ("$strUser", "$strPass", NOW());
EOD;
        $this->execute($strQuery);
        return $this->selectLastInsertID();
    }

    public function createUserSession($strUser, $strToken, $strExpiration, $bPersist){
        $bPersist = $bPersist ? '1' : '0';
        $strQuery = 
<<<EOD
    INSERT IGNORE INTO user_sessions
    (user, token, expiration, persist, created_date, updated_date)
    VALUES ("$strUser", "$strToken", "$strExpiration", $bPersist, NOW(), NOW());
EOD;
        $this->execute($strQuery);
        return $this->selectLastInsertID();
    }    
    
    //returns false if session doesnt exist
    public function getUserSession($iUser, $iSelector){
        $strQuery = 
<<<EOD
    SELECT 
        users.username,
        user_sessions.id,
        user_sessions.user,
        user_sessions.token,
        user_sessions.expiration,
        user_sessions.persist
    FROM user_sessions
    LEFT JOIN users ON users.id = user_sessions.user
    WHERE user_sessions.id = $iSelector
    AND user = "$iUser";
EOD;
        $oUser = $this->execute($strQuery);
        return !empty($oUser) ? $oUser[0] : false;
    }
    
    public function updateUserSession($iUser, $iSelector, $strToken, $strExpiration){
        $strQuery = 
<<<EOD
    UPDATE user_sessions 
    SET token = "$strToken",
    expiration = "$strExpiration",
    updated_date = NOW()
    WHERE id = $iSelector
    AND user = $iUser;
EOD;
        $this->execute($strQuery);
    }
}
