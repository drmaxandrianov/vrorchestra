<?php
	// Open image
	$im = imagecreatefrompng("../" . $_REQUEST['image']);
	
	// Set the content type header - in this case image/jpeg
	header('Content-Type: image/jpeg');

	// Output the image
	imagejpeg($im);

	// Free up memory
	imagedestroy($im);
?>
