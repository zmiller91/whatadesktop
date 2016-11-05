<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Image
 *
 * @author zmiller
 */
class Image extends Service
{

    protected function allowableMethods() 
    {
        return array(self::GET);
    }
    
    protected function authorize() 
    {
        return true;
    }
    
    protected function validate() 
    {
        $bSuccess = true;
        if(empty($this->m_aInput["id"]))
        {
            $this->setStatusCode(400);
            $this->m_oError->add("An 'id' parameter must be set");
            $bSuccess = false;
        }
        
        return $bSuccess;
    }

    protected function get() 
    {
        $oImageTable = new ImageTable($this->m_oConnection);
        $aImages = $oImageTable->getImage($this->m_aInput["id"]);
        if(isset($aImages[0]["root"]))
        {
            $this->m_mData = array($aImages[0]["root"] => $aImages);
        }
        return true;
    }
}
