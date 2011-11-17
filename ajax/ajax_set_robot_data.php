<?php

?>
<?php
if (isset($_GET['GET_WORLD_DATA'])) {

    // Check input parameters
    if (!isset($_GET['id']) || is_blank($_GET['id'])) {
        return_error("Error: 'id' parameter is not given.");
    }
    if (!isset($_GET['pos_x']) || is_blank($_GET['pos_x'])) {
        return_error("Error: 'pos_x' parameter is not given.");
    }
    if (!isset($_GET['pos_y']) || is_blank($_GET['pos_y'])) {
        return_error("Error: 'pos_y' parameter is not given.");
    }
    if (!isset($_GET['view_angle']) || is_blank($_GET['view_angle'])) {
        return_error("Error: 'view_angle' parameter is not given.");
    }
    if (!isset($_GET['data_type']) || is_blank($_GET['data_type'])) {
        return_error("Error: 'data_type' parameter is not given.");
    }



    // Parse input variables
    $id = $_GET['id'];
    $pos_x = $_GET['pos_x'];
    $pos_y = $_GET['pos_y'];
    $view_angle = $_GET['view_angle'];
    $data_type = $_GET['data_type'];


    // Connect to DB
    $dbc = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
    mysql_select_db($DB_NAME);

    $query = "select * from $WORLDS_TABLE where Id=$id";
    $data = mysql_query($query);

    // Create response string
    if (mysql_num_rows($data) != 0) {
        while($world = mysql_fetch_array($data)) { 
            $world_name = $world['Name'];
            $world_id = $world['Id'];

            // Read data from world image
            $world_image = $world['Image'];
            $im = imagecreatefrompng($world_image);

            $doc = new DOMDocument();
            $doc->formatOutput = true;

            // Write data to XML file
            $base = $doc->createElement('get_world_data');

            $field = $doc->createElement("name");
            $field->appendChild($doc->createTextNode($world_name));
            $base->appendChild($field);

            $field = $doc->createElement("data_type");
            $field->appendChild($doc->createTextNode($data_type));
            $base->appendChild($field);

            $field = $doc->createElement("pox_x");
            $field->appendChild($doc->createTextNode($pos_x));
            $base->appendChild($field);

            $field = $doc->createElement("pos_y");
            $field->appendChild($doc->createTextNode($pos_y));
            $base->appendChild($field);

            $field = $doc->createElement("view_angle");
            $field->appendChild($doc->createTextNode($view_angle));
            $base->appendChild($field);

            // Types of available data receving
            // -- 3x_matrix
            $pixels = array();
            $field = $doc->createElement("data");
            if ($data_type == "3x_matrix") {
                for ($i = -1; $i < 2; $i++) {
                    for ($j = -1; $j < 2; $j++) {
                        $bit = $doc->createElement("pixel_" . ($pos_x + $j) . "_" . ($pos_y + $i));
                        $pixels[] = $pixel = get_pixel_color($im, $pos_x + $j, $pos_y + $i);
                        $bit->appendChild($doc->createTextNode($pixel));
                        $field->appendChild($bit);
                    }
                }
            }
            $base->appendChild($field);

            $field = $doc->createElement("view_angle");
            $field->appendChild($doc->createTextNode($view_angle));

            $doc->appendChild($base);

            return_data($doc->saveXML());
        }
    } else {
        return_error("Error: no world exist with given 'id'.");
    }
    mysql_close($dbc);
}
?>
