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
        return array(self::GET);
    }

    protected function authorize() 
    {
        // Must be logged in to use this service
        $this->m_oUser->authorize();
        return $this->m_oUser->m_bLoggedIn;
    }

    protected function validate() 
    {
        return true;
    }
    
    protected function get()
    {
        $ImageStatusTable = new ImageStatusTable($this->m_oConnection);
        $aSavedRoots = $ImageStatusTable->getSavedRoots($this->m_oUser->m_iUserId);
        $this->m_mData = $aSavedRoots;
    }
}
