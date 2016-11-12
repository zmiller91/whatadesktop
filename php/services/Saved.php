<?php

class Saved extends Service
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
        $aImages = $oImageTable->getImageQueue("saved", null, $this->m_oUser->m_iUserId);
        if($oImageTable->m_oError->hasError())
        {
            $this->m_oError->addAll($oImageTable->m_oError->get());
            return false;
        }
        
        // Group every image by their root
        $aOut = array();
        foreach($aImages as $img)
        {
            if(!isset($aOut[$img['root']]))
            {
                $aOut[$img['root']] = array();
            }

            array_push($aOut[$img['root']], $img);
        }

        // Sort every image according to their width
        foreach($aOut as &$value)
        {
            usort($value, function($a, $b)
            {
                $a = $a["width"];
                $b = $b["width"];
                return ($a > $b) ? -1 : (($a < $b) ? 1 : 0);
                
            });
        }

        $this->m_mData = $aOut;
        return true;
    }
}
