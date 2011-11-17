<?php
if (isset($_GET['GET_WORLD_DATA'])) {

    // Check input parameters
    if (!isset($_GET['id']) || is_blank($_GET['id'])) {
        return_error("Error: 'id' parameter is not given.");
    }

    // Parse input variables
    $id = $_GET['id'];

    // Connect to DB
    $dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
    mysql_select_db($DB_NAME);

    $query = "select * from $ROBOTS_TABLE where Id=$id";
    $data = mysql_query($query);

    // Create response string
    if (mysql_num_rows($data) != 0) {
        while($robot = mysql_fetch_array($data)) { 
            $robot_name = $robot['Name'];
            $robot_id = $robot['Id'];
            $robot_world_id = $robot['WorldId'];
            $robot_pos_x = $robot['PosX'];
            $robot_pos_y = $robot['PosY'];
            $robot_view_angle = $robot['ViewAngle'];

            $doc = new DOMDocument();
            $doc->formatOutput = true;

            // Write data to XML file
            $base = $doc->createElement('get_robot_data');

            $field = $doc->createElement("name");
            $field->appendChild($doc->createTextNode($robot_name)); 
            $base->appendChild($field);

            $field = $doc->createElement("id");
            $field->appendChild($doc->createTextNode($robot_id)); 
            $base->appendChild($field);

            $field = $doc->createElement("world_id");
            $field->appendChild($doc->createTextNode($robot_world_id)); 
            $base->appendChild($field);

            $field = $doc->createElement("pos_x");
            $field->appendChild($doc->createTextNode($robot_pos_x));
            $base->appendChild($field);

            $field = $doc->createElement("pos_y");
            $field->appendChild($doc->createTextNode($robot_pos_y)); 
            $base->appendChild($field);

            $field = $doc->createElement("view_angle");
            $field->appendChild($doc->createTextNode($robot_view_angle)); 
            $base->appendChild($field);

            $doc->appendChild($base);
            
            return_data($doc->saveXML());
        }
    } else {
        return_error("Error: no world exist with given 'id'.");
    }
    mysql_close($dbc);
}
?>
