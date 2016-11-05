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
        $mUserId = null;
    
        // Look up the images to return
        $ImageTable = new ImageTable($this->m_oConnection);
        $aFileHashes = $ImageTable->getImageHashes($this->m_aInput["sort"], 
                $mUserId);
        
        // Return if no images were found
        if(!empty($aFileHashes))
        {  
            $aOut = array();
            $aRootIndex = array();
            $aImages = $ImageTable->getImages($aFileHashes, $mUserId);

            // Group every image by their root
            foreach($aImages as $img)
            {
                if(!isset($aOut[$img['root']]))
                {
                    $aOut[$img['root']] = array();
                }
                
                array_push($aOut[$img['root']], $img);
            }
            
            // Sort every image according to their width
            foreach($aOut as $key => $value)
            {
                usort($value, function($a, $b)
                {
                    return strcmp($a["width"], $b["width"]);
                });
            }
            
            $this->m_mData = $aOut;
        }
        
        return true;
    }
}
