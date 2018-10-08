<?php
    require_once("db_config.php");

    if(isset($_POST['createUser'])){
        $id = null;
        $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
        $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
        $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);


        $query = "INSERT INTO user (password, email, firstName, lastName, id) 
                  VALUES(:password, :email, :firstName, :lastName, :id)";
        $result = $db_connection->prepare($query);
        if($result->execute([
            'password'      => $password,
            'email'         => $email,
            'firstName'     => $firstname,
            'lastName'      => $lastname,
            'id'            => null
        ])){
            header('Location: sign_in.php');
            die();
        }
        else{
            echo "There was an error creating your account. Please try again";
        }


    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create a Record</title>
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>

<body>

<br>
<div class="box">
    <h1 class="text-center">Create Account</h1>
    <form method="post" action="create_account.php">
	    <div class="form-group">
	    <small>Email Address: </small> <input class="form-control" type="email" name="email">
	    <small>Password:</small> <input class="form-control" type="password" name="password">
	    <small>First Name: </small> <input class="form-control" type="text" name="firstname">
	    <small>Last Name:</small> <input class="form-control" type="text" name="lastname">
	    <button type="submit" name="createUser" class="btn btn-success float-right">Create User</button>
	    </div>
	    <div class="line">
		    <span>or </span>
	    </div>
	    <br>
	    <a href="sign_in.php" class="create">Sign In</a></br>
    </form>
</div>


</body>


</html>

