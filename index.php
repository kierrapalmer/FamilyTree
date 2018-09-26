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
<h1>Sign In</h1>
<form action="create_account.php" method="post">
    Username: <input type="text" name="username"></br>
    Password: <input type="text" name="password"></br>
    <a href="create_account.php">Create a new account</a></br>
    <a href="tree.php" class="btn btn-success">Login</a>
</form>
<!--
<div>
    <ul id="tree">

    </ul>
</div>
-->

<script src="scripts.js"></script>

</body>
</html>