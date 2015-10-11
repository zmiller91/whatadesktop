<?php

require_once 'ApplicationAutoloader.php';

$POST = json_decode(file_get_contents('php://input'),true);
if(!empty($POST) && $POST['method'] == 'register'){
    
    $oConn = Connection::getConnection('ima_user', 'fotbaltym9');
    $oUser = new User($oConn);
    $bSuccess = $oUser->register($POST['user']['name'], $POST['user']['pass']);
    Connection::closeConnection($oConn);
}