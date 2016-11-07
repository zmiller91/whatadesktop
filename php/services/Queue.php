<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of queue
 *
 * @author zmiller
 */
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
        // TODO: handle errors
        
        $this->m_mData = array();
        $ImageTable = new ImageTable($this->m_oConnection);
        $aImages = $ImageTable->getImageQueue(
                $this->m_aInput["sort"], 
                100,
                $this->m_oUser->m_iUserId);

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
