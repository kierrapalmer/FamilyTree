<?php
$db_host = 'localhost';
$db_name = 'familytree';
$db_username= 'kpalm';
$db_password = 'cs3620';

$dsn = "mysql:host=$db_host;dbname=$db_name;";
try {
    $db_connection = new PDO($dsn, $db_username, $db_password);
}catch(Exception $e){
    echo "Uh oh speghetti-o. Error = ". $e->getMessage();
}