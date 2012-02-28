<?php

if (isset($_POST['WORLD_SUBMIT'])) {

	// Check for empty form values
	if (empty($_POST['world_name'])) {
		display_form("world name was not given, fix it, please.");
		exit;
	}
	if (empty($_FILES['world_image'])) {
		display_form("image was not given, fix it, please.");
		exit;
	}

	$world_name = $_POST['world_name'];

	// Connect to DB
	$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
	mysql_select_db($DB_NAME);

	// Get the last world ID
	$query = "select max(Id) from worlds";	
	$last_id = mysql_query($query);
	$last_id = mysql_fetch_array($last_id);
	$next_id = $last_id[0] + 1;

	// Saving file
	$saving_path = $PATH_TO_FOLDER_WITH_UPLOADED_WORLDS . "world_" .
		$next_id . "." . pathinfo($_FILES['world_image']['name'], PATHINFO_EXTENSION);
	if (!move_uploaded_file($_FILES['world_image']['tmp_name'], $saving_path)) {
		// Error during uploading
		display_form("error during file uploading, try again, please.");
	}

	$query = "insert into $WORLDS_TABLE (Name, Image) " .
		"values ('$world_name', '$saving_path')";

	$data = mysql_query($query);
	mysql_close($dbc);

	display_success();
	
} else {
	display_form();
}


function display_form($warning_message = "") {
	?>
	<section>
		<h2>Deploy new world form.</h2>
		<p>
			<form action="deploy_world.php" enctype="multipart/form-data" method="POST">
				<!-- Maxim file size - 1Mb -->
				<input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
				<input type="hidden" name="WORLD_SUBMIT" />
				<legend>
					Notice, that it is better to upload PNG images for correct work.
					Also the maximum size of one image is limited to 1 Mb now.
				</legend>
					<?php
						// Display warning message in case of upploading error
						if ($warning_message != "")
							echo "<p><strong>Error: </strong>" . $warning_message . "</p>";
					?>
				<p><label>Name of new world: <input type="text" name="world_name" required></label></p>
				<p><label>Image of world: <input type="file" name="world_image" required></label></p>
				<p><button type="submit">Deploy world</button></p>
			</form>
		</p>
	</section>
	<?php
}

function display_success() {
	?>
	<section>
		<h2>Deploy new world form.</h2>
		<p>
			<h3>New world succesfullly deployed.</h3>
			Thank you, now you can use your world for new experiments.
		</p>
		<p>
			<a href="index.php">Go to the main page</a>
		</p>
	</section>
	<?php
}
?>
