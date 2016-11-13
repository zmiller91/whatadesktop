<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageStatus
 *
 * @author zmiller
 */
class ImageStatus extends Service
{
    public function __construct($strMethod, $aInput) 
    {
        parent::__construct($strMethod, $aInput);
    }
    
    protected function allowableMethods() 
    {
        return array(self::GET, self::PUT);
    }

    protected function authorize() 
    {
        // Must be logged in to use this service
        $this->m_oUser->authorize();
        return $this->m_oUser->m_bLoggedIn;
    }

    protected function validate() 
    {
        if($_SERVER["REQUEST_METHOD"] === self::PUT)
        {
            return $this->validatePost();
        }
        
        return true;
    }
    
    protected function get()
    {
        $ImageStatusTable = new ImageStatusTable($this->m_oConnection);
        $aSavedRoots = $ImageStatusTable->getSavedRoots($this->m_oUser->m_iUserId);
        if($ImageStatusTable->m_oError->hasError())
        {
            $this->m_oError->addAll($ImageStatusTable->m_oError->get());
            return false;
        }
        
        $this->m_mData = $aSavedRoots;
        return true;
    }
    
    protected function put()
    {
        $oImageStatusTable = new ImageStatusTable($this->m_oConnection);
        if($this->m_aInput["status"] === "reset")
        {
            $oImageStatusTable->removeImageStatus(
                    $this->m_oUser->m_iUserId, 
                    $this->m_aInput["root"]);
        }
        else
        {
            $iStatus = $this->m_aInput["status"] == "saved" ? 1 : -1;
            $oImageStatusTable->setImageStatus(
                    $this->m_oUser->m_iUserId, 
                    $this->m_aInput["root"], 
                    $iStatus);
        }
        
        if($oImageStatusTable->m_oError->hasError())
        {
            $this->m_oError->add($oImageStatusTable->m_oError->get());
        }
        
        $this->m_mData = array(
                "root" => $this->m_aInput["root"], 
                "status" => $this->m_aInput["status"]);
        
        return true;
    }
    
    private function validatePost()
    {
        $bSuccess = true;
        $root = $this->m_aInput["root"];
        $status = $this->m_aInput["status"];

        if(empty($root))
        {
            $this->setStatusCode(400);
            $this->m_oError->add("A 'root' parameter must be specified.");
            $bSuccess = false;
        }

        if(empty($status) || !in_array($status, array("deleted", "reset", "saved")))
        {
            $this->setStatusCode(400);
            $this->m_oError->add("A 'status' parameter must be specified and "
                    . "it's value must be in ['deleted', 'reset', 'saved']");
            $bSuccess = false;
        }

        return $bSuccess;
    }
    
}
