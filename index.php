<?php
    require_once("db_config.php");
    $displayRootBtn = true;

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


	if(isset($_POST['submitNode'])){
        $id = filter_var($_POST['id'], FILTER_SANITIZE_STRING);
        $first = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
        $last = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
        $nodeType = filter_var($_POST['nodetype'], FILTER_SANITIZE_STRING);
        $generation = filter_var($_POST['generation'], FILTER_SANITIZE_STRING);
        $parentId = filter_var($_POST['parentId'], FILTER_SANITIZE_STRING);

        if($nodeType == "spouse") {
            $node = new Node($id, $first, $last, $nodeType, $generation, $parentId);
        }
        elseif ($nodeType == "child"){
            $node = new Node(null, $first, $last, $nodeType, $generation, $id);         //set parentId to incoming id
            $node -> addChild($db_connection);
        }

    }

    class Node {
	    public $id;
	    public $first;
	    public $last;
	    public $nodeType;
	    public $generation;
	    public $parentId;

	    public function __construct($id, $first, $last, $nodeType, $generation, $parentId){
            $this->id = $id;
            $this->first = $first;
			$this->last = $last;
			$this->nodeType = $nodeType;
			$this->generation = $generation;
			$this->parentId = $parentId;
		}

		public function addChild($db_connection){

            $query = "INSERT INTO tree_node (_id, firstName, lastName, generation)
                  VALUES(:_id, :firstName, :lastName, :generation)";

            try{
                $result = $db_connection->prepare($query);
                if($result->execute([
                    '_id'           => null,
                    'firstName'     => $this->first,
                    'lastName'      => $this->last,
                    'generation'    => $this->generation,
                    'parentId'    => $this->parentId,
                ])){
                    $displayRootBtn = false;
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
    }

    $q = $db_connection->prepare("SELECT * FROM tree_node");
    $q->execute();
    $r = $q->fetchAll();
    $count = count($r);

    $results = $db_connection->query("SELECT * FROM tree_node")
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
		function addNode(id, nodeType) {
            var f = document.createElement("form");
            f.setAttribute('method',"post");
            f.setAttribute('action',"index.php");

            var first = document.createElement("input");
            first.setAttribute('type',"text");
            first.setAttribute('name',"firstname");

            var last = document.createElement("input");
            last.setAttribute('type',"text");
            last.setAttribute('name',"lastname");

            var id = document.createElement("input");
            id.setAttribute('type',"hidden");
            id.setAttribute('name',"id");

            var nodeType = document.createElement("input");
            nodeType.setAttribute('type',"hidden");
            nodeType.setAttribute('name',"nodeType");

            var generation = document.createElement("input");
            generation.setAttribute('type',"hidden");
            generation.setAttribute('name',"generation");

            var parentId = document.createElement("input");
            parentId.setAttribute('type',"hidden");
            parentId.setAttribute('name',"parentId");


            var s = document.createElement("input");
            s.setAttribute('type',"submit");
            s.setAttribute('name',"submitNode");
            s.setAttribute('value',"Submit");

            f.appendChild(first);
            f.appendChild(last);
            f.appendChild(id);
            f.appendChild(nodeType);
            f.appendChild(generation);
            f.appendChild(parentId);
            f.appendChild(s);
            document.getElementById("addNode").appendChild(f);
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
        <button class="btn root" id="addRootBtn" onclick="addNode('root')"> + Add Root Member</button>
    <?php }	?>

    <table border="1">
        <?php
        foreach($results as $result) {	?>
            <tr>
                <td><?php echo $result['firstName']?></td>
                <td><?php echo $result['lastName']?></td>
                <td><a href="#" onclick="addNode(<?php echo $result['_id']?>, 'spouse')">Add Spouse</a></td>
                <td><a href="#" onclick="addNode(<?php echo $result['_id']?>, 'child')">Add Child</a></td>
            </tr>
        <?php }	?>
    </table>

    <div id="addNode"></div>

</main>
<!--
<div>
    <ul id="tree">

    </ul>
</div>


<script src="scripts.js"></script>
-->
</body>
</html>