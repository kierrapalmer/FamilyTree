<?php
require_once("db_config.php");
$rootMem = false;

session_start();
if(isset($_SESSION["uId"])) {
    $id = $_SESSION["uId"];
    $query = "SELECT *
                  FROM user
                  WHERE id = '{$id}'";
    $row = $db_connection->query($query)->fetch();
}else{
	header('Location: sign_in.php');
	die();
}


	if(isset($_POST['submitMember'])){
		$id = null;
		echo "<br><br><br><br><br><br><br><br><br><br><br><br>fjalk";

//		$query = "INSERT INTO node (password, email, firstName, lastName, id)
//                  VALUES(:password, :email, :firstName, :lastName, :id)";
//		$result = $db_connection->prepare($query);
//		if($result->execute([
//			'password'      => $password,
//			'email'         => $email,
//			'firstName'     => $firstname,
//			'lastName'      => $lastname,
//			'id'            => null
//		])){
//			header('Location: sign_in.php');
//			die();
//		}
//		else{
//			echo "There was an error creating your account. Please try again";



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

	<script>
		function addRoot() {
            $.ajax({
                url: 'add_member.php',
                type: 'GET',
                data: {
                    'numberOfWords': 10
                },
                success: function (data) {
                    $('#addRoot').html(data);
                },
                error: function (request, error) {
                    alert("Request: " + JSON.stringify(request) + error);
                }
            });
        }


	</script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Family Tree</a>
    <div class="navbar-nav">
    </div>
    <div class="navbar-nav ml-auto">
            <a class="nav-link" href="" data-target="#myModal" data-toggle="modal">Hello <?php echo $row["firstName"]?></a>
            <a class="nav-link" href="" data-target="#myModal" data-toggle="modal">Sign out</a>
    </div>

</nav>

<button class="btn root" onclick="addRoot()"> + Add Root Member</button>
<div id="addRoot"></div>



<!--
<div>
    <ul id="tree">

    </ul>
</div>
-->

<script src="scripts.js"></script>

</body>
</html>