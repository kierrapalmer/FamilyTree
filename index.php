<?php
require_once("db_config.php");


session_start();
if(isset($_SESSION["uId"])) {
    $id = $_SESSION["uId"];
    $query = "SELECT *
                  FROM user
                  WHERE id = '{$id}'";
    $row = $db_connection->query($query)->fetch();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Family</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Family Tree</a>
    <div class="navbar-nav">
            <a class="nav-link" href="" data-target="#myModal" data-toggle="modal"> + Add New Member</a>
    </div>
    <div class="navbar-nav ml-auto">
            <a class="nav-link" href="" data-target="#myModal" data-toggle="modal"><?php echo $row["firstName"]?></a>
            <a class="nav-link" href="" data-target="#myModal" data-toggle="modal">Sign out</a>
    </div>

</nav>
<!--
<div>
    <ul id="tree">

    </ul>
</div>
-->

<script src="scripts.js"></script>

</body>
</html>