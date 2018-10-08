<?php
session_start();
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

    require_once("db_config.php");
    require_once("node.php");

    /*--- User Account ---*/
    if(isset($_SESSION["uId"])) {
        $id = $_SESSION["uId"];
        $query = "SELECT * FROM user WHERE id = '{$id}'";
        $row = $db_connection->query($query)->fetch();
    }else{
        header('Location: sign_in.php');
        die();
    }

	/*--- Count how many nodes/children---*/
    $q = $db_connection->prepare("SELECT * FROM tree_node");
    $q->execute();
    $r = $q->fetchAll();
    $count = count($r);

	/*--- Select All Nodes and marriages---*/
	$results = $db_connection->query("SELECT * FROM tree_node t LEFT JOIN marriage_union m ON t.parentMarriageId = m.unionId ORDER BY t.generation, m.memberId");

	/*--- Add Node Post Submission ---*/
    if(isset($_POST['submitNode'])){
        $id = filter_var($_POST['id'], FILTER_SANITIZE_STRING);
        $actionType = filter_var($_POST['actionType'], FILTER_SANITIZE_STRING);

        if($actionType != "delete" && $actionType != "divorce") {
            $first = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
            $last = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
            $generation = filter_var($_POST['generation'], FILTER_SANITIZE_STRING);
        }

        switch($actionType){
	        case "root":
		        $node = new Node(null, $first, $last, $generation, null);
		        $node -> addRoot($db_connection);
		        break;
	        case "child":
		        $generation++;
		        $node = new Node(null, $first, $last, $generation, $id);                //set parentMarriageId to incoming id
		        $node -> addChild($db_connection);
		        break;
	        case "spouse":
		        $node = new Node($id, $first, $last, null, null);
		        $node -> changeSpouse($db_connection);
		        break;
	        case "name":
		        $node = new Node($id, $first, $last, $generation, null);
		        $node -> changeName($db_connection);
		        break;
	        case "delete":
		        $node = new Node($id, null, null, null, null);
		        $node -> deleteNode($db_connection);
		        break;
	        case "divorce":
		        $node = new Node($id, null, null, null, null);
		        $node -> divorceSpouse($db_connection);
		        break;
        }

    }

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Family Tree</title>
    <link rel="stylesheet" href="styles.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script>


	</script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Family Tree</a>
    <div class="navbar-nav">
    </div>
    <div class="navbar-nav ml-auto">
            <a class="nav-link" href="#">Welcome <?php echo $row["firstName"]?></a>
            <a class="nav-link" href="logout.php">Sign out</a>
    </div>

</nav>

<main id="main container">
<!--	Only show if there are no members in database-->
    <?php if($count == 0){?>
	<a data-toggle="modal"
	   data-act="root"
	   data-id="0"
	   data-gen = "0"
	   href="#modalActionForm"
	   id="root"
	   class="user_dialog btn">
		+ Add Root Member</a>
    <?php }	?>



    <table>
        <thead>
            <th>Id</th>
            <th>Name</th>
            <th>Partner</th>
            <th>Generation</th>
            <th>Parent 1 </th>
            <th>Parent 2</th>
            <th>Add Child</th>
            <th>Delete</th>
        </thead>
        <?php
	        foreach($results as $result) {	?>
	            <tr>
	                <td><?php echo $result['_id']?></td>
		            <td>
                        <?php echo $result['firstName'] . " " . $result['lastName']?>
			            <a data-toggle="modal"
			               data-act="name"
			               data-id="<?php echo $result['_id']?>"
			               data-gen = "<?php echo $result['generation']?>"
			               href="#modalActionForm" class="user_dialog">
				                <i class="far fa-edit"></i>
                        </a>
                    </td>
		            <td>
                        <?php echo $result['spouse']?>
			            <a data-toggle="modal"
			               data-act="spouse"
			               data-id="<?php echo $result['_id']?>"
			               data-gen = "<?php echo $result['generation']?>"
			               href="#modalActionForm" class="user_dialog">
				            <?php
					            if($result['spouse'] != null && $result['spouse'] != " ")
				                    {echo '<i class="far fa-edit"></i>';}
				                else{ echo '<i class="fas fa-plus"></i>';}?>
                        </a>

			            <a data-toggle="modal"
			               data-act="divorce"
			               data-id="<?php echo $result['_id']?>"
			               data-gen = "<?php echo $result['generation']?>"
			               href="#modalActionForm" class="user_dialog">
				            <?php if($result['spouse'] != null && $result['spouse'] != " " )
				                {echo '<i class="fas fa-times text-danger"></i>';}?>
			            </a><br />
                        <?php echo $result['divorcedSpouses']?>
                    </td>
	                <td>
                        <?php echo $result['generation']?>
                    </td>
	                <td>
                        <?php echo $result['member']?>
                    </td>
	                <td>
                        <?php echo $result['partner']?>
                    </td>
	                <td>
                        <a data-toggle="modal"
                           data-act="child"
		                    data-id="<?php echo $result['_id']?>"
		                    data-gen = "<?php echo $result['generation']?>"
                            href="#modalActionForm" class="user_dialog">Add Child</a>
                    </td>
                    <td class="text-center">
	                    <a data-toggle="modal"
	                       data-act="delete"
	                       data-id="<?php echo $result['_id']?>"
	                       data-gen = "<?php echo $result['generation']?>"
	                       href="#modalActionForm" class="user_dialog">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
	            </tr>
	        <?php } 	?>
    </table>
    <div id="addNode"></div>

