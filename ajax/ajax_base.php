<?php
	require_once("common/common_constants.php");
	require_once("common/common_before_loading.php");
    require_once("common/common_start_session.php");

    // Common functions for ajax requests
    function is_blank($value) {
        return empty($value) && !is_numeric($value);
    }
    function return_error($value) {
        echo $value;
        exit;
    }
    function return_data($value) {
        echo $value;
        exit;
    }
    function get_pixel_color($image, $x, $y) {
        $im_w = imagesx($image);
        $im_h = imagesy($image);

        if ($x < 0 || $x >= $im_w)
            return "empty";
        if ($y < 0 || $y >= $im_h)
            return "empty";

        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        if ($r == 0xFF) return "red";
        if ($g == 0xFF) return "green";
        if ($b == 0xFF) return "blue";

        return "empty";
    }

    require_once("ajax/ajax_get_world_data.php");
    require_once("ajax/ajax_get_robot_data.php");
    require_once("ajax/ajax_set_robot_data.php");
?>
