<?php

$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
mysql_select_db($DB_NAME);

$query = "select * from $ROBOTS_TABLE";
$data = mysql_query($query);

// Display list with all robots
display_list_begin();
if (mysql_num_rows($data) != 0) {
	while($robot = mysql_fetch_array($data)) { 
		display_robot($robot['Id'], $robot['Name']);
	}
} else {
	display_list_empty_content();
}
display_list_end();


function display_robot($robot_id, $robot_name) {
	?>
			<div>
				<p>
					<strong>Robot name: </strong><?php echo $robot_name; ?><br/>
					<strong>Robot ID: </strong><?php echo $robot_id; ?><br/>
					<a href="delete_robot.php?id=<?php echo $robot_id; ?>&name=<?php echo $robot_name; ?>">Delete robot</a>
				</p>
			</div>
	<?php
}

function display_list_begin() {
	?>
		<section>
			<h2>List of all robots.</h2>
	<?php
}

function display_list_end() {
	?>
		</section>
	<?php
}

function display_list_empty_content() {
	?>
		<p>List of robots is empty. <a href="deploy_robot.php">Create new robot.</a></p>
	<?php
}

?>

