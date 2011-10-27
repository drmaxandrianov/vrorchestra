<?php

if (isset($_GET['confirm'])) {

	// Check for empty values
	if ($_GET['id'] == "") {
		display_error();
		exit;
	}

	$robot_id = $_GET['id'];

	// Connect to DB
	$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
	mysql_select_db($DB_NAME);

	$query = "select * from $ROBOTS_TABLE where Id=$robot_id";
	$data = mysql_query($query);

	if (mysql_num_rows($data) == 0) {
		display_no_such_robot();
		exit;
	}

	$robot = mysql_fetch_array($data);
	
	$query = "delete from $ROBOTS_TABLE " .
		"where Id=$robot_id";

	$data = mysql_query($query);
	mysql_close($dbc);

	display_success();
	
} else {
	if (empty($_GET['name']) ||
		!isset($_GET['id']) ||
		$_GET['id'] == "") {
		display_error();
		exit;
	}
		
	$wn = $_GET['name'];
	$id = $_GET['id'];
	display_confirmation($wn, $id);
}


function display_confirmation($robot_name, $robot_id) {
	?>
	<section>
		<h2>Delete robot.</h2>
		<p>
			You are going to delete robot '<?php echo $robot_name; ?>' with ID equal
			to '<?php echo $robot_id; ?>'. Are you sure?
		</p>
		<p>
			<a href="delete_robot.php?id=<?php echo $robot_id; ?>&confirm">Yes, delete!</a>
		</p>
		<p>
			<a href="javascript:history.go(-1)">No, go back</a>
		</p>
	</section>
	<?php
}

function display_error() {
	?>
	<section>
		<h2>Can not delete robot.</h2>
		<p>
			Sorry, but the robot ID or name is missing. Go to the
			<a href="list_all_robots.php">list of all robots</a> and select robot
			which you wish to delete.
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}

function display_success() {
	?>
	<section>
		<h2>Robot was deleted.</h2>
		<p>
			Your robot was successfully deleted.
		</p>
		<p>
			<a href="list_all_robots.php">Go to the list of all robots</a>
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}

function display_no_such_robot() {
	?>
	<section>
		<h2>No such robot.</h2>
		<p>
			Sorry, but selected robot does not exist.
		</p>
		<p>
			<a href="list_all_robots.php">Go to the list of all robots</a>
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}
?>
