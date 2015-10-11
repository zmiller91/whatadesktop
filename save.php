<?php
require_once 'ApplicationAutoloader.php';
    
$conn = Connection::getConnection('ima_user', 'fotbaltym9');

$user = new User($conn);
$user->authenticate();

$BaseTable = new BaseTable($conn);
$BaseTable->execute(
<<<EOD
    UPDATE images
    SET saved = 1,
    deleted = 0
    WHERE root = "{$_GET['root']}";
EOD
);
Connection::closeConnection($conn);