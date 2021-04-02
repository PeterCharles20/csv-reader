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

$dbhost = $options['h'];
$dbuser = $options['u'];
$dbpass = $options['p'];

// $records = readCsvFile($options['file']);

$conn = connectToDatabase($dbhost, $dbuser, $dbpass);
createTable($conn);
closeDatabase($conn);

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


/**
 * Helper function to connect to mysql db
 */
function connectToDatabase($dbhost, $dbuser, $dbpass) {

    // Create connection
    $conn = new mysqli($dbhost, $dbuser, $dbpass, 'catalyst');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    echo "Connected successfully";

    return $conn;

}

/**
 * Helper function to close database connection.
 */
function closeDatabase($conn) {
    mysqli_close($conn);
}


?>