<?php

@error_reporting(0);
@ini_set('display_errors', 0);

set_error_handler("bluePenErrorHandler");

ob_start();


$BP = new BP_AJAX();
$BP->importData();
$BP->checkVersion();

$BP->run();

class BP_AJAX {

	var $version = '1.0';
	var $valid_vars = array(
		"GET" => array("callback", "_", "task", "base_url", "file_name", "img_dir", "sub_dir", "selector", "nth_selector", "hrefs", "site_bluepen_dir", "site_image_dir", "fontFamily", "version", "cssReloadCnt", "pauseMillisec"),
		"POST" => array("task", "base_url", "file_name", "selector", "nth_selector", "settings_array", "parent_selector", "selector_body", "target_file", "new_selector", "version"),
		"FILES" => array("task", "base_url")
	);
	var $allowed_tasks = array("getFontFamilies", "getCSSfile", "getsettings", "getfiles", "uploadFile", "listBackups", "restoreBackup", "saveCSS", "deletecss", "savesettings", "getStylesheets", "getHTML", "embedFontFamily", "pause");
	var $data_source;
	var $base_url;
	var $vars;
	var $task;
	var $step_back;
	var $fontFamilies = 'Abel, Aclonica, Actor, Allan, Allerta, Allerta Stencil, Amaranth, Annie Use Your Telescope, Anonymous Pro, Anton, Architects Daughter, Arimo, Artifika, Arvo, Asset, Astloch, Aubrey, Bangers, Bentham, Bevan, Bigshot One, Black Ops One, Bowlby One, Bowlby One SC, Brawler, Buda, Cabin, Cabin Sketch, Calligraffitti, Candal, Cantarell, Cardo, Carme, Carter One, Caudex, Cedarville Cursive, Cherry Cream Soda, Chewy, Coda, Coda Caption, Coming Soon, Copse, Corben, Cousine, Covered By Your Grace, Crafty Girls, Crimson Text, Crushed, Cuprum, Damion, Dancing Script, Dawning of a New Day, Delius, Delius Swash Caps, Didact Gothic, Droid Sans, Droid Sans Mono, Droid Serif, EB Garamond, Expletus Sans, Federo, Fontdiner Swanky, Forum, Francois One, Gentium Basic, Geo, Give You Glory, Gloria Hallelujah, Goblin One, Goudy Bookletter 1911, Gravitas One, Gruppo, Hammersmith One, Holtwood One SC, Homemade Apple, IM Fell DW Pica, IM Fell DW Pica SC, IM Fell Double Pica, IM Fell Double Pica SC, IM Fell English, IM Fell English SC, IM Fell French Canon, IM Fell French Canon SC, IM Fell Great Primer, IM Fell Great Primer SC, Inconsolata, Indie Flower, Irish Grover, Istok Web, Josefin Sans, Josefin Slab, Judson, Jura, Just Another Hand, Just Me Again Down Here, Kameron, Kelly Slab, Kenia, Kranky, Kreon, Kristi, La Belle Aurore, Lato, League Script, Leckerli One, Lekton, Limelight, Lobster, Lobster Two, Lora, Love Ya Like A Sister, Loved by the King, Luckiest Guy, Maiden Orange, Mako, Marvel, Maven Pro, Meddon, MedievalSharp, Megrim, Merriweather, Metrophobic, Michroma, Miltonian, Miltonian Tattoo, Modern Antiqua, Molengo, Monofett, Mountains of Christmas, Muli, Neucha, Neuton, News Cycle, Nixie One, Nobile, Nothing You Could Do, Nova Cut, Nova Flat, Nova Mono, Nova Oval, Nova Round, Nova Script, Nova Slim, Nova Square, Nunito, OFL Sorts Mill Goudy TT, Old Standard TT, Open Sans, Open Sans Condensed, Orbitron, Oswald, Over the Rainbow, Ovo, PT Sans, PT Sans Caption, PT Sans Narrow, PT Serif, PT Serif Caption, Pacifico, Patrick Hand, Paytone One, Permanent Marker, Philosopher, Play, Playfair Display, Podkova, Pompiere, Puritan, Quattrocento, Quattrocento Sans, Radley, Raleway, Rationale, Redressed, Reenie Beanie, Rochester, Rock Salt, Rokkitt, Rosario, Ruslan Display, Schoolbell, Shadows Into Light, Shanti, Sigmar One, Six Caps, Slackey, Smokum, Smythe, Sniglet, Snippet, Special Elite, Stardos Stencil, Sue Ellen Francisco, Sunshiney, Swanky and Moo Moo, Syncopate, Tangerine, Tenor Sans, Terminal Dosis Light, The Girl Next Door, Tienne, Tinos, Tulpen One, Ubuntu, Ultra, UnifrakturCook, UnifrakturMaguntia, Unkempt, Unna, VT323, Varela, Varela Round, Vibur, Vollkorn, Waiting for the Sunrise, Wallpoet, Walter Turncoat, Wire One, Yanone Kaffeesatz, Yellowtail, Yeseva One, Zeyada';

	function BP_AJAX() {

		$this->_checkBackupDir();
		$this->_checkDb();
	}

	function importData() {

		$has_post = count($_POST) > 0;
		$has_get = count($_GET) > 0;
		$has_files = count($_FILES) > 0;

		if ($has_post || $has_get || $has_files) {

			if (($has_post + $has_get + $has_files) > 1) {
				echo '<pre>GET:
';
				print_r($_GET);
				echo 'POST:
';
				print_r($_POST);
				echo 'FILES:
';
				print_r($_FILES);
				echo '</pre>';
				
				trigger_error("TOO_MUCH_PARAMS", E_WARNING);
			}

			if ($has_post) {
				$this->data_source = "POST";
				$this->_importVars($_POST);
			} else if ($has_get) {
				$this->data_source = "GET";
				$this->_importVars($_GET);
			} else {
				$this->data_source = "FILES";
				$this->_importVars($_FILES);
			}
		} else {
			trigger_error("NO_DATA", E_USER_ERROR);
			send_output();
		}
		
	}

	function _importVars(&$vars) {

		$not_valid_keys = array();
		$keys = array_keys($vars);
		$valid_keys = $this->valid_vars[$this->data_source];

		foreach ($keys as $key) {

			if (!in_array($key, $valid_keys)) {
				$not_valid_keys[] = $key;
			}
		}

		if (count($not_valid_keys) > 0) {

			echo 'Not valid parameter' . (count($not_valid_keys) > 1 ? 's' : '') . ': ';
			echo '<ul>';
			foreach ($not_valid_keys as $key) {
				echo '<li><b>' . $key . '</b></li>';
			}
			echo '</ul>';
			
			trigger_error("NOT_ALLOWED_PARAMS", E_USER_WARNING);
		}

		$this->_setTask($vars["task"]);
		$this->_setBaseUrl($vars["base_url"]);
		$this->_setStepBack();
		$this->_setParams($vars);
	}

	function checkVersion() {
		if (!isset($this->vars["version"])) {
			trigger_error("NO_VERSION_NUMBER_DEFINED", E_USER_WARNING);
		} else if ($this->vars["version"] !== $this->version) {
			trigger_error("VERSION_MISMATCH", E_USER_WARNING);
		}
	}

	function _setTask(&$task) {

		$task = $this->_clean($task, true);

		if ($task == '') {
			trigger_error("NO_TASK", E_USER_ERROR);
			send_output();
		}

		if (!in_array($task, $this->allowed_tasks)) {
			trigger_error("NOT_ALLOWED_TASK", E_USER_ERROR);
			send_output();
		}

		$this->task = $task;
	}

