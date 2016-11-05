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
    
    private function getNewSql($iUser = null){
        
        $strUserAnd = $iUser ? "AND img_status.user = $iUser" : "";
        
        //should be ordering by a date
        return
<<<EOD
        SELECT DISTINCT root FROM images
        LEFT JOIN img_status 
        ON images.id = img_status.img_id
        $strUserAnd
        WHERE img_status.img_id IS NULL
        ORDER BY id DESC
        LIMIT 100;
EOD;
    }
    
    private function getRandomSQL($iUser = null){
        
        $strUserAnd = $iUser ? "AND img_status.user = $iUser" : "";
        
        return
<<<EOD
        SELECT DISTINCT root FROM images
        LEFT JOIN img_status 
        ON images.id = img_status.img_id
        $strUserAnd
        WHERE img_status.img_id IS NULL
        ORDER BY RAND()
        LIMIT 100;
EOD;
    }
    
    private function getPopularSQL(){
        return
<<<EOD
        SELECT root FROM img_status
        LEFT JOIN images 
        ON  img_status.img_id = images.id
        GROUP BY images.root
        HAVING sum(img_status.status) > 0
        ORDER BY sum(img_status.status) DESC
        LIMIT 100;
EOD;
    }
    
    private function getUnPopularSQL(){
        return
<<<EOD
        SELECT root FROM img_status
        LEFT JOIN images 
        ON  img_status.img_id = images.id
        GROUP BY images.root
        HAVING sum(img_status.status) < 0
        ORDER BY sum(img_status.status) ASC
        LIMIT 100;
EOD;
    }
    
    private function getSavedSQL($iUser){
        return
<<<EOD
        SELECT root FROM images
        LEFT JOIN img_status 
        ON images.id = img_status.img_id
        WHERE img_status.user = $iUser
        AND img_status.status = 1;
EOD;
    }
    
    private function getDeletedSQL($iUser){
        return
<<<EOD
        SELECT root FROM images
        LEFT JOIN img_status 
        ON images.id = img_status.img_id
        WHERE img_status.user = $iUser
        AND img_status.status = -1;
EOD;
    }
    
    public function getImageHashes($strSortMethod, $iUser = null){

        $sql = "";
        switch($strSortMethod){

            case "new":
                $sql = $this->getNewSql($iUser);
                break;

            case "popular":
                $sql = $this->getPopularSQL();
                break;

            case "unpopular":
                $sql = $this->getUnPopularSQL();
                break;

            case "random":
                $sql = $this->getRandomSQL($iUser);
                break;

            case "saved":
                $sql = $this->getSavedSQL($iUser);
                break;

            case "deleted":
                $sql = $this->getDeletedSQL($iUser);
                break;
        }
        return $this->execute($sql);
    }
    
    public function getImageQueue($aFileHashes, $iUser = null){
        
        // set defaults...dont really like $strStatusSQL...
        $strJoinSQL = '';
        $strStatusSQL = '0 AS status';
        if(isset($iUser)){
            $strJoinSQL = 
<<<EOD
        LEFT JOIN img_status
        ON images.id = img_status.img_id
        AND img_status.user = $iUser                  
EOD;

            $strStatusSQL = 
<<<EOD
        IFNULL(status, 0) AS status                 
EOD;
        }
        
        $sqlIn = $this->generateIn('root', $aFileHashes);
        $sql =
<<<EOD
        SELECT id, path, width, height, root, $strStatusSQL
        FROM images
        $strJoinSQL
        WHERE root in
        {$sqlIn}
        ORDER BY root, width DESC, height DESC;
EOD;
        return $this->execute($sql);
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
