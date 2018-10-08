<?php
require_once("db_config.php");

if(isset($_POST['loginUser'])){
    $id = null;
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    $query = "SELECT *
                  FROM user
                  WHERE email = '{$email}' AND password = '{$password}'";
    $result = $db_connection->query($query)->fetch();

    if($result != null){
        session_start();
        $_SESSION['uId'] = $result['id'];
        header('Location: index.php');
        die();
    }
    else{
        echo "There was an error creating your account. <a href='sign_in.php'>Please try again</a>";
        header('Location: create_account.php');
        die();
    }


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
	<link href="https://fonts.googleapis.com/css?family=Lato|Montserrat" rel="stylesheet">

</head>
<body>

<div class="box">
<h1 class="text-center">Sign In</h1>
<form action="sign_in.php" method="post">
    <small>Email Address: </small> <input class="form-control" id="email" type="text" name="email">
	<small>Password:</small> <input class="form-control mb-3" type="password" name="password">
	<button type="submit" name="loginUser" class="btn btn-success float-right">Sign in</button>

	<div class="line">
		  <span>or </span>
	</div>
	<br>
    <a href="create_account.php" class="create pt-4 text-center">Create a new account</a>
</form>
</div>

<script src="scripts.js"></script>

</body>
</html>