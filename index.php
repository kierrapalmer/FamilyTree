<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

    require_once("db_config.php");

    /*--- User Account ---*/
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

    /*--- Select All Nodes and marriages---*/
    $q = $db_connection->prepare("SELECT * FROM tree_node");
    $q->execute();
    $r = $q->fetchAll();
    $count = count($r);
    $results = $db_connection->query("SELECT * FROM tree_node t LEFT JOIN marriage_union m ON t.parentMarriageId = m.unionId ORDER BY t._id, m.member");




/*--- Add Node Post Submission ---*/
    if(isset($_POST['submitNode'])){
        $id = filter_var($_POST['id'], FILTER_SANITIZE_STRING);
        $actionType = filter_var($_POST['actionType'], FILTER_SANITIZE_STRING);

        if($actionType != "delete") {
            $first = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
            $last = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
            $generation = filter_var($_POST['generation'], FILTER_SANITIZE_STRING);
        }

        if($actionType == "spouse") {                                                   //Add/Change Spouse
            $node = new Node($id, $first, $last, null, null);
            $node -> changeSpouse($db_connection);
        }
        elseif ($actionType == "child"){                                                //Add Node
        	$generation++;
            $node = new Node(null, $first, $last, $generation, $id);                //set parentMarriageId to incoming id
            $node -> addNode($db_connection);
        }
        elseif($actionType == "root"){                                                  //Add Root Node
	        $node = new Node(null, $first, $last, $generation, null);
	        $node -> addNode($db_connection);
        }
        elseif($actionType == "name"){                                                  //Change Name
	        $node = new Node($id, $first, $last, $generation, null);
	        $node -> changeName($db_connection);
        }
        elseif($actionType == "delete"){                                                 //Delete Node
            $node = new Node($id, null, null, null, null);
            $node -> deleteNode($db_connection);
        }

    }


    /*--- Node Class ---*/
    class Node {
	    public $id;
	    public $first;
	    public $last;
	    public $generation;
	    public $parentId;

	    public function __construct($id, $first, $last, $generation, $parentId){
            $this->id = $id;
            $this->first = $first;
			$this->last = $last;
			$this->generation = $generation;
			$this->parentId = $parentId;
		}

		public function addNode($db_connection){
            $results = $db_connection->query("SELECT * FROM marriage_union WHERE memberId = {$this->parentId}")->fetch();     //get marriage union id for childs parent
            $parentMarriageId = $results['unionId'] . "";

            $query = "INSERT INTO tree_node (_id, firstName, lastName, generation, parentMarriageId)
                  VALUES(:_id, :firstName, :lastName, :generation, :parentMarriageId)";

            try{
                $result = $db_connection->prepare($query);
                if($result->execute([
                    '_id'           => $this->id,
                    'firstName'     => $this->first,
                    'lastName'      => $this->last,
                    'generation'    => $this->generation,
                    'parentMarriageId' =>  $parentMarriageId
                ])){
                    header('Location: index.php');
                    die();
                }
                else
                    echo "There was an error adding the member. Please try again";
            }
            catch (Exception $ex){
                echo "Error adding member." . $ex;
            }
        }

        public function changeSpouse($db_connection){
	        $results = $db_connection->query("SELECT firstName, lastName FROM tree_node WHERE _id = {$this->id}")->fetch();
            $member = $results['firstName'] . " " . $results['lastName'];
	        $spouse = $this->first . " " . $this->last;

	        $addSpouseQuery = "UPDATE tree_node
                    SET spouse = :spouse
                    WHERE _id = :_id";

            $addMarriageQuery = "INSERT INTO marriage_union(unionId, member, spouse)
						VALUES	(:_id, :member, :spouse)";

            try{
                $addSpouseResult = $db_connection->prepare($addSpouseQuery);
	            $addMarriageResult = $db_connection->prepare($addMarriageQuery);
                if(
	                $addSpouseResult->execute([
	                    '_id'           => $this->id,
	                    'spouse'     => $spouse,
                ]) AND
	                $addMarriageResult->execute([
		                '_id'           => null,
		                'member'    =>  $member,
		                'spouse'     => $spouse,
                ])
                ){
                    header('Location: index.php');
                    die();
                }
                else
                    echo "There was an error adding the spouse. Please try again";
            }
            catch (Exception $ex){
                echo "Error adding spouse." . $ex;
            }
        }

	    public function changeName($db_connection){
		    $query = "UPDATE tree_node 
                    SET firstName = :first,
                    lastName = :last
                    WHERE _id = :_id";

		    try{
			    $result = $db_connection->prepare($query);
			    if($result->execute([
				    '_id'           => $this->id,
				    'first'     => $this->first,
				    'last'     => $this->last,
			    ])){
				    header('Location: index.php');
				    die();
			    }
			    else
				    echo "There was an error changing the name. Please try again";
		    }
		    catch (Exception $ex){
			    echo "Error changing name." . $ex;
		    }
	    }

        public function deleteNode($db_connection){

            $query = "DELETE FROM tree_node 
                    WHERE _id = :_id";

            try{
                $result = $db_connection->prepare($query);
                if($result->execute([
                    '_id'           => $this->id,
                ])){
                    header('Location: index.php');
                    die();
                }
                else
                    echo "There was an error changing the name. Please try again";
            }
            catch (Exception $ex){
                echo "Error changing name." . $ex;
            }
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
		function submitAction(id, actionType, gen=null) {
		    var f = document.createElement("form");
            f.setAttribute('method',"post");
            f.setAttribute('action',"index.php");

            var i = document.createElement("input");
            i.setAttribute('type',"hidden");
            i.setAttribute('name',"id");
            i.setAttribute('value', id.toString());

            var node = document.createElement("input");
            node.setAttribute('type',"hidden");
            node.setAttribute('name',"actionType");
            node.setAttribute('value', actionType.toString());

            var generation = document.createElement("input");
            generation.setAttribute('type', "hidden");
            generation.setAttribute('name', "generation");
            if(gen != null) {
                generation.setAttribute('value', gen.toString());
            }


            if(actionType != "delete") {
                var first = document.createElement("input");
                first.setAttribute('type', "text");
                first.setAttribute('name', "firstname");

                var last = document.createElement("input");
                last.setAttribute('type', "text");
                last.setAttribute('name', "lastname");

                var s = document.createElement("input");
                s.setAttribute('type',"submit");
                s.setAttribute('name',"submitNode");
                s.setAttribute('value',"Submit");

                f.appendChild(first);
                f.appendChild(last);
                f.appendChild(s);
            }
            else{
                var s = document.createElement("input");
                s.setAttribute('type',"submit");
                s.setAttribute('name',"submitNode");
                s.setAttribute('value',"Submit");
                f.appendChild(s);
            }

            f.appendChild(i);
            f.appendChild(node);
            f.appendChild(generation);
            document.getElementById("addNode").appendChild(f);

            if(actionType == "delete"){
                var conf = confirm("Are you sure you want to delete this member?");
                if (conf == true) {
                    s.click();
                }
            }
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

<main id="main">
    <?php if($count == 0){?>
        <button class="btn root" id="addRootBtn" onclick="addNode(0, 0, 'root')"> + Add Root Member</button>
    <?php }	?>



    <table border="1">
        <thead>
            <td>Id</td>
            <td>Name</td>
            <td>Spouse</td>
            <td>Generation</td>
            <td>Parent 1 </td>
            <td>Parent 2</td>
            <td>Add Spouse</td>
            <td>Add Child</td>
            <td>Delete</td>
        </thead>
        <?php
	        foreach($results as $result) {	?>
	            <tr>
	                <td><?php echo $result['_id']?></td>
		            <td>
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'name', <?php echo $result['generation']?>)" >
				            <?php echo $result['firstName'] . " " . $result['lastName']?>
                        </a>
                    </td>
		            <td>
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'spouse', <?php echo $result['generation']?>)" >
                            <?php if($result['spouse'] != null){echo $result['spouse'];} else{ echo "-";}?>
                        </a>
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
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'spouse')">
			                Add Spouse
                        </a>
                    </td>
	                <td>
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'child', <?php echo $result['generation']?>)">
			                Add Child
                        </a>
                    </td>
                    <td class="text-center">
                        <a class="text-secondary" href="#" onclick="submitAction(<?php echo $result['_id']?>, 'delete')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
	            </tr>
	        <?php } 	?>
    </table>
    <div id="addNode"></div>

</main>
<!--
<div>
    <ul id="tree">

    </ul>
</div>


<script src="scripts.js"></script>


<a href="" class="btn btn-default btn-rounded mb-4" data-toggle="modal" data-target="#modalRegisterForm">Launch Modal Register Form</a>

<!--Add Node/Spouse Modal
<div class="modal fade" id="modalRegisterForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header text-center">
				<h4 class="modal-title w-100 font-weight-bold">Add Member</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<form action="index.php" method="post">
			<div class="modal-body">
				<div class="md-form">
					<label data-error="wrong" data-success="right" for="firstname">First Name</label>
					<input type="text" id="firstname" class="form-control validate">
				</div>
				<div class="md-form">
					<label data-error="wrong" data-success="right" for="lastname">Last Name</label>
					<input type="text" id="lastname" class="form-control validate">
				</div>

			</div>
			<div class="modal-footer d-flex justify-content-center">
				<input type="submit" class="btn btn-deep-orange">
			</div>
			</form>
		</div>
	</div>
</div>
-->
</body>
</html>