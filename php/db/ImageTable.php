<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageTable
 *
 * @author zmiller
 */
class ImageTable extends BaseTable{
    
    public function __construct($conn) {
        parent::__construct($conn);
    }
    
    private function generateIn($strField, $aValues){
        $last = sizeof($aValues) - 1;
        $sql = '(';
        foreach($aValues as $index => $v){
            $criteria = isset($v[$strField]) ? $v[$strField] : null;
            $sql .= $criteria ? "'$criteria'" : '';
            $sql .= $criteria && $index != $last ? ',' : '';
        }
        $sql .= ')';
        return $sql;
    }
    
    private function getNewRoots($limit, $iUser = -1)
    {
        return
<<<EOD
            SELECT DISTINCT root 
            FROM images
            WHERE root NOT IN (
                    SELECT DISTINCT img_root
                    FROM img_status
                    WHERE status = -1
                    AND user = $iUser
            )
            ORDER BY id DESC
            LIMIT $limit;
EOD;
    }
    
    private function getRandomRoots($limit, $iUser = -1)
    {
        return
<<<EOD
            SELECT DISTINCT root 
            FROM images
            WHERE root NOT IN (
                    SELECT DISTINCT img_root
                    FROM img_status
                    WHERE status = -1
                    AND user = $iUser
            )
            ORDER BY RAND()
            LIMIT $limit;
EOD;
    }
    
    private function getPopularRoots($limit)
    {
        return
<<<EOD
            SELECT img_root as root
            FROM img_status
            GROUP BY img_root
            ORDER BY SUM(status) DESC
            limit $limit;
EOD;
    }
    
    private function getUnPopularRoots($limit)
    {
        return
<<<EOD
            SELECT img_root
            FROM img_status
            GROUP BY img_root
            ORDER BY SUM(status) ASC
            LIMIT $limit;
EOD;
    }
    
    private function getSavedRoots($iUser = -1){
        return
<<<EOD
            SELECT DISTINCT img_root as root
            FROM img_status
            WHERE status = 1
            AND user = $iUser;
EOD;
    }
    
    private function getDeletedRoots($iUser = -1){
        return
<<<EOD
            SELECT DISTINCT img_root as root
            FROM img_status
            WHERE status = -1
            AND user = $iUser;
EOD;
    }
    
    public function getRoots($strSortMethod, $limit, $iUser = -1)
    {
        $sql = "";
        switch($strSortMethod){

            case "new":
                $sql = $this->getNewRoots($limit, $iUser);
                break;

            case "popular":
                $sql = $this->getPopularRoots($limit);
                break;

            case "unpopular":
                $sql = $this->getUnPopularRoots($limit);
                break;

            case "random":
                $sql = $this->getRandomRoots($limit, $iUser);
                break;

            case "saved":
                $sql = $this->getSavedRoots($iUser);
                break;

            case "deleted":
                $sql = $this->getDeletedRoots($iUser);
                break;
            
            case "default":
                $sql = $this->getRandomRoots(100);
        }
        
        return $this->execute($sql);
    }
    
    public function getImageQueue($sort, $limit, $iUser = -1)
    {
        if($iUser == null)
        {
            $iUser = -1;
        }
        
        $roots = $this->getRoots($sort, $limit, $iUser);
        $rootList = $this->generateIn("root", $roots);
        $bRandomize = $sort != "saved" && $sort != "deleted";
        $strRandomSql = !$bRandomize ? "" :
<<<EOD
            ORDER BY RAND()
EOD;
        
        $sql = 
<<<EOD
            SELECT 
                    root, 
                    path, 
                    width, 
                    height, 
                    imgur_url, 
                    IFNULL(status, 0) as status
            FROM images
            LEFT JOIN img_status 
                ON id = img_id 
                AND user = $iUser
            WHERE root IN $rootList
                $strRandomSql;
EOD;
        $aOut = $this->execute($sql);
        return isset($aOut) ? $aOut : array();
    }
    
    public function setImageStatus($iUser, $strRoot, $iImgId, $iStatus){
        return $this->execute(
<<<EOD
        INSERT INTO img_status
        (user, img_root, img_id, status, updated_date)
        VALUES ($iUser, '$strRoot',$iImgId, $iStatus, NOW())
        ON DUPLICATE KEY UPDATE
        status = $iStatus,
        updated_date = NOW();
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
    
    public function getImage($root){
        return $this->execute(
<<<EOD
        SELECT * from images
        WHERE root = "$root"
        ORDER BY width DESC;
EOD
        );
    }
}