	function _setBaseUrl(&$base_url) {

		$base_url = $this->_clean($base_url, true);

		if ($base_url == '') {
			trigger_error("NO_BASE_URL", E_USER_ERROR);
			send_output();
		}

		if (substr($base_url, 0, 7) !== "http://") {
			echo "" . $base_url . "";
			trigger_error("INVALID_BASE_URL", E_USER_ERROR);
			send_output();
		}

		$server_name = preg_replace('/(http:\/\/)|(www\.)|(\/)/', "", $_SERVER["SERVER_NAME"]);
		$base_url_server = preg_replace('/(http:\/\/)|(www\.)|(\/)/', "", $base_url);

		if (substr($base_url_server, 0, strlen($server_name)) !== $server_name) {
			echo $base_url_server . " - " . $server_name;
			trigger_error("WRONG_SERVER_NAME", E_USER_ERROR);
			send_output();
		}

		$this->base_url = $base_url;
	}

	function _setParams(&$vars) {

		if (count($vars) == 0) {
			trigger_error("NO_VAR_TO_PROCESS", E_USER_ERROR);
			send_output();
		}

		unset($vars["task"]);
		unset($vars["base_url"]);

		foreach ($vars as $key => $val) {

			$val = $this->_clean($val);

			$this->vars[$key] = $val;

			unset($vars[$key]);
		}
	}

	function _clean($str, $noWhiteSpace = false) {

		$str = stripslashes(trim(strip_tags(nl2br($str))));

		if ($noWhiteSpace) {
			$str = preg_replace('/\s\s+/', ' ', $str);
		}

		return $str;
	}

	function _getFileName($file_name) {

		$file_name = str_replace( str_replace("www.", "", $this->base_url), "", str_replace("www.", "", $file_name));

		$qm_pos = strpos($file_name, "?");
		if ($qm_pos > -1)
			$file_name = substr($file_name, 0, $qm_pos);

		$bpfile_pos = strpos($file_name, "bluepen-local.php");
		if ($bpfile_pos > -1)
			$file_name = substr($file_name, $bpfile_pos + 17, strlen($file_name));

		if (preg_match("/http:/", $file_name)) {
			echo "" . $file_name . "";
			trigger_error("EXTERNAL_FILE", E_USER_NOTICE);
			return $file_name;
		}

		return $this->step_back . $file_name;
	}
	
	function stripProtocolls( $str ) {
		return preg_replace("/http:\/\/|www./", "", $str);
	}

	function _saveSettings() {

		$settings = array();
		$settings_array = $this->vars["settings_array"];

		if (!@file_put_contents("bp-db.txt", $settings_array)) {
			trigger_error("SETTINGS_NOT_SAVED", E_USER_NOTICE);
		}
	}

	function _getSettings() {

		if (is_file("bp-db.txt")) {
			$setArr = file_get_contents("bp-db.txt");

			if ($setArr == "")
				$setArr = "{}";
			echo $setArr;
		} else {
			trigger_error("NO_DB_FILE", E_USER_NOTICE);
		}
	}

	function _getFontFamilies() {
		echo $this->fontFamilies;
	}

	function _getCSSfile() {

		$file_name = $this->_getFileName($this->vars["file_name"]);

		$ext = substr($file_name, -4);

		if ($ext !== '.css') {
			echo $file_name;
			trigger_error("NO_CSS_FILE", E_USER_ERROR);
			send_output();
		}

		header('content-type:text/css');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");


		$base_url = $this->base_url;

		// a sajat import fajlokat kiszuri
		function filter_imports($matches) {
			global $BP;

			$url = preg_replace("/('|\"|\(|\)|url|\s)/", "", $matches[1]);
			$url = str_replace($BP->base_url, "", $url);

			// ha kulso hivatkozas, akkor maradjon benne
			if (strpos($url, "http://") !== FALSE) {
				return $matches[0];
			}

			// kulonben ezt is at kell irni, php-s betoltesre
			return '@import "' . $BP->absolute_url($BP->base_url, $_SERVER["PHP_SELF"]) . '?cssReloadCnt=' . date("YmdGis") . '&task=getCSSfile&base_url=' . $BP->base_url . '&version=' . $BP->version . '&file_name=' . $BP->absolute_url($BP->absolute_url($BP->base_url, $BP->vars["file_name"]), $url) . '";';
		}
		
		// a relativ hivatkozasokat abszolutra csereli
		function absolutize_urls($matches) {
			global $BP;
			$source_css = $BP->absolute_url($BP->base_url, $BP->vars["file_name"]);
			$path = preg_replace("/url\(|\);|\"|\'/", "", $matches[0]);
			return 'url("'.$BP->absolute_url($source_css, $path).'");';
		}

		$css = file_get_contents($file_name);
		$css = preg_replace_callback("|@import(.*);|", "filter_imports", $css);
		// a relative hivatkozasokbol absolute-ot kell csinalni
		$css = preg_replace_callback("|url\([^\)]*\);|", "absolutize_urls", $css);

		echo $css;
	}

	function absolute_url($base_url, $relative_url) {
		$base_url_info = parse_url($base_url);
		$base_url_path = explode("/", $base_url_info["path"]);
		$base_url_file = $base_url_path[count($base_url_path) - 1];
		unset($base_url_path[count($base_url_path) - 1]);
		$relative_url_path = explode("/", $relative_url);
		if (parse_url($relative_url, PHP_URL_SCHEME) != "") {
			return $relative_url;
		} else {
			switch ($relative_url_path[0]) {
				case ".":
					unset($relative_url_path[0]);
					return $this->absolute_url
							($base_url
							, implode("/", $relative_url_path)
					);
					break;
				case "..":
					unset($relative_url_path[0]);
					return $this->absolute_url
							(str_replace($base_url_info["path"], "", $base_url)
							. implode("/", $base_url_path)
							, implode("/", $relative_url_path)
					);
					break;
				case "":
					return str_replace($base_url_info["path"], "", $base_url)
					. $relative_url
					;
					break;
				default:
					return str_replace($base_url_info["path"], "", $base_url)
					. implode("/", $base_url_path)
					. "/"
					. $relative_url
					;
					break;
			}
		}
	}

	function _listBackups() {

		if (!is_dir("backup")) {
			trigger_error("NO_BACKUP_DIR", E_USER_ERROR);
			send_output();
		}

		$i = 0;
		$files = array();
		$handler = opendir("backup");
		while (false !== ($file = readdir($handler))) {

			if ($file !== '.' && $file !== '..' && $file !== '.svn' && !is_dir("backup/" . $file)) {

				$files[$i] = array();
				$files[$i]["datetime"] = date("Y-m-d G:i:s", substr($file, 0, strpos($file, "-")));
				$files[$i]["filename"] = $file;
				$files[$i]["path"] = urldecode(substr($file, strpos($file, "-") + 1));
				$i++;
			}
		}

		rsort($files);
		if (count($files) > 0) {
			echo json_encode($files);
		} else {
			trigger_error("NO_BACKUPS", E_USER_NOTICE);
		}
	}

	function _restoreBackup() {

		$file_name = $this->vars["file_name"];
		$target_file = urldecode(substr($file_name, strpos($file_name, "-") + 1));
		$step_back = $this->step_back;

		if (!is_file("backup/" . $file_name)) {
			echo '' . $file_name . '';
			trigger_error("BACKUP_FILE_NOT_FOUND", E_USER_ERROR);
			send_output();
		}
		if (!is_file($step_back . $target_file)) {
			echo "" . $step_back . $target_file . "";
			trigger_error("BACKUP_TARGET_NOT_FOUND", E_USER_NOTICE);
			send_output();
		}

		if (@copy("backup/" . $file_name, $step_back . $target_file)) {
			echo 'File restoring successfull';
		} else {
			echo 'error by restoring file <br /><br />(source: <br />' . $file_name . '<br /> -> <br />target: <br />' . $target_file . ')';
			trigger_error("CAN_NOT_RESTORE", E_USER_NOTICE);
			send_output();
		}
	}

