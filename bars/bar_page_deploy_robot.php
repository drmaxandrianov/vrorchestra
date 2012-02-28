<?php

// Connect to DB
$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
mysql_select_db($DB_NAME);

$query = "select * from worlds";
$data = mysql_query($query);

// Create list of all worlds for form
$form_worlds_list = "";
while($world = mysql_fetch_array($data)) { 
	$form_worlds_list .= "<option value='" . $world['Id'] . "' " .
		">" . $world['Name'] . "</option>";
}


if (isset($_POST['ROBOT_SUBMIT'])) {

	// Check for empty form values
	if (empty($_POST['robot_name'])) {
		display_form("robot name was not given, fix it, please.");
		exit;
	}
	if (empty($_POST['destination_world'])) {
		display_form("destination world was not given, fix it, please.");
		exit;
	}

	// Read posted data
	$robot_name = $_POST['robot_name'];
	$destination_world = $_POST['destination_world'];
	if (empty($_POST['robot_pos_x'])) $robot_pos_x = 0;
		else $robot_pos_x = $_POST['robot_pos_x'];	
	if (empty($_POST['robot_pos_y'])) $robot_pos_y = 0;
		else $robot_pos_y = $_POST['robot_pos_y'];
	if (empty($_POST['robot_angle'])) $robot_angle = 0;
		else $robot_angle = $_POST['robot_angle'];


	$query = "insert into $ROBOTS_TABLE (Name, WorldId, PosX, PosY, ViewAngle) " .
		"values ('$robot_name', '$destination_world' , '$robot_pos_x', '$robot_pos_y', '$robot_angle')";

	$data = mysql_query($query);
	mysql_close($dbc);

	display_success();
	
} else {
	display_form();
}


function display_form($warning_message = "") {
	global $form_worlds_list;
	?>
	<section>
		<h2>Deploy new robot form.</h2>
		<p>
			<form action="deploy_robot.php" method="POST">
				<input type="hidden" name="ROBOT_SUBMIT" />
				<legend>
					For current time it is very easy form. Just specify robot's name and world
					where it will live.
				</legend>
					<?php
						// Display warning message in case of upploading error
						if ($warning_message != "")
							echo "<p><strong>Error: </strong>" . $warning_message . "</p>";
					?>
				<p><label>Name for robot: <input type="text" name="robot_name" required></label></p>
				<p><label>
					Destination world:
					<select name="destination_world">
						<?php echo $form_worlds_list; ?>
					</select>
				</label></p>
				<p><label>Robot X position (optional): <input type="number" name="robot_pos_x"></label></p>
				<p><label>Robot Y position (optional): <input type="number" name="robot_pos_y"></label></p>
				<p><label>Robot view angle (optional): <input type="number" min="0" max="6.29" step="0.01" name="robot_angle"></label></p>
				<p><button type="submit">Deploy robot</button></p>
			</form>
		</p>
	</section>
	<?php
}

function display_success() {
	?>
	<section>
		<h2>Deploy new robot form.</h2>
		<p>
			<h3>New robot succesfully deployed.</h3>
			Thank you, now you can use your robot for new experiments.
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}
?>
