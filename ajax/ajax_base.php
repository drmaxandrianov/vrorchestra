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

   require_once("ajax/ajax_get_world_data.php");
    require_once("ajax/ajax_get_robot_data.php");
    require_once("ajax/ajax_set_robot_data.php");
?>
