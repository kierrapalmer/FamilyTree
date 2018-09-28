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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>

<body>

<br>
<div class="container">
    <h1>Add a new user</h1>
    <form method="post" action="create_account.php">
        <div class="form-group row">
            <label for="author" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="email" name="email" value="">
            </div>
        </div>
        <div class="form-group row">
            <label for="genre" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="password" name="password" value="">
            </div>
        </div>
        <div class="form-group row">
            <label for="height" class="col-sm-2 col-form-label">First Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="firstname" name="firstname" value="">
            </div>
        </div>
        <div class="form-group row">
            <label for="publisher" class="col-sm-2 col-form-label">Last Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="lastname" name="lastname" value="">
            </div>
        </div>

        <button type="submit" name="createUser" class="btn btn-success">Create User</button>

    </form>
</div>


</body>


</html>

