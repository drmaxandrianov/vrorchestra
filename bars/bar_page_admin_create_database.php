<?php

if (isset($_POST['DATABASE_SUBMIT'])) {

	// Check for parameters
	if (empty($_POST['root_login'])) display_form("password is not given, fix it, please.");
	if (empty($_POST['root_password'])) display_form("login is not given, fix it, please.");

	// Connect to database
	$dbc = mysql_connect($DB_HOST, $_POST['root_login'], $_POST['root_password']);
	if (!$dbc) {
		display_form("can not connect to local database server, try again, plaese.");
		exit;
	}

	// Create new database
	if (!mysql_query("create database $DB_NAME", $dbc)) {
		display_form("can not create new database may be it has already been created.");
		exit;
	}
	
	// Create user for new database
	if (!mysql_query("create user $DB_USER identified by '$DB_PASSWORD'", $dbc)) {
		display_form("database created, but new user was not created, try again, plaese.");
		exit;
	}

	// Grant access for the new user
	if (!mysql_query("grant all on $DB_NAME.* to '$DB_USER'", $dbc)) {
		display_form("database created, but can not grant access to new user, try again, plaese.");
		exit;
	}

	// Create table
	


	// Database and was successfully installed
	display_success();

	mysql_close($dbc);

	
} else {
	display_form();
}

function display_form($warning_message = "") {
	?>
	<section>
		<h2>Configuration of VR Orchestra.</h2>
		<p>
			This page is only for database installation purpouse.
			Follow the instructions below.
		</p>
		<p>
			<h3>Database creation.</h3>
			<form method="POST" action="admin_create_database.php">
				<legend>
					All fields are required. Private data as administrator's login and password
					will not be saved. It will be used only one time for data base
					creation process. And one prerequirement: data base should be MySQL and
					installation server should be local host.
				</legend>
					<?php
						// Display warning message in case of upploading error
						if ($warning_message != "")
							echo "<p><strong>Error: </strong>" . $warning_message . "</p>";
					?>
				<input type="hidden" name="DATABASE_SUBMIT">
				<p><label>Data base administrator login: <input type="text" name="root_login" required></label></p>
				<p><label>Data base administrator password: <input type="password" name="root_password" required></label></p>
				<button type="submit">Create database</button>
			</form>
		</p>
	</section>
	<?php
}

function display_success() {
	?>
	<section>
		<h2>Configuration of VR Orchestra.</h2>
		<p>
			This page is only for database installation purpouse.
			Follow the instructions below.
		</p>
		<p>
			<h3>Database successfully created.</h3>
			New data base was successfully created. Now you can use VR Orchestra.
		</p>
	</section>
	<?php
}
?>
