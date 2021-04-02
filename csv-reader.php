<?php

// Define options.
$shortopts  = "";
$shortopts .= "u:";  // MySQl Username.
$shortopts .= "p:";  // MySQl Password.
$shortopts .= "h:";  // MySQl Host.

$longopts  = array(
    "file:",         // Name of the CSV.
    "create_table:", // Build users table.
    "dry_run:",      // Run script but not insert into DB.
    "help:",         // Output list of directives.
);

$options = getopt($shortopts, $longopts);

$records = readCsvFile($options['file']);


/**
 * Helper function to read CSV.
 * 
 * @param $file
 *  CSV File to be read. 
 * @return $records
 *  Output of CSV. 
 */
function readCsvFile($file) {
    $records = array();

    // Get file handler.
    $fileHandle = fopen($file, "r");

    // Read Header Row.
    $row = fgetcsv($fileHandle);
    $columns = array();
    foreach ($row as $i => $header) {
    $columns[$i] = trim($header);
    }

    // Load each row.
    while ($row = fgetcsv($fileHandle)) {
    $record = array();
    foreach ($row as $i => $field) {
        $record[$columns[$i]] = $field;
    }
    // Push into array.
    array_push($records, $record);
    }
    // Close file.
    fclose($fileHandle);

    return $records;
}

?>