</main>


<!--Add Node/Spouse Modal Javascript-->
<script>
    $(document).on("click", ".user_dialog", function () {
        let id = $(this).data('id');
        let actionType = $(this).data('act');
        let gen = $(this).data('gen');
        $(".modal-body #id").val( id );
        $(".modal-body #actionType").val( actionType );
        $(".modal-body #generation").val( gen );

		switch(actionType){
			case "child":
                $(".modal-content #modal-title").text( "Add a New Child" );
				break;
			case "spouse":
                $(".modal-content #modal-title").text( "Add a New Partner" );
				break;
            case "name":
                $(".modal-content #modal-title").text( "Change Name of Member" );
                break;
            case "root":
                $(".modal-content #modal-title").text( "Add First Member of Family Tree" );
                break;
            case "delete":
                onRemoveDelete();
                $( "#submit" ).attr( "id", "remove" );
                $(".modal-content #modal-title").text( "Remove Member" );
                $(".modal-body #deleteMessage").text( "Are you sure you would like to remove this member?" );
                break;
            case "divorce":
                onRemoveDelete();
                $( "#submit" ).attr( "id", "remove" );
                $(".modal-content #modal-title").text( "Remove Partner" );
                $(".modal-body #deleteMessage").text( "Are you sure you would like to remove this partner?" );
                break;
        }
    });

	function onRemoveDelete(){
        $(".modal-body #first").hide();
        $(".modal-body #last").hide();

        $(".modal-content #submit").val( "Remove" );
    }
</script>

<!--Add Node/Spouse Modal-->
<div class="modal fade" id="modalActionForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-center ">
				<h4 id="modal-title" class="modal-title w-100 font-weight-bold">Add Member</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<form action="index.php" method="post">
			<div class="modal-body">
				<div id="deleteMessage" class="text-center"></div>
				<div class="md-form" id="first">
					<label data-error="wrong" data-success="right" for="firstname">First Name</label>
					<input type="text" id="firstname" name="firstname" class="form-control validate">
				</div>
				<div class="md-form" id="last">
					<label data-error="wrong" data-success="right" for="lastname">Last Name</label>
					<input type="text" id="lastname" name="lastname" class="form-control validate">
				</div>

                <div class="md-form">
                    <input type="hidden" name="id" id="id">
                </div>
                <div class="md-form">
                    <input type="hidden" name="generation" id="generation">
                </div>
                <div class="md-form">
                    <input type="hidden" name="actionType" id="actionType">
                </div>

			</div>
			<div class="d-flex justify-content-center mb-2" id="modal-footer">
				<input type="button" name="cancel" id="cancel" value="Cancel" onclick="location.reload()" class="btn" />
				<input type="submit" id="submit" name="submitNode" class="btn">
			</div>
			</form>
		</div>
	</div>
</div>

</body>
</html>