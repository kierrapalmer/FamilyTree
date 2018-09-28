<?php
require_once("db_config.php");

if(isset($_POST['loginUser'])){
    $id = null;
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);




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


<!--
<canvas id="myCanvas" width="700" height="700" style="border:1px solid #000000;"> Your browser doesn't support visualization of family trees
</canvas>
-->
tree
<!--
<div>
    <ul id="tree">

    </ul>
</div>
-->

<script src="scripts.js"></script>

</body>
</html>