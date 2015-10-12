<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserAuth
 *
 * @author zmiller
 */
class User {
    
    const ERR_INVALID_USERNAME = 'invalid_username';
    const ERR_INVALID_PASSWORD = 'invalid_password';
    const ERR_USERNAME_EXISTS = 'username_exists';
    
    private $oConn;
    private $bLoggedIn;
    private $strCookieIdentifier;
    private $strUser;
    private $aErrors;
    private $oUserTable;
    
    public function __construct($oConn) {
        $this->oConn = $oConn;
        $this->bLoggedIn = false;
        $this->strCookieIdentifier = "mine";
        $this->aErrors = [];
        $this->oUserTable = new UserTable($this->oConn);
    }
    
    public function getId(){
        return $this->strUser;
    }
    
    public function getErrors(){
        return $this->getErrors();
    }
    
    public function isLoggedIn(){
        return $this->bLoggedIn;
    }
    
    public function register($strUser, $strPass){
        
        //create user and login
        if(!$this->oUserTable->getUser($strUser)){
            $strHashedPass = $this->hashPassword($strPass);
            $this->oUserTable->createUser($strUser, $strHashedPass);
            $this->logIn($strUser, $strPass);
           
        //user exists
        }else{
            $this->aErrors = self::ERR_USERNAME_EXISTS;
            $this->bLoggedIn = false;
            return $this->bLoggedIn;
        }
    }
    
    public function logIn($strUser, $strPass, $bKeepLoggedIn = false){

        $strEncodedPass = $this->encode($strPass);
        $oUserCreds = $this->oUserTable->getUser($strUser);
        
        //user doesn't exist
        if(!$oUserCreds){
            array_push($this->aErrors, self::ERR_INVALID_USERNAME);
            $this->bLoggedIn = false;
            return $this->bLoggedIn;
        }
        
        $bVerified = password_verify($strEncodedPass, $oUserCreds['password']);     
        if($bVerified){
            
            //create new session and set cookie 
            $this->strUser = $oUserCreds['id'];
            $strToken = $this->generateToken();
            $strExpiration = $this->generateExiprationDate($bKeepLoggedIn);
            $strSelector = $this
                    ->oUserTable
                    ->createUserSession(
                            $this->strUser, 
                            $strToken, 
                            $strExpiration, 
                            $bKeepLoggedIn);
            
            $this->setCookie($this->strUser, $strSelector, $strToken);
            $this->bLoggedIn = true;
            return $this->bLoggedIn;
            
        //password doesn't match
        }else{
            array_push($this->aErrors, self::ERR_INVALID_PASSWORD);
            $this->bLoggedIn = false;
            return $this->bLoggedIn;
        }
    }
    
    public function logout() {
        unset($_COOKIE[$this->strCookieIdentifier]);
        $this->bLoggedIn = false;
        return $this->bLoggedIn;
    }

    public function authenticate(){
        
        //cookie must exist
        $strCookie = !empty($_COOKIE[$this->strCookieIdentifier]) 
                ? $_COOKIE[$this->strCookieIdentifier] 
                : '';
        if(empty($strCookie)){
            $this->bLoggedIn = false;
            return $this->bLoggedIn;
        }

        //all cookie fields must exist
        list($strUser, $strSelector, $strToken) = explode(':', $strCookie, 3);
        if(empty($strUser) || empty($strSelector) || empty($strToken)){
            $this->bLoggedIn = false;
            return $this->bLoggedIn;
        }
        
        //user session must exist
        $oUserSession = $this->oUserTable->getUserSession($strUser, $strSelector);
        if($oUserSession){
            
            //session expired
            if(date("Y-m-d H:i:s") > $oUserSession['expiration']){
                $this->bLoggedIn = false;
                return $this->bLoggedIn;
            }
            
            //authenticated, generate new token and set new cookie
            if($oUserSession['token'] === $strToken){
                
                $this->strUser = $strUser;
                $strNewToken = $this->generateToken();
                $strExpiration = $this->generateExiprationDate($oUserSession['persist'] == 1);
                $this->oUserTable->updateUserSession(
                        $strUser, 
                        $strSelector, 
                        $strNewToken, 
                        $strExpiration);
                
                $this->setCookie($strUser, $strSelector, $strNewToken);
                $this->bLoggedIn = true;
                return $this->bLoggedIn;
                
            //security violation. user and selector exists but the token has been
            //tampered with. delete everything.
            }else{
                $this->oUserTable->deleteAllSessions($strUser);
                unset($_COOKIE, $this->strCookieIdentifier);
                $this->bLoggedIn = false;
                return $this->bLoggedIn;
            }
        }
        
        //no sessions found
        $this->bLoggedIn = false;
        return $this->bLoggedIn;
    }
    
    private function createUser($strUser, $strPass){
        $strEncodedPass = $this->encode($strPass);
        $this->oUserTable->createUser($strUser, $strEncodedPass);
    }
    
    private function generateToken(){
        return bin2hex(openssl_random_pseudo_bytes(60));
    }
    
    private function setCookie($strUser, $strSelector, $strToken){
        $strCookie = "$strUser:$strSelector:$strToken";
        setcookie($this->strCookieIdentifier, $strCookie);
    }
    
    //return an token expiration date in MYSQL DATETIME format
    private function generateExiprationDate($bPersist){
         
        //2 weeks
        if($bPersist){
            return date("Y-m-d H:i:s", strtotime("+14 days"));
            
        //1 hour
        }else{
            return date("Y-m-d H:i:s", strtotime("+1 hour"));
        }
    }
    
    private function encode($strPass){
        return base64_encode(hash('sha256', $strPass, true));
    }
    
    private function hashPassword($strPass){
        $strSHA256  = $this->encode($strPass);
        return password_hash($strSHA256, PASSWORD_BCRYPT);
    }
}
