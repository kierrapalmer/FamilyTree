<!--TODO:
Try for bootstrap modals: https://stackoverflow.com/questions/47544251/how-to-pass-id-to-the-bootstrap-modal
Add support for single parents
Finish styles
-->
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
        elseif($actionType == "divorce"){
	        $node = new Node($id, null, null, null, null);
	        $node -> divorceSpouse($db_connection);
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
            $results = $db_connection->query("SELECT * FROM marriage_union WHERE memberId = {$this->parentId}  AND isActive = 1")->fetch();     //get marriage union id for childs parent
            if($results['unionId'] != null)
                $parentMarriageId = $results['unionId'] . "";
            else
                $parentMarriageId = null;

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
	        $results = $db_connection->query("SELECT _id, firstName, lastName FROM tree_node WHERE _id = {$this->id}")->fetch();
            $member = $results['firstName'] . " " . $results['lastName'];
	        $spouse = $this->first . " " . $this->last;

	        $addSpouseQuery = "UPDATE tree_node
                    SET spouse = :spouse
                    WHERE _id = :_id";

            $addMarriageQuery = "INSERT INTO marriage_union(unionId, member, memberId, partner, isActive)
						VALUES	(:_id, :member, :memberId, :partner, 1)";

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
		                'memberId'  => $results['_id'],
		                'partner'     => $spouse,
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
	        $name = $this->first . " " . $this->last;
		    $query = "UPDATE tree_node 
                    SET firstName = :first,
                    lastName = :last
                    WHERE _id = :_id;
                    
                    UPDATE marriage_union
                    SET member = :member
                    WHERE memberId = :_id";

		    try{
			    $result = $db_connection->prepare($query);
			    if($result->execute([
				    '_id'           => $this->id,
				    'first'     => $this->first,
				    'last'     => $this->last,
                    'member'    => $name
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
                    echo "There was an error deleting the member. Please try again";
            }
            catch (Exception $ex){
                echo "Error deleting member." . $ex;
            }
        }

        public function divorceSpouse($db_connection)
        {
            $results = $db_connection->query("SELECT spouse, divorcedSpouses FROM tree_node WHERE _id = {$this->id}")->fetch();
            $spouse = $results['spouse'];
            $divorcedSpouses = $results['divorcedSpouses'];
            $divorcedSpouses = $divorcedSpouses == null ? $spouse . "(d)" : $divorcedSpouses . ", " . $spouse . "(d)";

            $query = "UPDATE tree_node
                    SET divorcedSpouses = :divorcedSpouses,
                    spouse = null
                    WHERE _id = :_id;
                    
                    UPDATE marriage_union        
                    SET isActive = 0
                    WHERE memberId = :_id"; //sets all specified member marriage union to inactive

            try {
                $result = $db_connection->prepare($query);
                if ($result->execute([
                    '_id' => $this->id,
                    'divorcedSpouses' => $divorcedSpouses,
                ])) {
                    header('Location: index.php');
                    die();
                } else
                    echo "There was an error divorcing the partner. Please try again";
            } catch (Exception $ex) {
                echo "Error divorcing partner." . $ex;
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
        var f = document.createElement("form");
        f.setAttribute('method',"post");
        f.setAttribute('action',"index.php");

        function submitAction(id, actionType, gen) {
           addCommonFields(id, actionType, gen);

            var l;
            if(actionType=="child"){
                l = displayLabel("Add a New Child", "h2");
            }
            else if(actionType=="spouse"){
                l = displayLabel("Add a New Partner", "h2");
            }
            else if(actionType=="name"){
                l = displayLabel("Change a Family Member Name", "h2");
            }
            else{
                l = displayLabel("Add", "h2");
            }

            var first = document.createElement("input");
            first.setAttribute('type', "text");
            first.setAttribute('name', "firstname");
            var firstLabel = displayLabel("First Name", "p");

            var last = document.createElement("input");
            last.setAttribute('type', "text");
            last.setAttribute('name', "lastname");
            var lastLabel = displayLabel("Last Name", "p");

            var s = document.createElement("input");
            s.setAttribute('type',"submit");
            s.setAttribute('name',"submitNode");
            s.setAttribute('value',"Submit");

            f.appendChild(l);
            f.appendChild(firstLabel);
            f.appendChild(first);
            f.appendChild(lastLabel);
            f.appendChild(last);
            f.appendChild(s);

            //Add form to page
            document.getElementById("addNode").appendChild(f);
        }

        function submitRemoveAction(id, actionType){
            addCommonFields(id, actionType);

            var s = document.createElement("input");
            s.setAttribute('type',"submit");
            s.setAttribute('name',"submitNode");
            s.setAttribute('value',"Submit");
            s.style.display= 'none';
            f.appendChild(s);
            document.getElementById("addNode").appendChild(f);

            if(actionType == "delete"){
                var conf = confirm("Are you sure you want to delete this member?");
                if (conf == true) {
                    s.click();
                }
            }
            else if(actionType == "divorce"){
                var conf = confirm("Are you sure you want to mark this partner as divorced?");
                if (conf == true) {
                    s.click();
                }
            }
        }

        function displayLabel(label, size){
            var l = document.createElement(size);
            var t = document.createTextNode(label);
            l.appendChild(t);
            return l;
        }

        function addCommonFields(id, actionType, gen=null){
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

            f.appendChild(i);
            f.appendChild(node);
            f.appendChild(generation);
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

<main id="main container">
    <?php if($count == 0){?>
        <button class="btn root" id="addRootBtn" onclick="addNode(0, 0, 'root')"> + Add Root Member</button>
    <?php }	?>



    <table>
        <thead>
            <th>Id</th>
            <th>Name</th>
            <th>Spouse</th>
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
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'name', <?php echo $result['generation']?>)" >
                            <i class="far fa-edit"></i>
                        </a>
                    </td>
		            <td>
                        <?php echo $result['spouse']?>
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'spouse', <?php echo $result['generation']?>)" >
                            <?php if($result['spouse'] != null && $result['spouse'] != " "){echo '<i class="far fa-edit"></i>';} else{ echo '<i class="fas fa-plus"></i>';}?>
                        </a>
			            <a href="#" onclick="submitRemoveAction(<?php echo $result['_id']?>, 'divorce')">
				            <?php if($result['spouse'] != null && $result['spouse'] != " " ){echo '<i class="fas fa-times text-danger"></i>';}?>
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
                        <a href="#" onclick="submitAction(<?php echo $result['_id']?>, 'child', <?php echo $result['generation']?>)">
			                Add Child
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="#" onclick="submitRemoveAction(<?php echo $result['_id']?>, 'delete')">
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

<!--Add Node/Spouse Modal-->
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

</body>
</html>