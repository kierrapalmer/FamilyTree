<?php
	require_once("db_config.php");
	echo '<form action="index.php" method="post">
	First Name: <input type="text" name="first">
	Last Name: <input type="text" name="last">
	<input type="submit" id="submitMember" value="Add">
	</form>';
