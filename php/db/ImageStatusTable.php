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
    
    public function setImageStatus($iUser, $strRoot, $iStatus)
    {
        // Remove the images if they exist
        $this->removeImageStatus($iUser, $strRoot);
        $aIds = $this->execute(
<<<EOD
        SELECT id 
        FROM images
        WHERE root = "$strRoot";
EOD
        );
        
        // Exit if there's nothing to do
        if(empty($aIds))
        {
            return true;
        }
        
        // Generate a value list
        $aValues = [];
        foreach($aIds as $id)
        {
            $id = $id["id"];
            array_push($aValues, "($iUser, '$strRoot', $id, $iStatus, NOW())");
        }
        
        // Add the new values
        $strValues = implode(", ", $aValues);
        return $this->execute(
<<<EOD
        INSERT INTO img_status
        (user, img_root, img_id, status, updated_date)
        VALUES $strValues;
EOD
        );
    }
    
    public function removeImageStatus($iUser, $strRoot){
        return $this->execute(
<<<EOD
        DELETE FROM img_status
        WHERE user = $iUser
        AND img_root = "$strRoot";
EOD
        );
    }
}
