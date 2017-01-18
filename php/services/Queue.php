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
            $this->m_oError->add("A 'sort' parameter must be set");
            $bSuccess = false;
        }
        
        if(isset($this->m_aInput["filter"])) {
            if(!$this->isJson($this->m_aInput["filter"])) {
                $this->m_oError->add("A 'filter' must be valid json");
                $bSuccess = false;
            }
            else 
            {
                $aFilter = json_decode($this->m_aInput["filter"], true);
                $bSuccess = $bSuccess && 
                    $this->validateWidthHeight($aFilter, "width", array("min", "max")) && 
                    $this->validateWidthHeight($aFilter, "height", array("min", "max"));
                
                $bSuccess = $bSuccess && $this->validateAspectRatio($aFilter);
            }
        }
        
        if(!$bSuccess) {
            $this->setStatusCode(400);
        }
        return $bSuccess;
    }
    
    protected function get() 
    {
        $this->m_mData = array();
        $aFilter = isset($this->m_aInput["filter"]) ? 
                json_decode($this->m_aInput["filter"], true): array();
        
        $oImageTable = new ImageTable($this->m_oConnection);
        $aImages = $oImageTable->getImageQueue(
                $this->m_aInput["sort"], 
                100,
                $this->m_oUser->m_iUserId,
                $aFilter);

        if($oImageTable->m_oError->hasError())
        {
            $this->m_oError->addAll($oImageTable->m_oError->get());
            return false;
        }
        $this->m_mData = format_queue($aImages);
        return true;
    }

    private function validateAspectRatio($aFilter) {
        
        $bSuccess = true;
        if(isset($aFilter["ar"])) {
            // If width exists then height must too, and vice versa
            if(!empty($aFilter["ar"]["width"]) xor !empty($aFilter["ar"]["height"])) {
                $this->m_oError->add("ar width and height must exist in ar filter");
                $bSuccess = false;
            }
            else if(!empty($aFilter["ar"]["width"]) and !empty($aFilter["ar"]["height"]))
            {
                // Width and height must be numeric
                if(!is_numeric($aFilter["ar"]["width"]) && 
                        !is_numeric($aFilter["ar"]["height"])) {
                     $this->m_oError->add("ar width and height must be numeric");
                     $bSuccess = false;
                }
                else 
                {
                    // Width and height must be greater than 0
                    $iWidth = intval($aFilter["ar"]["width"]);
                    $iHeight = intval($aFilter["ar"]["height"]);
                    if($iWidth <= 0 || $iHeight <= 0) 
                    {
                        $this->m_oError->add("ar width and height must be greater than 0");
                        $bSuccess = false;
                    }
                }
            }
        }
        
        return $bSuccess;
    }
    
    private function validateWidthHeight($aFilter, $strDimension, $aKey) {
        
        $bSuccess = true;
        if(isset($aFilter[$strDimension])) {
            if(!is_array($aFilter[$strDimension])) {
                $this->m_oError->add($strDimension . " must be an array");
                $bSuccess = false;
            }
            
            foreach($aKey as $k) {
                if(isset($aFilter[$strDimension][$k]) && 
                        !is_numeric($aFilter[$strDimension][$k]) &&
                        intval($aFilter[$strDimension][$k]) < 0) {
                    $this->m_oError->add("'" . $k . "' must be a positive integer on '" . 
                            $strDimension . "' filter");
                    $bSuccess = false;
                }
            }
        }
        
        return $bSuccess;
    }
    
    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
   }
}
