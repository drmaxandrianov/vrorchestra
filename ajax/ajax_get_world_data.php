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

    $query = "select * from $WORLDS_TABLE where Id=$id";
    $data = mysql_query($query);

    // Create response string
    if (mysql_num_rows($data) != 0) {
        while($robot = mysql_fetch_array($data)) { 
            $world_name = $robot['Name'];
            $world_id = $robot['Id'];
            $world_data = "world data";

            $doc = new DOMDocument();
            $doc->formatOutput = true;

            // Write data to XML file
            $base = $doc->createElement('get_world_data');

            $field = $doc->createElement("name");
            $field->appendChild($doc->createTextNode($world_name)); 
            $base->appendChild($field);

            $field = $doc->createElement("id");
            $field->appendChild($doc->createTextNode($world_id)); 
            $base->appendChild($field);

            $field = $doc->createElement("world_id");
            $field->appendChild($doc->createTextNode($world_data)); 
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
