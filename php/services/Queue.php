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

            foreach($aImages as $img)
            {
                // If the root exists, then update it
                if(array_key_exists($img['root'], $aRootIndex)){
                    $index = $aRootIndex[$img['root']];
                    $aOut[$index][] = $img;

                // Otherwise insert it
                }else{
                    $aOut[] = array($img);
                    $aRootIndex[$img['root']] = sizeof($aOut) - 1;

                }
            }
            
            $this->m_mData = $aOut;
        }
        
        return true;
    }
}
