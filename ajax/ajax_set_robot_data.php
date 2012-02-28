<?php
if (isset($_POST['SET_ROBOT_DATA'])) {

    // Check input parameters
    if (!isset($_POST['id']) || is_blank($_POST['id'])) {
        return_error("Error: 'id' parameter is not given.");
    }
    if (!isset($_POST['pos_x']) || is_blank($_POST['pos_x'])) {
        return_error("Error: 'pos_x' parameter is not given.");
    }
    if (!isset($_POST['pos_y']) || is_blank($_POST['pos_y'])) {
        return_error("Error: 'pos_y' parameter is not given.");
    }
    if (!isset($_POST['view_angle']) || is_blank($_POST['view_angle'])) {
        return_error("Error: 'view_angle' parameter is not given.");
    }
    if (!isset($_POST['world_id']) || is_blank($_POST['world_id'])) {
        return_error("Error: 'world_id' parameter is not given.");
    }



    // Parse input variables
    $id = $_POST['id'];
    $pos_x = $_POST['pos_x'];
    $pos_y = $_POST['pos_y'];
    $view_angle = $_POST['view_angle'];
    $world_id = $_POST['world_id'];


    // Connect to DB
    $dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
    mysql_select_db($DB_NAME);

    $query = "update $ROBOTS_TABLE set PosX='$pos_x', PosY='$pos_y' \
              ViewAngle='$view_angle', WorldId='$world_id' where Id=$id";
    $data = mysql_query($query);
    mysql_close($dbc);


    // Make responsem, that everything was saved
    return_data("saved");
}
?>
