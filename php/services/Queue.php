<?php

require_once "functions/utilities.php";

class Queue extends Service
{
    protected function allowableMethods() 
    {
        return array(self::GET);
    }
    
    protected function authorize() 
    {
        $this->m_oUser->authorize();
        return true;
    }

    protected function validate() 
    {
        $bSuccess = true;
        if(empty($this->m_aInput["sort"]))
        {
            $this->setStatusCode(400);
            $this->m_oError->add("A 'sort' parameter must be set");
            $bSuccess = false;
        }
        
        return $bSuccess;
    }

    protected function get() 
    {
        $this->m_mData = array();
        $oImageTable = new ImageTable($this->m_oConnection);
        $aImages = $oImageTable->getImageQueue(
                $this->m_aInput["sort"], 
                100,
                $this->m_oUser->m_iUserId);

        if($oImageTable->m_oError->hasError())
        {
            $this->m_oError->addAll($oImageTable->m_oError->get());
            return false;
        }
        $this->m_mData = format_queue($aImages);
        return true;
    }
}
