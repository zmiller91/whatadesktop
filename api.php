<?php

require_once 'ApplicationAutoloader.php';

function requeueImg($oUser, $PostData, $oConn){
    
    //string status to int status, assume 0

    //store image for user
    $ImageTable = new ImageTable($oConn);
    $ImageTable->removeImageStatus(
            $oUser->getId(), 
            $PostData['root']);

    return;
}

function setStatus($oUser, $PostData, $oConn){
    
    $ImageTable = new ImageTable($oConn);
    $aImgIds = $ImageTable->getImgIds($PostData['root']);
    $iStatus = $PostData['status'];
    
    //store image for user
    foreach($aImgIds as $id){
        $ImageTable->setImageStatus(
            $oUser->getId(), 
            $PostData['root'], 
            $id['id'],
            $iStatus);
    }
    return;
}

function getSortedImages($oUser, $strSort, $oConn, $iStatus = null){
    
    $mUserId = $oUser->isLoggedIn() ? $oUser->getId() : null;
    
    //no images
    $ImageTable = new ImageTable($oConn);
    $aFileHashes = $ImageTable->getImageHashes($strSort, $mUserId);
    if(empty($aFileHashes)){
        return array();
    }
    
    //images found, now format them into the correct structure
    $aImages = $ImageTable->getImages($aFileHashes, $mUserId);
    $aRootIndex = array();  
    $oOut = array();

    foreach($aImages as $img){
        
        //set default status if provided
        if($iStatus){
            $img['status'] = $iStatus;
        }
        
        //if the root exists, then update it
        if(array_key_exists($img['root'], $aRootIndex)){
            $index = $aRootIndex[$img['root']];
            $oOut[$index][] = $img;
            
        //otherwise insert it
        }else{
            $oOut[] = array($img);
            $aRootIndex[$img['root']] = sizeof($oOut) - 1;
            
        }
    }

    return $oOut;
}
        
//create a connection and authenticate a user
$oConn = Connection::getConnection('ima_user', 'fotbaltym9');
$oUser = new User($oConn);
$oUser->authenticate();

//method is sort
if(!empty($_GET) 
    && !empty($_GET['method'] 
    && $_GET['method'] === 'sort' 
    &&!empty($_GET['sort']))){
    
    $aOut['images'] = getSortedImages($oUser, $_GET['sort'], $oConn);
    $aOut['user'] = $oUser->getUser();
    echo json_encode($aOut);
}

//update, save or requeue, user must be logged in
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $_POSTDATA = json_decode(file_get_contents('php://input'),true);
    
    //set status method, user must be logged in
    if($oUser->isLoggedIn()
        &&!empty($_POSTDATA['method']) 
        && $_POSTDATA['method'] === 'set_status'
        && !empty($_POSTDATA['status'])
        && !empty($_POSTDATA['root'])){
        
        setStatus($oUser, $_POSTDATA, $oConn);
    }
    
    //requeue method
    if($oUser->isLoggedIn()
        &&!empty($_POSTDATA['method']) 
        && $_POSTDATA['method'] === 'requeue'
        && !empty($_POSTDATA['root'])){
        
        requeueImg($oUser, $_POSTDATA, $oConn);
    }
    
    $oOut = [];
    $oOut['user'] = $oUser->getUser();
    echo json_encode($oOut);
}

Connection::closeConnection($oConn);