<?php

require_once "functions/utilities.php";

class Removed extends Service
{
    protected function allowableMethods() 
    {
        return array(self::GET);
    }

    protected function authorize() 
    {
        $this->m_oUser->authorize();
        $bLoggedIn = $this->m_oUser->m_bLoggedIn;
        if(!$bLoggedIn)
        {
            $this->setStatusCode(401);
            $this->m_oError->add("Please authenticate and try again.");
        }
        
        return $bLoggedIn;
    }

    protected function validate() 
    {
        return true;
    }
    
    protected function get()
    {
        $oImageTable = new ImageTable($this->m_oConnection);
        $aImages = $oImageTable->getImageQueue("removed", null, $this->m_oUser->m_iUserId);
        if($oImageTable->m_oError->hasError())
        {
            $this->m_oError->addAll($oImageTable->m_oError->get());
            return false;
        }
        
        $this->m_mData = format_queue($aImages);
        return true;
    }
}