<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageStatusTable
 *
 * @author zmiller
 */
class ImageStatusTable extends BaseTable
{
    public function __construct($conn) 
    {
        parent::__construct($conn);
    }
    
    public function getSavedRoots($iUserId = -1)
    {
        if($iUserId == null)
        {
            $iUserId = -1;
        }
        
        $strQuery = 
<<<EOD
            SELECT
                DISTINCT img_root AS root,
                status
            FROM img_status
            WHERE user = $iUserId;
EOD;
        return $this->execute($strQuery);
    }
}
