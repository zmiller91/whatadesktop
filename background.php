<?php

class Connection {
    private $conn;
    
    private function __construct($user, $password) {
        $this->conn = new mysqli("localhost", $user, $password);
        $this->conn->select_db("backgrounds");
        if ($this->conn->connect_error){
            die("Connection failed because: " . $this->conn->connect_error);
        }
    }
    
    /*
     * Initializes a new connection to the DB. Returns a mysqli connection.
     */
    public static function getConnection($user, $password){
        $conn = new Connection($user, $password);
        $conn->conn->query("START TRANSACTION");
        return $conn->conn;
    }
    
    public static function closeConnection($conn){
        $conn->query("COMMIT");
    }
}

function generateIn($strField, $aValues){
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

function execute($conn, $strQuery){

    $result = $conn->query($strQuery);

    //If there's an error in the query then die
    if(!$result){
        die("Query: $strQuery, Error: ");
    }

    //If there is a mysqli_result then return it
    if($result instanceof mysqli_result){
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
    
$conn = Connection::getConnection('ima_user', 'fotbaltym9');
$aFileHashes = execute($conn,
<<<EOD
    SELECT DISTINCT root FROM images
    WHERE deleted = 0
    AND saved = 0
    ORDER BY RAND()
    LIMIT 100;
EOD
);

$sqlIn = generateIn('root', $aFileHashes);
$aImages = execute($conn,
<<<EOD
        SELECT id, path, width, height, root FROM IMAGES
        WHERE root in
        {$sqlIn}
        ORDER BY root, height DESC, width DESC
EOD
);

$aRootIndex = array();  
$aOut = array();

foreach($aImages as $img){
    $index = sizeof($aOut);
    $groupImg = array();
    
    if(array_key_exists($img['root'], $aRootIndex)){
        $index = $aRootIndex[$img['root']];
        $groupImg = $aOut[$index];
    }
    
    array_push($groupImg, $img);
    $aOut[$index] = $groupImg;
    $aRootIndex[$img['root']] = $index;
}

        
echo json_encode($aOut);