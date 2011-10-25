<?php

if (isset($_POST['WORLD_SUBMIT'])) {

	// Check for empty form values
	if (empty($_POST['world_name']))
		display_form("world name was not given, fix it, please");
	if (empty($_POST['world_image']))
		display_form("image was not given, fix it, please.");

	
	
	display_form("nothing to uppload");
} else {
	display_form();
}


function display_form($warning_message = "") {
	?>
	<section>
		<h2>Deploy new world form.</h2>
		<p>
			<form action="deploy_world.php" method="POST">
				<!-- Maxim file size - 1Mb -->
				<input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
				<input type="hidden" name="WORLD_SUBMIT" />
				<legend>
					Notice, that it is better to upload BMP images for correct work.
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
?>
