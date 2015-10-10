<?php

require_once 'ApplicationAutoloader.php';

if(!empty($_POST)){
    $oConn = Connection::getConnection('ima_user', 'fotbaltym9');
    $oUser = new User($oConn);
    $bSuccess = $oUser->logIn($_POST['username'], $_POST['password'], $_POST['persist']);
}