<?php

// Define options.
$shortopts  = "";
$shortopts .= "u:";  // MySQl Username.
$shortopts .= "p:";  // MySQl Password.
$shortopts .= "h:";  // MySQl Host.
$shortopts .= "d:";  // MySQl Database.

$longopts  = array(
    "file:",         // Name of the CSV.
    "create_table", // Build users table.
    "dry_run",      // Run script but not insert into DB.
    "help",         // Output list of directives.
);

$options = getopt($shortopts, $longopts);

runScript($options);

/**
 * Main function.
 * 
 * @param $options
 *  Options passed by command line.
 */
function runScript($options) {

    // Check if --help is .
    if (array_key_exists('help', $options)) {
        $optionDescriptions = [
            '-u' => 'My SQL Username',
            '-p' => 'My SQL Password',
            '-h' => 'My SQL Host',
            '-d' => 'My SQL Database',
            '--file' => 'Name of the CSV File',
            '--create_table' => 'Build users table',
            '--dry_run' => 'Run script but not insert into DB',
        ];        
        print_r($optionDescriptions);
        exit();
    }

    // Check if db information was passed.
    if (array_key_exists('h', $options) && array_key_exists('u', $options)
            && array_key_exists('p', $options) && array_key_exists('d', $options)) {
        $dbhost = $options['h'];
        $dbuser = $options['u'];
        $dbpass = $options['p'];
        $dbname = $options['d'];
        $conn = connectToDatabase($dbhost, $dbuser, $dbpass, $dbname);
    }

    // Here we are rebuilding the user table if create_table is passed.
    if (array_key_exists('create_table', $options)) {
        if (isset($conn)) {
            dropUserTable($conn); 
            createUserTable($conn);
            closeDatabase($conn);
            exit();
        } else {
            echo 'Failed to connect to Database.';
            echo 'Please make sure you have provided DB information.';
            exit();
        }
    
    }

    
    // Check if file was passed.
    if (array_key_exists('file', $options)) {
        $filename = $options['file'];
        $extension = get_file_extension($filename);
        if ($extension == 'csv') {
            $records = readCsvFile($filename);
        } else {
            echo 'Please pass a csv file.\n';
            exit();
        }
    } else {
        echo 'Please pass a csv file.\n';
        exit();
    }

    // Check if dry_run was passed.
    if (array_key_exists('dry_run', $options)) {
        print_r($records);
        exit();
    }

    // Check if connection was established.
    if ($conn) {
        dropUserTable($conn); 
        createUserTable($conn);
        insertIntoUserTable($conn, $records);
        closeDatabase($conn);
    }
}

/**
 * Helper functoin to get file extenstion.
 * 
 * @param $file_name
 *  Name of file.
 */
function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
}

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
 * 
 * @param $dbhost
 *  DB Host
 * @param $dbuser
 *  DB User
 * @param $dbpass
 *  DB Password
 * 
 * @return $conn
 *  mysqli Object.
 */
function connectToDatabase($dbhost, $dbuser, $dbpass, $dbname) {

    // Create connection
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    echo "Connected successfull\n";

    return $conn;

}


/**
 * Helper function to close database connection.
 * 
 * @param $conn
 *  mysqli Object.
 */
function closeDatabase($conn) {
    mysqli_close($conn);
}

/**
 * Helper funciton to create user table in DB.
 * 
 * @param $conn
 *  mysqli Object.
 */
function createUserTable($conn) {

    // SQL for creating user.
    $sql = "CREATE TABLE users (
        email VARCHAR(50) PRIMARY KEY,
        surname VARCHAR(30) NOT NULL,
        firstname VARCHAR(30) NOT NULL
    )";

    if (mysqli_query($conn, $sql)) {
        echo "Table created successfully.\n";
    } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn) .'\n';
    }
}

/**
 * Helper funciton to drop user table in DB.
 * 
 * @param $conn
 *  mysqli Object.
 */
function dropUserTable($conn) {
    $sql = "DROP TABLE IF EXISTS users";

    if (mysqli_query($conn, $sql)) {
        echo "Table dropped successfully.\n";
    } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn) .'\n';
    }
}

/**
 * Inserts records from CSV into user table.
 * 
 * @param $conn
 *  mysqli Object.
 * @param $records.
 *  Array of records from CSV.
 */
function insertIntoUserTable($conn, $records) {
    foreach ($records as $record) {
        
        // Validate Email.
        $email = strtolower($record['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "The following record was not inserted, as email address was not valid.\n";
            print_r($record);
            continue;
        }
        // Capatlise first letter.
        $firstname = ucfirst(strtolower($record['name']));
        $surname = ucfirst(strtolower($record['surname']));

        $sql = 'INSERT INTO users (email, firstname, surname)
            VALUES ("' . $email . '", "' . $firstname . '", "' . $surname . '")';

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully.\n";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error .'\n';
        }
    }
}

?>