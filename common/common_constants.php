<?php
	// DB credentials
	$DB_HOST = 'localhost';
	$DB_USER = 'vrorchestra';
	$DB_PASSWORD = 'vrorchestra_password';
	$DB_NAME = 'vrorchestra';

	$WORLDS_TABLE = "worlds";
	$ROBOTS_TABLE = "robots";
	
	$WORLDS_TABLE_CREATE_CODE = "CREATE TABLE $WORLDS_TABLE (" .
		"Id bigint(20) NOT NULL AUTO_INCREMENT, " .
		"Name text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, " .
		"Image text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, " .
		"PRIMARY KEY (Id)" .
		") ENGINE=MyISAM DEFAULT CHARSET=latin1";

	$ROBOTS_TABLE_CREATE_CODE = "CREATE TABLE $ROBOTS_TABLE (" .
		"Id bigint(20) NOT NULL AUTO_INCREMENT, " .
		"WorldId bigint(20) NOT NULL, " .
		"PosX double NOT NULL, " .	
		"PosY double NOT NULL, " .
		"ViewAngle double NOT NULL, " .
		"PRIMARY KEY (Id)" .
		") ENGINE=MyISAM DEFAULT CHARSET=latin1";

	$PATH_TO_FOLDER_WITH_UPLOADED_WORLDS = 'worlds/';
?>