	function _saveCSS() {

		$file_name = $this->vars["file_name"];
		$selector = preg_replace("/\s+/", " ", $this->vars["selector"]);
		$parent_selector = $this->vars["parent_selector"];
		$nth_selector = $this->vars["nth_selector"];
		$new_selector = $this->vars["new_selector"];
		$selector_body = str_replace("|", ";\n\t", stripcslashes($this->vars["selector_body"]));

		$nth_selector = $nth_selector == -1 ? -1 : $nth_selector + 1;
		$relative_path = $this->_getFileName($file_name);
//		$selector_body = substr($selector_body, 0, -1);

		if (!is_file($relative_path)) {
			echo 'saveCSS: ' . $relative_path . '';
			trigger_error("FILE_NOT_FOUND", E_USER_NOTICE);
			send_output();
		}

		if (!is_writable($relative_path) && !@chmod($relative_path, 0664)) {
			$chmod = substr(sprintf('%o', fileperms($relative_path)), -4);
			echo 'No permission to write file: ' . $relative_path . '. <br /> Current chmod: ' . $chmod . ' <br /><br /> Set group writing permission, and try again!';
			trigger_error("NO_PERMISSION", E_USER_NOTICE);
			send_output();
		}

		$backup_filename = time() . '-' . urlencode(str_replace($this->base_url, "", $file_name));
		if (@!copy($relative_path, 'backup/' . $backup_filename)) {
			echo '';
		}

		@chmod('backup/' . $backup_filename, 0755);

		$hol = file_get_contents($relative_path);
		$hol_len = strlen($hol);

		if (!$this->__isCSSvalid($hol)) {
			echo "The source CSS (" . $relative_path . ") is NOT VALID!";
			trigger_error("NOT_VALID_SOURCE_CSS", E_USER_NOTICE);
			send_output();
		}

		if ($new_selector == "false") {

			echo '<br />Updating existing selector<br />';
			$pos_close = $prev_pos_close = $pos_open = 0;
			for ($i = 0; $i < $nth_selector; $i++) {
				$pos_open = $this->__strpos($hol, "{", $pos_open + 1);
			}
			$prev_pos_close = $this->__strrpos($hol, "}", $pos_open);
			$pos_close = $this->__strpos($hol, "}", $pos_open);

			$prev_pos_close = !$prev_pos_close ? 0 : $prev_pos_close + 1;
			$nth_selector_text = $this->__stripComments(substr($hol, $prev_pos_close, $pos_open - $prev_pos_close));
			$nth_selector_text = trim(preg_replace("/(\s)+/", " ", $nth_selector_text));

			if ($selector == $nth_selector_text) {
				$hol = substr_replace($hol, "{
	" . $selector_body . "}", $pos_open, $pos_close - $pos_open + 1);
			} else {
				echo 'This two selector does not match: <br />' . $selector . '<br /><br />' . $nth_selector_text . '<br />';
				trigger_error("SELECTOR_MISMATCH", E_USER_NOTICE);
				send_output();
			}
		} else {

			if ($parent_selector !== '') {
				echo '<br />Saving after parent selector: ' . $parent_selector . '<br />';
				$parent_selector_text = "";
				$pos = -1;
				$i = 0;

				while ($i < 20 && $parent_selector_text !== $parent_selector) {

					$pos = $this->__strpos(preg_replace("/\s/", ' ', $hol), $parent_selector, ++$pos);

					$pos_open = $this->__strpos($hol, "{", $pos);

					$prev_pos_close = $this->__strrpos($hol, "}", $pos_open);
					$pos_close = $this->__strpos($hol, "}", $pos_open);

					$prev_pos_close = !$prev_pos_close ? 0 : $prev_pos_close + 1;
					$parent_selector_text = $this->__stripComments(substr($hol, $prev_pos_close, $pos_open - $prev_pos_close));
					$parent_selector_text = trim(preg_replace("/(\s)+/", " ", $parent_selector_text));


					echo 'Parent kereses nr (' . $i . '): ' . $parent_selector_text . '<br />';
					$i++;
				}







				if ($parent_selector == $parent_selector_text) {
					$hol = substr_replace($hol, "

" . $selector . " {
	" . $selector_body . "}", $pos_close + 1, 0);
				} else {
					echo 'A ket selector nem egyezik meg: <br />' . $selector . '<br /><br />' . $nth_selector_text . '<br />';
					trigger_error("SELECTOR_MISMATCH", E_USER_NOTICE);
					send_output();
				}
			} else {
				echo '<br />Saving at the end of the Stylesheet<br />';


				$pos_close = $this->__strrpos($hol, "}", $hol_len);

				$pos_close++;

				$hol = substr_replace($hol, "

" . $selector . " {
	" . $selector_body . "}", $pos_close, 0);
			}
		}

		if (!$this->__isCSSvalid($hol)) {
			echo "The CSS to save is NOT VALID!<br /><br /> ";
			trigger_error("NOT_VALID_SOURCE_CSS", E_USER_NOTICE);
			send_output();
		}

		if (is_file($relative_path)) {
			$file_css = fopen($relative_path, "w");
			$fw_css = fwrite($file_css, $hol);
			fclose($file_css);

			echo '<b>SAVED</b><br /> <br /> <b>Filename:</b> ' . $file_name . '<br /><br /> <b>Selector: </b>' . $selector . '<br />';
		} else {
			echo "(saveCSS)<br />" . $relative_path . "";
			echo "";
		}
	}

	function _deleteCSS() {

		$file_name = $this->vars["file_name"];
		$selector = preg_replace("/\s+/", " ", $this->vars["selector"]);
		$nth_selector = $this->vars["nth_selector"];

		$nth_selector = $nth_selector == -1 ? -1 : $nth_selector + 1;
		$relative_path = $this->_getFileName($file_name);

		if (!is_file($relative_path)) {
			echo '(deleteCSS)<br />' . $relative_path . '';
			trigger_error("FILE_NOT_FOUND", E_USER_NOTICE);
			send_output();
		}

		if (!is_writable($relative_path) && !@chmod($relative_path, 0664)) {
			$chmod = substr(sprintf('%o', fileperms($relative_path)), -4);
			echo 'No permission to write file: ' . $relative_path . '. <br /> Current chmod: ' . $chmod . ' <br /><br /> Set group writing permission, and try again!';
			trigger_error("NO_PERMISSION", E_USER_NOTICE);
			send_output();
		}

		$backup_filename = time() . '-' . urlencode(str_replace($this->base_url, "", $file_name));
		if (@!copy($relative_path, 'backup/' . $backup_filename)) {
			echo '';
		}

		@chmod('backup/' . $backup_filename, 0755);

		$hol = file_get_contents($relative_path);
		$hol_len = strlen($hol);

		if (!$this->__isCSSvalid($hol)) {
			echo "The source CSS (" . $relative_path . ") is NOT VALID!";
			trigger_error("NOT_VALID_SOURCE_CSS", E_USER_NOTICE);
			send_output();
		}

		echo '<br />Deleting existing selector<br />';
		$pos_close = $prev_pos_close = $pos_open = 0;
		for ($i = 0; $i < $nth_selector; $i++) {
			$pos_open = $this->__strpos($hol, "{", $pos_open + 1);
		}
		$prev_pos_close = $this->__strrpos($hol, "}", $pos_open);
		$pos_close = $this->__strpos($hol, "}", $pos_open);

		$prev_pos_close = !$prev_pos_close ? 0 : $prev_pos_close + 1;
		$nth_selector_text = $this->__stripComments(substr($hol, $prev_pos_close, $pos_open - $prev_pos_close));
		$nth_selector_text = trim(preg_replace("/(\s)+/", " ", $nth_selector_text));

		if ($selector == $nth_selector_text) {
			$hol = substr_replace($hol, "
", $prev_pos_close, $pos_close - $prev_pos_close + 1);
		} else {
			echo 'This two selector does not match: <br />' . $selector . '<br /><br />' . $nth_selector_text . '<br />';
			trigger_error("SELECTOR_MISMATCH", E_USER_NOTICE);
			send_output();
		}

		if (!$this->__isCSSvalid($hol)) {
			echo "The target CSS to save is NOT VALID!<br /><br /> ";
			trigger_error("NOT_VALID_SOURCE_CSS", E_USER_NOTICE);
			send_output();
		}
		
		if (is_file($relative_path)) {
			$file_css = fopen($relative_path, "w");
			$fw_css = fwrite($file_css, $hol);
			fclose($file_css);

			echo '<b>Selector Deleted</b><br /> <br /> <b>Filename:</b> ' . $file_name . '<br /><br /> <b>Selector: </b>' . $selector . '<br />';
		} else {
			echo "(deleteCSS)<br />" . $relative_path . "";
			echo "";
		}

		
	}

	function _getFiles() {

		$sub_dir = $this->vars["sub_dir"];
		$img_dir = $this->vars["img_dir"];
		$step_back = $this->step_back;

		if ($sub_dir !== "" && substr($sub_dir, -1) !== "/") {
			$sub_dir = $sub_dir . "/";
		}

		if (is_dir($step_back . $img_dir . $sub_dir)) {

			$i = 0;
			$firstFile = "";
			$dirs = $files = array();
			$handler = opendir($step_back . $img_dir . $sub_dir);
			while (false !== ($file = readdir($handler))) {

				if ($file !== '.' && $file !== '..' && $file !== '.svn') {

					if (is_dir($step_back . $img_dir . $sub_dir . $file)) {
						$dirs[] = $file;
					} else {
						$files[] = $file;
					}
				}
			}
			sort($dirs);
			sort($files);

			// itt kezdem osszeallitani a kimenetet
			$ret = array();
			$retI = 0;
			if (strlen($sub_dir) > 0 && $sub_dir !== "/") {
				$ret["dirs"][$retI++]["name"] = "..";
				$i++;
			}

			// mappak hozzaadasa
			if (count($dirs) > 0) {
				foreach ($dirs as $dir) {
					$ret["dirs"][$retI++]["name"] = $dir;
					$i++;
				}
			}

			$retI = 0;
			// fajlok hozzaadasa (kepek eseten meretet is adja at)
			if (count($files) > 0) {
				foreach ($files as $file) {
					$ret["files"][$retI]["name"] = $file;

					// ha kep, akkor meretet is adjon at az elonezet miatt
					$ext = pathinfo($step_back . $img_dir . $sub_dir . $file, PATHINFO_EXTENSION);
					if (preg_match("/gif|png|jpg|jpeg|bmp/", $ext)) {
						$sizes = getimagesize($step_back . $img_dir . $sub_dir . $file);
						$ret["files"][$retI]["sizes"] = Array("width" => $sizes[0], "height" => $sizes[1]);
					}
					$retI++;
					$i++;
				}
			}
			echo json_encode($ret);
		} else {
			echo 'Image directory not found! (' . $img_dir . $sub_dir . ')';
			echo '<!--bp-error-->';
		}
	}

	function _getStylesheets() {
		$hrefs = $this->vars["hrefs"];
		$file_names = explode(";", $hrefs);
		$data = Array();

		foreach ($file_names as $file_name) {

			if ($file_name == "")
				continue;

			$relative_path = $this->_getFileName($file_name);

			if (!is_file($relative_path)) {
				echo 'getStylesheets: ' . $relative_path . '';
				trigger_error("FILE_NOT_FOUND", E_USER_NOTICE);
				continue;
			}

			if (!is_writable($relative_path) && !@chmod($relative_path, 0664)) {
				$chmod = substr(sprintf('%o', fileperms($relative_path)), -4);
				echo 'No permission to write file: ' . $relative_path . '. <br /> Current chmod: ' . $chmod . ' <br /><br /> Set group writing permission, and try again!';
				trigger_error("NO_PERMISSION", E_USER_NOTICE);
				send_output();
				continue;
			}



			$file = file_get_contents($relative_path);
			$file_len = strlen($file);

			if (!$this->__isCSSvalid($file)) {
				echo "" . $relative_path . "";
				trigger_error("NOT_VALID_SOURCE_CSS", E_USER_NOTICE);
				continue;
			}


			$file = $this->__stripComments($file);
			$file = $this->__clearWhiteSpace($file);

			$i = 0;
			$file_arr = Array();
			$selectors = explode("}", $file);

			if (count($selectors) > 1) {
				foreach ($selectors as $selector) {

					$selector_arr = explode("{", $selector);

					$selector_text = isset($selector_arr[0]) ? $selector_arr[0] : '';
					$selector_body = isset($selector_arr[1]) ? $selector_arr[1] : '';


					$selector_text = trim($selector_text);
					$selector_body = trim($selector_body);

					$file_arr[$i]["selector"] = $selector_text;
					$file_arr[$i++]["selector_body"] = $selector_body;
				}
			}

			$data[$file_name] = $file_arr;
		}

		echo json_encode($data);
	}

	function _embedFontFamily() {
		$fontFamily = $this->vars["fontFamily"];
		$file_name = $this->_getFileName($this->vars["file_name"]);

		if (!is_file($file_name)) {
			echo 'Target CSS file name: ' . $file_name;
			trigger_error("FILE_NOT_FOUND", E_USER_NOTICE);
			send_output();
		}

		$css = file_get_contents($file_name);
		$css = '@import url("http://fonts.googleapis.com/css?family=' . urlencode($fontFamily) . '");

' . $css;

		file_put_contents($file_name, $css);
	}

	function _pause() {
		$millisec = $this->vars["pauseMillisec"];
		sleep(ceil($millisec / 1000));
	}

	function _uploadFile() {

		$img_dir = $this->vars["img_dir"];

		if (move_uploaded_file($_FILES["Filedata"]["tmp_name"], $img_dir . $_FILES["Filedata"]["name"])) {
			echo 'File upload successfull.';
		} else {
			echo '<pre>';
			print_r($_FILES);
			print_r($this->vars);
			echo '</pre>';
			echo 'Error by uploading file. ';
		}
	}

	function run() {
		$task = "_" . $this->task;
		$this->$task();
	}

	function _checkBackupDir() {
		if (!is_dir("backup") && !@mkdir("backup")) {
			trigger_error("BACKUP_DIR_PERMISSION", E_USER_NOTICE);
		}
	}

	function _checkDb() {
		if (!is_file("bp-db.txt") && !@touch("bp-db.txt")) {
			trigger_error("UNABLE_TO_CREATE_DB", E_USER_NOTICE);
		}
	}

	function _setStepBack() {

		$base_url = $this->base_url;

		$php_self_arr = explode('/', $_SERVER["PHP_SELF"]);
		array_shift($php_self_arr);
		array_pop($php_self_arr);

		$step_back = str_repeat('../', count(array_diff($php_self_arr, explode("/", $base_url))));

		$this->step_back = $step_back;
	}

	function __isCSSvalid($css) {
		$css = $this->__stripComments($css);
		if (preg_match("/\{([^}]*){/", $css) == 1) {
			return false;
		}
		return true;
	}

	function __stripComments($ret) {
		return preg_replace("/\/\*[\s\S]*?\*\//", "", $ret);
	}

	function __clearWhiteSpace($ret) {

		$ret = preg_replace("/\t/", "", $ret);
		$ret = preg_replace("/[\r|\n]/", " ", $ret);
		$ret = preg_replace("/\s{1,300}/", " ", $ret);

		return $ret;
	}

	function __strrpos($hol, $mit, $visszafele_honnan) {

		$is_in_comment = true;
		$hol_len = strlen($hol);
		$pos = $visszafele_honnan;

		while ($is_in_comment) {
			$rev_pos = -($hol_len - $pos) - 1;
			$pos = strrpos($hol, $mit, $rev_pos);

			if ($pos == false)
				return false;

			$pos_prev_comm_open = strrpos($hol, "/*", -($hol_len - $pos));
			$pos_prev_comm_close = strrpos($hol, "*/", -($hol_len - $pos));

			if (
					!($pos_prev_comm_open !== false && $pos_prev_comm_close == false ) &&
					$pos_prev_comm_open <= $pos_prev_comm_close
			) {
				$is_in_comment = false;
			}
		}

		return $pos;
	}

	function __strpos($hol, $mit, $honnan) {

		$is_in_comment = true;
		$hol_len = strlen($hol);
		$pos = $honnan - 1;

		while ($is_in_comment) {
			$pos = strpos($hol, $mit, $pos + 1);

			if ($pos == false)
				return false;

			$pos_prev_comm_open = strrpos($hol, "/*", -($hol_len - $pos));
			$pos_prev_comm_close = strrpos($hol, "*/", -($hol_len - $pos));

			if (
					!($pos_prev_comm_open !== false && $pos_prev_comm_close == false ) &&
					$pos_prev_comm_open <= $pos_prev_comm_close
			) {
				$is_in_comment = false;
			}
		}

		return $pos;
	}

	function _getHTML() {


		$bpConf["siteBpDir"] = $_GET["site_bluepen_dir"];
		$bpConf["siteImgDir"] = $_GET["site_image_dir"];

		$step_back = str_repeat('../', count(explode("/", $bpConf["siteBpDir"])) - 1);
?>
		<a href="javascript:void(null)" class="bp-toggle-btn">&nbsp;</a><div id="bp-cont"><div id="bp-panel" class="bp-panel"><div class="bp-title" id="bp-panel-dragger"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat"><div class="bp-title-text bp-title-logo">Main panel</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat" id="bp-panel-repeat"><div id="bp-message">&nbsp;</div><div id="bp-dialog-holder"><div class="bp-title bp-dialog-title"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat bp-dialog-title-repeat"><div class="bp-title-text" id="bp-dialog-title">Dialog</div><div class="bp-title-btns bp-dialog-btns"><a href="javascript:void(null)" class="bp-btn-close" id="bp-dialog-close-btn">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div id="bp-dialog-repeat"><div id="bp-dialog-content"><a class="bp-dialog-item" href="javascript:void(null)">small-caps</a><a class="bp-dialog-item" href="javascript:void(null)">small-caps</a><a class="bp-dialog-item" href="javascript:void(null)">small-caps</a><a class="bp-dialog-item" href="javascript:void(null)">small-caps</a><a class="bp-dialog-item" href="javascript:void(null)">small-caps</a></div></div><div id="bp-dialog-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat bp-dialog-footer-repeat"><a href="javascript:void(null)" id="bp-dialog-btn-ok">OK</a><a href="javascript:void(null)" id="bp-dialog-btn-cancel">Cancel</a><div class="bp-clear">&nbsp;</div></div><div class="bp-footer-right bp-footer-right">&nbsp;</div><div class="bp-clear">&nbsp;</div></div></div><a href="javascript:void(null)" class="bp-tab-btn" id="bp-selected-layers-btn" > Selected layer / parent layers  <span class="bp-hotkey-title">[S]</span></a><div id="bp-selected-layers-content" class="bp-tab-content"></div><a href="javascript:void(null)" class="bp-tab-btn" id="bp-file-manager-btn" > File manager  <span class="bp-hotkey-title">[M]</span></a><div id="bp-file-manager-content" class="bp-tab-content"><div id="bp-files"><h2>Files</h2><div id="bp-filemanager-files"><object width="273" height="300" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="scroller.swf?bp_list_id=bp-filemanager-files-flash" name="movie"/><param value="autohigh" name="quality"/><param value="always" name="allowScriptAccess"/><param value="opaque" name="wmode"/><param value="bgcolor" name="#000000"/><param value="LT" name="salign" /><embed id="bp-filemanager-files-flash" width="273" height="300" salign="LT" src="scroller.swf?bp_list_id=bp-filemanager-files-flash" bgcolor="black" pluginspace="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" wmode="opaque" quality="autohigh" allowscriptaccess="always" /></object></div></div><div class="bp-clear">&nbsp;</div><div id="bp-file-upload-holder"><a href="javascript:void(null)" id="bp-reload-files">reloadfiles</a><object width="65" height="20" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="bp-file-upload-flash"><param value="http://bluebros.net/bluepen/images/bp-multifile.swf?phpUrl=<?php echo urlencode($this->base_url . $bpConf["siteBpDir"]); ?>bp-ajax.php%3Fimg_upl_dir%3D<?php echo urlencode($step_back . $bpConf["siteImgDir"]); ?>" name="movie"/><param value="autohigh" name="quality"/><param value="always" name="allowScriptAccess"/><param value="opaque" name="wmode"/><embed width="65" height="20" src="http://bluebros.net/bluepen/images/bp-multifile.swf?phpUrl=<?php echo urlencode($this->base_url . $bpConf["siteBpDir"]); ?>bp-ajax.php%3Fimg_upl_dir%3D<?php echo urlencode($step_back . $bpConf["siteImgDir"]); ?>" pluginspace="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" wmode="opaque" quality="autohigh" allowscriptaccess="always" /></object><div class="bp-clear">&nbsp;</div></div><div id="bp-uploading-files"></div><div id="bp-uploading-files-template" class="bp-dn"><div class="bp-uploading-file"> filename <div class="bp-uploading-file-progressbar"><div id="bp-uploading-file-#i#" class="bp-uploading-file-stripe">&nbsp;</div></div></div></div><div id="bp-row-template-edited" class="bp-dn"><div class="bp-row-#isEven#" id="#row_id#"><a href="javascript:void(null)" class="bp-checkbox2-inactive" id="#toggle_id#">&nbsp;</a><a href="javascript:void(null)" onclick="return false;" title="#title#">#title#</a><a href="javascript:void(null)" class="bp-filemanager-file-preview" id="#preview_id#">&nbsp;</a></div></div><div id="bp-rowtitle-template-edited" class="bp-dn"><div class="bp-row-title"><a href="javascript:void(null)" onclick="" title="#title#">#title#</a></div></div><div id="bp-rowtitle-template-edited-active" class="bp-dn"><div class="bp-row-title-active"><a href="javascript:void(null)" onclick="" title="#title#">#title#</a></div></div><div id="bp-background-selection-btns"><a href="javascript:void(null)" id="bp-select-image" class="bp-btn-ok">Ok</a><a href="javascript:void(null)" id="bp-cancel-image" class="bp-btn-cancel">Cancel</a></div><br /><br /></div><a href="javascript:void(null)" class="bp-tab-btn" id="bp-css-editor-btn" > CSS editor  <span class="bp-hotkey-title">[C]</span></a><div id="bp-css-editor-content" class="bp-tab-content"><a href="javascript:void(null)" class="bp-slider-title-inactive"  id="bp-slider-actual-styles"> Style overview <span class="bp-shortcut-key"><span>[<b>F</b>]</span></span></a><div class="bp-slider-content" id="bp-slider-actual-styles-content"><h2>Now edited</h2><div class="bp-scroll-holder" id="bp-edited-list" style="height: 300px;"><div class="bp-scroll-content" id="bp-now-edited"><object width="273" height="300" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="scroller.swf?bp_list_id=bp-now-edited-flash" name="movie"/><param value="autohigh" name="quality"/><param value="always" name="allowScriptAccess"/><param value="opaque" name="wmode"/><param value="bgcolor" name="#000000"/><param value="LT" name="salign" /><embed id="bp-now-edited-flash" width="273" height="300" salign="LT" src="scroller.swf?bp_list_id=bp-now-edited-flash" bgcolor="black" pluginspace="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" wmode="opaque" quality="autohigh" allowscriptaccess="always" /></object><br /></div></div></div><a href="javascript:void(null)" class="bp-slider-title-inactive"  id="bp-slider-font-text-properties"> Font / Text properties <span class="bp-shortcut-key"><span>[<b>F</b>]</span></span></a><div class="bp-slider-content" id="bp-slider-font-text-properties-content"><h2>Font properties</h2><div class="bp-fieldset"><div class="bp-input-title-medium"> Font-family </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="font-family" id="font-family" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Font-size </div><div class="bp-input-holder-medium bp-slider-bg-medium"><div class="bp-slider-btn" id="font-size-dragger"><input type="text" name="font-size" id="font-size" class="bp-drag" /><input type="text" name="font-size-unit" id="font-size-unit" class="bp-drag-unit" /></div></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Font-weight </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="font-weight" id="font-weight" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Line-height </div><div class="bp-input-holder-medium bp-slider-bg-medium"><a href="javascript:void(null)" onclick="return false;" class="bp-slider-btn" id="line-height-dragger"><input type="text" name="line-height" id="line-height" class="bp-drag" /><input type="text" name="line-height-unit" id="line-height-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Font-variant </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="font-variant" id="font-variant" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Letter-spacing </div><div class="bp-input-holder-medium bp-slider-bg-medium"><a href="javascript:void(null)" onclick="return false;" class="bp-slider-btn" id="letter-spacing-dragger"><input type="text" name="letter-spacing" id="letter-spacing" class="bp-drag" /><input type="text" name="letter-spacing-unit" id="letter-spacing-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Font-style </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="font-style" id="font-style" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Color </div><div class="bp-input-holder-medium bp-color-select"><input type="text" name="color" id="color" class="bp-color-input" /><a href="javascript:void(null)" class="bp-color-picker-btn bp-color-select-btn"> &nbsp; </a></div><div class="bp-clear">&nbsp;</div></div><h2>Text properties</h2><div class="bp-fieldset"><div class="bp-input-title-medium"> Text-align </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="text-align" id="text-align" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Text-decoration </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="text-decoration" id="text-decoration" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Text-transform </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="text-transform" id="text-transform" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Text-indent </div><div class="bp-input-holder-medium bp-slider-bg-medium"><a href="javascript:void(null)" onclick="return false;" class="bp-slider-btn" id="text-indent-dragger"><input type="text" name="text-indent" id="text-indent" class="bp-drag" /><input type="text" name="text-indent-unit" id="text-indent-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Word-wrap </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="word-wrap" id="word-wrap" class="bp-select" /></div><div class="bp-clear">&nbsp;</div></div></div><a href="javascript:void(null)" class="bp-slider-title-inactive" id="bp-slider-box-properties">Box properties <span class="bp-shortcut-key"><span>[<b>B</b>]</span></span></a><div class="bp-slider-content" id="bp-slider-box-properties-content"><div id="bp-box-holder"><div id="bp-box-properties-holder"><table border="0" cellpadding="0" cellspacing="0"><tr><td id="bp-margin-all"><a href="javascript:void(null)" id="margin-dragger"><input type="hidden" name="margin-unit" id="margin-unit" class="bp-box-input-unit" /></a></td><td id="bp-margin-top" colspan="5"><input type="text" id="margin-top-view" class="bp-box-input" /><a href="javascript:void(null)" id="margin-top-dragger" class="bp-box-controller"><input type="text" name="margin-top" id="margin-top" class="bp-box-popup-input" /><input type="text" name="margin-top-unit" id="margin-top-unit" class="bp-box-input-unit" /></a></td><td id="bp-margin-righttop">&nbsp;</td></tr><tr><td id="bp-margin-left" rowspan="5"><input type="text" id="margin-left-view" class="bp-box-input" /><a href="javascript:void(null)" id="margin-left-dragger" class="bp-box-controller"><input type="text" name="margin-left" id="margin-left" class="bp-box-popup-input" /><input type="text" name="margin-left-unit" id="margin-left-unit" class="bp-box-input-unit" /></a></td><td id="bp-border-all"><a href="javascript:void(null)" id="border-dragger"><input type="text" name="border" id="border" class="bp-box-input" /></a></td><td id="bp-border-top" colspan="3"><a href="javascript:void(null)" id="border-top-dragger"><input type="text" name="border-top" id="border-top" class="bp-box-input" /></a></td><td id="bp-border-righttop">&nbsp;</td><td id="bp-margin-right" rowspan="5"><input type="text" id="margin-right-view" class="bp-box-input" /><a href="javascript:void(null)" id="margin-right-dragger" class="bp-box-controller"><input type="text" name="margin-right" id="margin-right" class="bp-box-popup-input" /><input type="text" name="margin-right-unit" id="margin-right-unit" class="bp-box-input-unit" /></a></td></tr><tr><td id="bp-border-left" rowspan="3"><a href="javascript:void(null)" id="border-left-dragger"><input type="text" name="border-left" id="border-left" class="bp-box-input" /></a></td><td id="bp-padding-all"><a href="javascript:void(null)" id="padding-dragger"><input type="hidden" name="padding" id="padding" value="" class="bp-drag" /><input type="hidden" name="padding-unit" id="padding-unit" class="bp-drag-unit" /></a></td><td id="bp-padding-top"><input type="text" id="padding-top-view" class="bp-box-input" /><a href="javascript:void(null)" id="padding-top-dragger" class="bp-box-controller"><input type="text" name="padding-top" id="padding-top" class="bp-box-popup-input" /><input type="text" name="padding-top-unit" id="padding-top-unit" class="bp-box-input-unit" /></a></td><td id="bp-padding-righttop">&nbsp;</td><td id="bp-border-right" rowspan="3"><a href="javascript:void(null)" id="border-right-dragger"><input type="text" name="border-right" id="border-right" class="bp-box-input" /></a></td></tr><tr><td id="bp-padding-left"><input type="text" id="padding-left-view" class="bp-box-input" /><a href="javascript:void(null)" id="padding-left-dragger" class="bp-box-controller"><input type="text" name="padding-left" id="padding-left" class="bp-box-popup-input" /><input type="text" name="padding-left-unit" id="padding-left-unit" class="bp-box-input-unit" /></a></td><td id="bp-dimensions"> &nbsp; </td><td id="bp-padding-right"><input type="text" id="padding-right-view" class="bp-box-input" /><a href="javascript:void(null)" id="padding-right-dragger" class="bp-box-controller"><input type="text" name="padding-right" id="padding-right" class="bp-box-popup-input" /><input type="text" name="padding-right-unit" id="padding-right-unit" class="bp-box-input-unit" /></a></td></tr><tr><td>&nbsp;</td><td id="bp-padding-bottom"><input type="text" id="padding-bottom-view" class="bp-box-input" /><a href="javascript:void(null)" id="padding-bottom-dragger" class="bp-box-controller"><input type="text" name="padding-bottom" id="padding-bottom" class="bp-box-popup-input" /><input type="text" name="padding-bottom-unit" id="padding-bottom-unit" class="bp-box-input-unit" /></a></td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td id="bp-border-bottom" colspan="3"><a href="javascript:void(null)" id="border-bottom-dragger"><input type="text" name="border-bottom" id="border-bottom" class="bp-box-input" /></a></td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td id="bp-margin-bottom" colspan="5"><input type="text" id="margin-bottom-view" class="bp-box-input" /><a href="javascript:void(null)" id="margin-bottom-dragger" class="bp-box-controller"><input type="text" name="margin-bottom" id="margin-bottom" class="bp-box-popup-input" /><input type="text" name="margin-bottom-unit" id="margin-bottom-unit" class="bp-box-input-unit" /></a></td><td>&nbsp;</td></tr></table></div><a href="javascript:void(null)" id="bp-setdimensions-tobg">Set To Bg</a><a href="javascript:void(null)" id="bp-show-bg-onchange-btn">Bg indicator</a><a href="javascript:void(null)" id="bp-dimension-correction-btn">Auto adjust</a><div class="bp-clear">&nbsp;</div></div><h2>Dimensions <span>(position prop required)</span></h2><div class="bp-fieldset"><div class="bp-input-title">Width</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="width-dragger" class="bp-slider-btn" ><input type="text" name="width" id="width" class="bp-drag bp-drag-wide" /><input type="text" name="width-unit" id="width-unit" class="bp-drag-unit" /></a><br /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title">Height</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="height-dragger" class="bp-slider-btn" ><input type="text" name="height" id="height" class="bp-drag bp-drag-wide" /><input type="text" name="height-unit" id="height-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><a href="javascript:void(null)" class="bp-move" id="bp-dimensions-move">Resize</a><div class="bp-clear">&nbsp;</div></div><h2>Offset <span>(position prop required)</span></h2><div class="bp-fieldset"><div class="bp-input-title">Left</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="bp-left-dragger" class="bp-slider-btn" ><input type="text" name="bp-left" id="bp-left" class="bp-drag bp-drag-wide" /><input type="text" name="bp-left-unit" id="bp-left-unit" class="bp-drag-unit" /></a><br /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title">Top</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="bp-top-dragger" class="bp-slider-btn" ><input type="text" name="bp-top" id="bp-top" class="bp-drag bp-drag-wide" /><input type="text" name="bp-top-unit" id="bp-top-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title">Right</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="bp-right-dragger" class="bp-slider-btn" ><input type="text" name="bp-right" id="bp-right" class="bp-drag bp-drag-wide" /><input type="text" name="bp-right-unit" id="bp-right-unit" class="bp-drag-unit" /></a><br /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title">Bottom</div><div class="bp-input-holder bp-slider-bg"><a href="javascript:void(null)" id="bp-bottom-dragger" class="bp-slider-btn" ><input type="text" name="bp-bottom" id="bp-bottom" class="bp-drag bp-drag-wide" /><input type="text" name="bp-bottom-unit" id="bp-bottom-unit" class="bp-drag-unit" /></a></div><div class="bp-clear">&nbsp;</div><a href="javascript:void(null)" id="bp-offset-move">Move</a><div class="bp-clear">&nbsp;</div></div></div><a href="javascript:void(null)" class="bp-slider-title-inactive"  id="bp-slider-positioning-properties">Positioning / background properties <span class="bp-shortcut-key"><b>[P]</b></span></a><div class="bp-slider-content" id="bp-slider-positioning-properties-content"><h2>Positioning properties</h2><div class="bp-fieldset"><div class="bp-input-title-medium"> Display </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="display" id="display" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Float </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="float" id="float" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Overflow </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="overflow" id="overflow" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Position </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="position" id="position" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Opacity </div><div class="bp-input-holder-medium bp-slider-bg-medium"><a href="javascript:void(null)" onclick="return false;" class="bp-slider-btn" id="opacity-dragger"><input type="text" name="opacity" id="opacity" class="bp-drag" /></a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Visibility </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="visibility" id="visibility" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Clear </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="clear" id="clear" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Cursor </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="cursor" id="cursor" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Z-index </div><div class="bp-input-holder-medium bp-slider-bg-medium"><a href="javascript:void(null)" onclick="return false;" class="bp-slider-btn" id="z-index-dragger"><input type="text" name="z-index" id="z-index" class="bp-drag" /></a></div><div class="bp-clear">&nbsp;</div></div><h2>Background properties</h2><div class="bp-fieldset"><div class="bp-input-title-medium"> Bg-color </div><div class="bp-input-holder bp-color-select"><input type="text" name="background-color" id="background-color" value="" class="bp-color-input" /><a href="javascript:void(null)" class="bp-background-color-picker-btn bp-color-select-btn">&nbsp;</a></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Image </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="background-image" id="background-image" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Repeat </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="background-repeat" id="background-repeat" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Attachment </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="background-attachment" id="background-attachment" class="bp-select" /></div><div class="bp-clear">&nbsp;</div><div class="bp-input-title-medium"> Position </div><div class="bp-input-holder-medium bp-popup-bg-medium"><input type="text" name="background-position" id="background-position" class="bp-select" /><div class="bp-clear">&nbsp;</div></div><div class="bp-clear">&nbsp;</div><a href="javascript:void(null)" id="bp-bg-pos-2d">Move</a><div class="bp-clear">&nbsp;</div></div></div><a href="javascript:void(null)" class="bp-slider-title-inactive"  id="bp-slider-textarea">Edit all <span class="bp-shortcut-key"><b>[T]</b></span></a><div class="bp-slider-content" id="bp-slider-textarea-content"><h2>Edit in textarea</h2><div class="bp-fieldset"><div id="bp-textarea-message"></div><textarea id="bp-editor-textarea" class="bp-textarea">asdf</textarea><a href="javascript:void(null)" id="bp-update-textarea">Update [F9]</a><div class="bp-clear">&nbsp;</div></div></div></div></div><div class="bp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat bp-footer-repeat-drag"><a href="javascript:void(null)" id="bp-save-btn">Save</a><a href="javascript:void(null)" id="bp-cancel-btn">Restore</a><a href="javascript:void(null)" class="bp-bluepen-link">www.bluepeneditor.com</a></div><div class="bp-footer-right bp-footer-right-drag">&nbsp;</div></div></div><div id="bp-toolbar"><div id="bp-toolbar-overlay">&nbsp;</div><div id="bp-toolbar-top">&nbsp;</div><div id="bp-toolbar-repeat"><a href="javascript:void(null)" id="bp-select-layer" class="bp-toolbar-btn">&nbsp;</a><div class="bp-toolbar-separator">&nbsp;</div><a href="javascript:void(null)" id="bp-icon-css-editor" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-icon-font-props" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-icon-box-props" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-icon-position-props" class="bp-toolbar-btn">&nbsp;</a><div class="bp-toolbar-separator">&nbsp;</div><a href="javascript:void(null)" id="bp-ruler-horizontal-creator" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-ruler-vertical-creator" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-debug-mode-btn" class="bp-toolbar-btn">&nbsp;</a><a href="javascript:void(null)" id="bp-toggle-info-win" class="bp-toolbar-btn bp-toolbar-btn-active">&nbsp;</a><a href="javascript:void(null)" id="bp-toggle-help-win" class="bp-toolbar-btn bp-toolbar-btn-active">&nbsp;</a><a href="javascript:void(null)" id="bp-toggle-backup-win" class="bp-toolbar-btn bp-toolbar-btn">&nbsp;</a></div><div id="bp-toolbar-bottom">&nbsp;</div></div><div id="bp-info-win" class="bp-panel"><div class="bp-title" id="bp-info-win-dragger"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat"><div class="bp-title-text bp-title-info">Information panel</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat"><h2>Coordinates</h2><div class="bp-fieldset" id="bp-coordinates"><div id="bp-mouse-pos-x"></div><div id="bp-mouse-pos-y"></div><div class="bp-clear">&nbsp;</div></div><h2>Edited</h2><div class="bp-fieldset" id="bp-edited-box"><div id="bp-edited-helptext"><center>To edit a selector, please select a layer!</center></div><a href="javascript:void(null)" id="bp-select-css-file-label">File:</a><span id="bp-edited-file" class="bp-blue">&nbsp;</span><a href="javascript:void(null)" id="bp-select-css-file">...</a><div class="bp-clear">&nbsp;</div><div id="bp-edited-style-label">Style:</div><div id="bp-edited-style" class="bp-blue">&nbsp;</div><textarea name="bp-new-style-textarea" id="bp-new-style-textarea"></textarea><div class="bp-clear">&nbsp;</div><div id="bp-new-style-selection-btns"><a href="javascript:void(null)" id="bp-inlinestyle-ok" class="bp-btn-ok">[Inline style]</a><a href="javascript:void(null)" id="bp-newstyle-ok" class="bp-btn-ok">OK</a><a href="javascript:void(null)" id="bp-newstyle-cancel" class="bp-btn-cancel">Cancel</a><div class="bp-clear">&nbsp;</div></div><a href="javascript:void(null)" id="bp-newstyle-btn">New style</a><a href="javascript:void(null)" id="bp-deletestyle-btn">Delete style</a><div class="bp-clear">&nbsp;</div></div><h2>State</h2><div id="bp-state-table"><div class="bp-row-even" id="bp-state-layer-sel"><a href="javascript:void(null)">layer selection mode</a><b class="bp-on">&nbsp;</b></div><div class="bp-row-odd"  id="bp-state-debug-mode"><a href="javascript:void(null)">debug mode</a><b class="bp-on">&nbsp;</b></div><div class="bp-row-even" id="bp-state-editing"><a href="javascript:void(null)">editing</a><b class="bp-on">&nbsp;</b></div><div class="bp-row-odd"  id="bp-state-dimm-corr"><a href="javascript:void(null)">dimension correction</a><b class="bp-on">&nbsp;</b></div><div class="bp-row-even" id="bp-state-show-bg"><a href="javascript:void(null)">background indicator</a><b class="bp-on">&nbsp;</b></div></div></div><div class="bp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat bp-footer-repeat-drag"><div id="bp-infowin-holder"><div id="bp-infowin-text-default">Done</div><div id="bp-infowin-text-message">Info panel</div></div></div><div class="bp-footer-right bp-footer-right-drag">&nbsp;</div></div></div><div id="bp-default-colorpicker"><div id="bp-default-picker-holder" class="bp-panel bp-colorpicker-holder"><div class="bp-title bp-colorp-title"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat bp-colorp-title-repeat"><div class="bp-title-text bp-colorp-text">default colorpicker</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat bp-colorp-repeat"><div id="bp-default-color-picker-bg"><div id="bp-colorpicker-default"><div id="bp-color-picker-holder-paramsname" class="bp-color-picker-holder bp-popup"><div class="bp-selected-color"><span>Selected color:</span><input type="text" name="paramsname" id="paramsname-preview" class="bp-colorp-preview-input" /><a href="javascript:void(null)" class="bp-transparent" id="bp-paramsname-transparent">&nbsp;</a><div id="bp-color-preview-paramsname" class="bp-colorp-preview">&nbsp;</div><div id="bp-color-preview-paramsname-live" class="bp-colorp-preview-live">&nbsp;</div><div class="bp-clear">&nbsp;</div></div><div class="bp-clear">&nbsp;</div><div class="bp-popup-cp-top">&nbsp;</div><div class="bp-popup-cp-repeat"><div id="bp-saturation-value-paramsname" class="bp-saturation-value"><div class="bp-saturation-img"><div class="bp-saturation-crosshair" id="bp-saturation-crosshair-paramsname">&nbsp;</div></div></div><div id="bp-hue-paramsname" class="bp-hue"><div class="bp-hue-img"><div id="bp-hue-position-paramsname" class="bp-hue-position">&nbsp;</div></div></div><div class="bp-saved-colors"><div class="bp-colorp-archive-top">&nbsp;</div><div id="bp-saved-colors-paramsname"></div></div><div class="bp-clear">&nbsp;</div></div><div class="bp-popup-cp-bottom">&nbsp;</div></div></div></div></div><div class="bp-footer bp-colorp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat bp-colorp-footer-repeat"><a href="javascript:void(null)" id="bp-cancel-btn-default" class="bp-cancel-btn">Cancel</a><a href="javascript:void(null)" id="bp-ok-btn-default" class="bp-ok-btn">Ok</a></div><div class="bp-footer-right">&nbsp;</div></div></div></div><div id="bp-debugger-holder" class="bp-panel bp-debugger-holder"><div class="bp-title bp-debugger-title" id="bp-info-win-dragger"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat bp-debugger-title-repeat"><div class="bp-title-text bp-title-logo bp-debugger-text">Debug panel</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close" id="bp-debugger-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat bp-debugger-repeat"><h2><a href="javascript:void(null)" id="bp-debugger-clear">Clear</a></h2><div class="bp-fieldset" id="bp-debugger-content"></div></div></div><div id="bp-message"></div><div id="bp-top-message-holder"><div id="bp-top-message-top">BluePen message</div><div id="bp-top-message">&nbsp;</div><div id="bp-top-message-bottom">&nbsp;</div></div><div id="bp-tooltip"><table border="0" cellpadding="0" cellspacing="0"><tr><td class="bp-tooltip-left">&nbsp;</td><td class="bp-tooltip-content" id="bp-tooltip-html">  </td><td class="bp-tooltip-right"></td></tr></table></div><div id="bp-img-preview"><div class="bp-title"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat"><div class="bp-title-text bp-title-info">File preview</div><div class="bp-title-btns">&nbsp;</div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat" id="bp-file-preview-img"> &nbsp; </div><div class="bp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat bp-footer-repeat" id="bp-file-preview-size"> file size </div><div class="bp-footer-right bp-footer-right">&nbsp;</div></div></div><div id="bp-help-win" class="bp-panel"><div class="bp-title" id="bp-help-win-dragger"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat"><div class="bp-title-text bp-title-help">Help panel</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat"><h2>Tutorials</h2><div class="bp-fieldset" id="bp-help-results"><center><img src="http://bluebros.net/bluepen/images/bp-help-search.png" alt="" /></center><br /><div class="hr">&nbsp;</div><div class="bp-help-result bp-fieldset"><h1><b>CSS</b> modul</h1><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><a href="#" class="bp-more">More info</a></div><div class="hr">&nbsp;</div><div class="bp-help-result bp-fieldset"><h1><b>CSS</b> modul</h1><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><a href="#" class="bp-more">More info</a></div><div class="hr">&nbsp;</div><div class="bp-help-result bp-fieldset"><h1><b>CSS</b> modul</h1><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p><a href="#" class="bp-more">More info</a></div></div></div><div class="bp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat" id="bp-help-footer"><a href="javascript:void(null)" class="bp-bluepen-link">www.bluepeneditor.com</a></div><div class="bp-footer-right">&nbsp;</div></div></div><div id="bp-backup-win" class="bp-panel"><div class="bp-title" id="bp-backup-win-dragger"><div class="bp-title-left">&nbsp;</div><div class="bp-title-repeat"><div class="bp-title-text bp-title-backup">Backup panel</div><div class="bp-title-btns"><a href="javascript:void(null)" class="bp-btn-minimize">&nbsp;</a><a href="javascript:void(null)" class="bp-btn-close">&nbsp;</a></div></div><div class="bp-title-right">&nbsp;</div></div><div class="bp-repeat"><div id="bp-backup-list"></div><center><a href="javascript:void(null)" id="bp-backup-ok" class="bp-btn-ok">OK</a><a href="javascript:void(null)" id="bp-backup-cancel" class="bp-btn-cancel">Cancel</a></center></div><div class="bp-footer"><div class="bp-footer-left">&nbsp;</div><div class="bp-footer-repeat" id="bp-help-footer"><a href="javascript:void(null)" class="bp-bluepen-link">www.bluepeneditor.com</a></div><div class="bp-footer-right">&nbsp;</div></div></div><div id="bp-highlight">&nbsp;</div></div><div id="bp-msg-rendering"><img src="http://bluebros.net/bluepen/images/89.gif" alt="" /></div><div id="bp-prop-applied-dummy">&nbsp;</div>
<?php
	}

}

$error_messages = array();

// error handler function
function bluePenErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $error_messages;
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
	
	$output_so_far = ob_get_contents();
	ob_end_clean();

	$ei = count($error_messages);
	$error_messages[$ei]["errno"] = $errno;
	$error_messages[$ei]["errstr"] = $errstr;
	$error_messages[$ei]["errline"] = $errline;
	$error_messages[$ei]["errfile"] = $errfile;
	$error_messages[$ei]["php_version"] = PHP_VERSION;
	$error_messages[$ei]["php_os"] = PHP_OS;
	$error_messages[$ei]["message"] = $output_so_far;

    /* Don't execute PHP internal error handler */
    return true;
}

// egyseges output formatum. Csak a vegpontokban hivhato meg.
function send_output() {
	global $error_messages;
	$output_so_far = ob_get_contents();
	ob_end_clean();
	$output = array();
	if (count($error_messages)) {
		$output["error_reporting"] = $error_messages;
	}
	$output["output"] = $output_so_far;
	echo json_encode($output);
	exit(); 
}

?>
