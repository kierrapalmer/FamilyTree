<?php
	require_once("db_config.php");
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

		//Used to add very first root node
		public function addRoot($db_connection){
			$query = "INSERT INTO tree_node (_id, firstName, lastName, generation)
                  VALUES(:_id, :firstName, :lastName, :generation)";

			try{
				$result = $db_connection->prepare($query);
				if($result->execute([
					'_id'           => null,
					'firstName'     => $this->first,
					'lastName'      => $this->last,
					'generation'    => $this->generation,
				])){
					header('Location: index.php');
					die();
				}
				else
					echo "There was an error adding the root member. Please try again";
			}
			catch (Exception $ex){
				echo "Error adding member." . $ex;
			}
		}

		//Finds parentMarriageId or creates a new one(in case of single parent)
		//Adds child with parentMarriageId
		public function addChild($db_connection){
			$findMarriageResults = $db_connection->query("SELECT * FROM marriage_union WHERE memberId = {$this->parentId}  AND isActive = 1")->fetch();     //get marriage union id for childs parent
			$findParentResults = $db_connection->query("SELECT * FROM tree_node WHERE _id = {$this->parentId}")->fetch();     //get parent node
			$member = $findParentResults['firstName'] . " " . $findParentResults['lastName'];

			if($findMarriageResults['unionId'] != null)
				$parentMarriageId = $findMarriageResults['unionId'] . "";
			else{
				$this->addMarriageUnion($db_connection, $member, $findParentResults['_id'], null);
				$findMarriageResults2 = $db_connection->query("SELECT * FROM marriage_union WHERE memberId = {$this->parentId}  AND isActive = 1")->fetch();     //get marriage union id for childs parent
				$parentMarriageId = $findMarriageResults2['unionId'] . "";
			}

			$query = "INSERT INTO tree_node (_id, firstName, lastName, generation, parentMarriageId)
                  VALUES(:_id, :firstName, :lastName, :generation, :parentMarriageId)";

			try{
				$result = $db_connection->prepare($query);
				if($result->execute([
					'_id'           => null,
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

		//Updates the spouse column
		public function changeSpouse($db_connection){
			$results = $db_connection->query("SELECT _id, firstName, lastName FROM tree_node WHERE _id = {$this->id}")->fetch();
			$member = $results['firstName'] . " " . $results['lastName'];
			$spouse = $this->first . " " . $this->last;

			$addSpouseQuery = "UPDATE tree_node
                    SET spouse = :spouse
                    WHERE _id = :_id";

			try{
				$addSpouseResult = $db_connection->prepare($addSpouseQuery);
				$this->addMarriageUnion($db_connection, $member, $results['_id'], $spouse);
				if(
				$addSpouseResult->execute([
					'_id'           => $this->id,
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

		//Adds a new Marriage union table entry
		//Is called from addChild and changeSpouse Functions
		public function addMarriageUnion($db_connection, $member, $id, $spouse){
			$addMarriageQuery = "INSERT INTO marriage_union(unionId, member, memberId, partner, isActive)
					VALUES	(:_id, :member, :memberId, :partner, 1);";

			try{
				$addMarriageResult = $db_connection->prepare($addMarriageQuery);
				if($addMarriageResult->execute([
					'_id'           => null,
					'member'    =>  $member,
					'memberId'  => $id,
					'partner'     => $spouse,
				])
				){
					log("Added marriage union");
				}
				else
					echo "There was an error adding the spouse. Please try again";
			}
			catch (Exception $ex){
				echo "Error adding spouse." . $ex;
			}
		}

		//Changes name of member in both tree_node and marriage_union table
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

		//Deletes child node from table
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

		//Removes a partner from spouse column
		// and adds them to divorcedSpouses column(marked with a d for divorced)
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