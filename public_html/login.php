<?php

require_once 'ApplicationAutoloader.php';

$POST = json_decode(file_get_contents('php://input'),true);
if(!empty($POST) && $POST['method'] == 'register'){
    
    $oConn = Connection::getConnection('ima_user', 'fotbaltym9');
    $oUser = new User($oConn);
    $bSuccess = $oUser->register($POST['user']['name'], $POST['user']['pass']);
    Connection::closeConnection($oConn);
    echo json_encode($oUser->getUser());
}

if(!empty($POST) && $POST['method'] == 'login'){
    
    $oConn = Connection::getConnection('ima_user', 'fotbaltym9');
    $oUser = new User($oConn);
    $bSuccess = $oUser->login($POST['user']['name'], $POST['user']['pass']);
    Connection::closeConnection($oConn);
    echo json_encode($oUser->getUser());
}

if(!empty($POST) && $POST['method'] == 'logout'){
    
    $oConn = Connection::getConnection('ima_user', 'fotbaltym9');
    $oUser = new User($oConn);
    $bSuccess = $oUser->logout();
    Connection::closeConnection($oConn);
    echo json_encode($oUser->getUser());
}