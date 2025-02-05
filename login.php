<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->

<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// Start the session
session_start();

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_jhuang74"; // change "cwl" to your own CWL
$config["dbpassword"] = "a66382623"; // change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL; // Database connection handle

// Function to connect to the Oracle database
function connectToDB() {
    global $db_conn, $config;
    $db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);
    if ($db_conn) {
        return true;
    } else {
        // Handle connection error
        $e = oci_error(); // For oci_connect errors pass no handle
        echo htmlentities($e['message']);
        return false;
    }
}

// Function to disconnect from the database
function disconnectFromDB() {
    global $db_conn;
    oci_close($db_conn);
}

// Function to handle user login or account creation
function handleInsertRequest() {
    global $db_conn;

    // Get input values from the login form
    $username = trim($_POST['insName']);
    $age = trim($_POST['age']);

    // Check if the username and age exist in the database
    $checkUserQuery = "SELECT * FROM \"User\" WHERE name = :username AND age = :age";
    $checkStmt = oci_parse($db_conn, $checkUserQuery);
    oci_bind_by_name($checkStmt, ":username", $username);
    oci_bind_by_name($checkStmt, ":age", $age);
    oci_execute($checkStmt);

    if (oci_fetch($checkStmt)) {
        // If user exists, log them in as a returning user
        $_SESSION['username'] = $username;
        $_SESSION['age'] = $age;
        $_SESSION['is_new_user'] = false; // Mark as a returning user
    } else {
        // User does not exist, proceed to create a new account

        // Check if the username exists with a different age
        $checkNameOnlyQuery = "SELECT * FROM \"User\" WHERE name = :username";
        $checkNameStmt = oci_parse($db_conn, $checkNameOnlyQuery);
        oci_bind_by_name($checkNameStmt, ":username", $username);
        oci_execute($checkNameStmt);

        if (oci_fetch($checkNameStmt)) {
            // Username exists with a different age
            // Store a note message to inform the user
            $_SESSION['note_message'] = "Note: The username '$username' exists with a different age. Creating a new account...";
        }

        // Generate a new user ID
        $newUserIdQuery = "SELECT NVL(MAX(ID), 0) + 1 AS new_id FROM \"User\"";
        $newUserIdStmt = oci_parse($db_conn, $newUserIdQuery);
        oci_execute($newUserIdStmt);
        $newIdRow = oci_fetch_assoc($newUserIdStmt);
        $newUserId = $newIdRow['NEW_ID'];

        // Insert the new user into the database
        $insertUserQuery = "INSERT INTO \"User\" (ID, name, age) VALUES (:id, :username, :age)";
        $insertStmt = oci_parse($db_conn, $insertUserQuery);
        oci_bind_by_name($insertStmt, ":id", $newUserId);
        oci_bind_by_name($insertStmt, ":username", $username);
        oci_bind_by_name($insertStmt, ":age", $age);

        if (oci_execute($insertStmt)) {
            oci_commit($db_conn);
            // Account created successfully, set session variables
            $_SESSION['username'] = $username;
            $_SESSION['age'] = $age;
            $_SESSION['is_new_user'] = true; // Mark as a new user
        } else {
            // Error occurred during account creation
            $_SESSION['error_message'] = "Error: Unable to create account. Please try again.";
            return;
        }
    }

    // Redirect to project.php after successful login or account creation
    header("Location: project.php");
    exit;
}

// Function to handle POST requests
function handlePOSTRequest() {
    if (connectToDB()) {
        if (array_key_exists('insertQueryRequest', $_POST)) {
            handleInsertRequest();
        }
        disconnectFromDB();
    }
}

// Check if the login form was submitted
if (isset($_POST['insertSubmit'])) {
    handlePOSTRequest();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <h2>User Login</h2>
    <h4 class="centered-text" style="text-align: center;">
    If your username and age are already registered, use them to log in! Otherwise, create a new account by entering your username and age!
</h4>
    <form method="POST" action="login.php">
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        Username: <input type="text" name="insName" required> <br /><br />
        Age: <input type="number" name="age" required> <br /><br />
        <input type="submit" value="Login" name="insertSubmit">
    </form>
</body>
</html>