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
    
    private function createFilter($aFilter) {
        if(!isset($aFilter)) {
            return "";
        }
        
        $aSqlFilter = array();
        
        // Width
        if(!empty($aFilter["width"])) {
            if(!empty($aFilter["width"]["min"])) {
                array_push($aSqlFilter, "AND width >= " . $aFilter["width"]["min"]);
            }
            if(!empty($aFilter["width"]["max"])) {
                array_push($aSqlFilter, "AND width <= " . $aFilter["width"]["max"]);
            }
        }
        
        // Height
        if(!empty($aFilter["height"])) {
            if(!empty($aFilter["height"]["min"])) {
                array_push($aSqlFilter, "AND height >= " . $aFilter["height"]["min"]);
            }
            if(!empty($aFilter["height"]["max"])) {
                array_push($aSqlFilter, "AND height <= " . $aFilter["height"]["max"]);
            }
        }
        
        // Aspect Ratio
        if(!empty($aFilter["ar"])) {
            if(!empty($aFilter["ar"]["width"]) && 
                    !empty($aFilter["ar"]["height"])) {
                
                $width =  $aFilter["ar"]["width"];
                $height =  $aFilter["ar"]["height"];
                $ar = $width / $height;
                array_push($aSqlFilter, "AND ROUND(width / height,1) = ROUND(" . $ar . ", 1)");
            }
        }
        
        return implode(" ", $aSqlFilter);
    }
    
    private function getNewRoots($limit, $iUser = -1, $strFilter = "")
    {
        return
<<<EOD
            SELECT DISTINCT root 
            FROM images
            WHERE root NOT IN (
                    SELECT DISTINCT img_root
                    FROM img_status
                    WHERE (status = -1
                    or status = 1)
                    AND user = $iUser
            )
                $strFilter
            ORDER BY id DESC
            LIMIT $limit;
EOD;
    }
    
    private function getRandomRoots($limit, $iUser = -1, $strFilter = "")
    {
        return
<<<EOD
            SELECT DISTINCT root 
            FROM images
            WHERE root NOT IN (
                    SELECT DISTINCT img_root
                    FROM img_status
                    WHERE (status = -1
                    OR status = 1)
                    AND user = $iUser
            )
            $strFilter
            ORDER BY RAND()
            LIMIT $limit;
EOD;
    }
    
    private function getPopularRoots($limit, $strFilter = "")
    {
        return
<<<EOD
            SELECT img_root as root
            FROM img_status
            LEFT JOIN images 
                ON img_status.img_root = images.root
            WHERE 1 = 1
            $strFilter
            GROUP BY img_root
            HAVING SUM(status) > 0
            ORDER BY SUM(status) DESC
            limit $limit;
EOD;
    }
    
    private function getUnPopularRoots($limit, $strFilter = "")
    {
        return
<<<EOD
            SELECT img_root as root
            FROM img_status
            LEFT JOIN images 
                ON img_status.img_root = images.root
            WHERE 1 = 1
            $strFilter
            GROUP BY img_root
            HAVING SUM(status) < 0
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
    
    public function getRoots($strSortMethod, $limit, $iUser = -1, $aFilter = null)
    {
        $strFilter = $this->createFilter($aFilter);
        $sql = "";
        switch($strSortMethod){

            case "new":
                $sql = $this->getNewRoots($limit, $iUser, $strFilter);
                break;

            case "popular":
                $sql = $this->getPopularRoots($limit, $strFilter);
                break;

            case "unpopular":
                $sql = $this->getUnPopularRoots($limit, $strFilter);
                break;

            case "random":
                $sql = $this->getRandomRoots($limit, $iUser, $strFilter);
                break;

            case "saved":
                $sql = $this->getSavedRoots($iUser);
                break;

            case "removed":
                $sql = $this->getDeletedRoots($iUser);
                break;
            
            case "default":
                $sql = $this->getRandomRoots($limit, $strFilter);
        }
        
        $roots = $this->execute($sql);
        if(!in_array($strSortMethod, array("saved", "deleted"))
                && sizeof($roots) < $limit)
        {
            $more = $this->execute($this->getRandomRoots(
                    $limit - sizeof($roots), $iUser, $strFilter));
            $roots = array_merge($roots, $more);
        }
        
        return $roots;
    }
    
    public function getImageQueue($sort, $limit, $iUser = -1, $aFilter = null)
    {
        if($iUser == null)
        {
            $iUser = -1;
        }
        
        $roots = $this->getRoots($sort, $limit, $iUser, $aFilter);
        if(empty($roots))
        {
            return array();
        }
        
        $rootList = $this->generateIn("root", $roots);
        $bRandomize = $sort != "saved" && $sort != "removed";
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
