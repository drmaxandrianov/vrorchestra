<?php

$dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
mysql_select_db($DB_NAME);

$query = "select * from worlds";
$data = mysql_query($query);

// Display list with all worlds
display_list_begin();
if (mysql_num_rows($data) != 0) {
	while($world = mysql_fetch_array($data)) { 
		display_world($world['Id'], $world['Name'], $world['Image']);
	}
} else {
	display_list_empty_content();
}
display_list_end();


function display_world($world_id, $world_name, $world_image) {
	?>
			<div>
				<a href="<?php echo $world_image; ?>">
					<img width=120 height=120 src="common/common_export_png_as_jpeg.php?image=<?php echo $world_image; ?>" alt="<?php echo $world_name; ?>">
				</a>
				<p><strong>World name: </strong><?php echo $world_name; ?></p>
				<p><strong>World ID: </strong><?php echo $world_id; ?></p>
				<p><a href="delete_world.php?id=<?php echo $world_id; ?>&name=<?php echo $world_name; ?>">Delete world</a></p>
			</div>
		</section>
	<?php
}

function display_list_begin() {
	?>
		<section>
			<h2>List of all worlds.</h2>
	<?php
}

function display_list_end() {
	?>
		</section>
	<?php
}

function display_list_empty_content() {
	?>
		<p>List of worlds is empty. <a href="deploy_world.php">Create new world.</a></p>
	<?php
}

?>

