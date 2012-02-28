<?php

if (isset($_GET['confirm'])) {

	// Check for empty values
	if ($_GET['id'] == "") {
		display_error();
		exit;
	}

	$world_id = $_GET['id'];

	// Connect to DB
	$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
	mysql_select_db($DB_NAME);

	$query = "select * from $WORLDS_TABLE where Id=$world_id";
	$data = mysql_query($query);

	if (mysql_num_rows($data) == 0) {
		display_no_such_world();
		exit;
	}

	$world = mysql_fetch_array($data);
	
	// Delete file
	unlink($world['Image']);

	$query = "delete from $WORLDS_TABLE " .
		"where Id=$world_id";

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


function display_confirmation($world_name, $world_id) {
	?>
	<section>
		<h2>Delete world.</h2>
		<p>
			You are going to delete world '<?php echo $world_name; ?>' with ID equal
			to '<?php echo $world_id; ?>'. Are you sure?
		</p>
		<p>
			<a href="delete_world.php?id=<?php echo $world_id; ?>&confirm">Yes, delete!</a>
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
		<h2>Can not delete world.</h2>
		<p>
			Sorry, but the world ID or name is missing. Go to the
			<a href="list_all_worlds.php">list of all worlds</a> and select world
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
		<h2>World was deleted.</h2>
		<p>
			Your world was successfully deleted.
		</p>
		<p>
			<a href="list_all_worlds.php">Go to the list of all worlds</a>
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}

function display_no_such_world() {
	?>
	<section>
		<h2>No such world.</h2>
		<p>
			Sorry, but selected world does not exist.
		</p>
		<p>
			<a href="list_all_worlds.php">Go to the list of all worlds</a>
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}
?>
