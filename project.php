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

// Database access configuration
$config["dbuser"] = "ora_jhuang74";      // change "cwl" to your own CWL
$config["dbpassword"] = "a66382623";     // change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;    // login credentials are used in connectToDB()

$success = true;    // keep track of errors so page redirects only if there are no errors
$show_debug_alert_messages = false; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<!DOCTYPE html>
<html>
<head>
    <title>Discover Your Next Favorite Restaurant</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <h1 style="text-align: center;">Discover Your Next Favorite Restaurant</h1>
    <p>Rate, review, and explore the best dining experiences in your area.</p>

    <!-- User Information Section -->
    <!-- Assuming $welcomeMessage is set in session -->
    <?php
    // Handle the request
    handleRequest();

    // Retrieve user information from session variables
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    $age = isset($_SESSION['age']) ? $_SESSION['age'] : 'N/A';
    $is_new_user = isset($_SESSION['is_new_user']) ? $_SESSION['is_new_user'] : false;

    // Prepare the welcome message based on whether the user is new or returning
    if ($is_new_user) {
        $welcomeMessage = "Welcome, new user $username (Age: $age)!";
    } else {
        $welcomeMessage = "Welcome back, $username (Age: $age)!";
    }
    ?>
    <h2>User Information</h2>
    <p><?php echo $welcomeMessage; ?></p>

    <hr />

    <!-- Search for Restaurants Section -->
    <h2>Search for a Restaurant</h2>
    <form method="GET" action="">
        <input type="hidden" name="searchRestaurantRequest">
        <p>Select the restaurant to search for:</p>
        <select name="restaurantName" required>
            <?php echo getRestaurantOptions(); ?>
        </select>
        <br /><br />
        <input type="submit" value="Search" name="searchRestaurantSubmit">
    </form>

    <hr />

    <!-- Add a Restaurant Section -->
    <h2>Add a Restaurant</h2>
    <form method="POST" action="">
        <input type="hidden" name="addRestaurantRequest">
        Restaurant Name: <input type="text" name="name" required> <br /><br />
        Owner Name: <input type="text" name="ownerName" required> <br /><br />
        Rating (0-5): <input type="number" name="rating" min="0" max="5" step="0.1"> <br /><br />
        <input type="submit" value="Add Restaurant" name="addSubmit">
    </form>

    <hr />

    <!-- Update Restaurant Details Section -->
    <h2>Update Restaurant Details</h2>

    <?php
    $restaurants = getAllRestaurantsWithOwners();
    if (!empty($restaurants)) {
        echo "<table border='1'>";
        echo "<tr><th>Restaurant Name</th><th>Owner Name</th><th>Rating</th></tr>";
        foreach ($restaurants as $restaurant) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($restaurant['restaurant_name']) . "</td>";
            echo "<td>" . htmlspecialchars($restaurant['owner_name']) . "</td>";
            echo "<td>" . htmlspecialchars($restaurant['rating']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br />";
    } else {
        echo "<p>No restaurants available to update.</p>";
    }
    ?>

    <form method="POST" action="">
        <input type="hidden" name="updateRestaurantRequest">
        <p>Select the Restaurant to Update:</p>
        <select name="restaurantToUpdate" required>
            <?php echo getRestaurantOptions(); ?>
        </select>
        <br /><br />
        Enter New Owner Name (leave blank if no change):
        <input type="text" name="newOwnerName" placeholder="Enter new owner name">
        <br /><br />
        Enter New Rating (leave blank if no change):
        <input type="number" name="newRating" min="0" max="5" step="0.1" placeholder="Enter new rating">
        <br /><br />
        <input type="submit" value="Update Restaurant" name="updateRestaurantSubmit">
    </form>

    <hr />

    <!-- Delete a Restaurant Section -->
    <h2>Delete a Restaurant</h2>
    <form method="POST" action="">
        <input type="hidden" name="deleteRestaurantRequest">
        <p>Select the restaurant to delete:</p>
        <select name="restaurantToDelete" required>
            <?php echo getRestaurantOptions(); ?>
        </select>
        <br /><br />
        <input type="submit" value="Delete Restaurant" name="deleteRestaurantSubmit">
    </form>

    <hr />

    <!-- Search Dishes Section -->
    <h2>Search Dishes</h2>
    <form method="POST" action="">
        <input type="hidden" name="searchDishesRequest">
        <p>Enter your search criteria:</p>
        <div id="conditions">
            <!-- Condition rows will be added here dynamically -->
        </div>
        <button type="button" onclick="addCondition()">Add Condition</button>
        <br /><br />
        <input type="submit" value="Search Dishes" name="searchDishesSubmit">
    </form>

    <script>
        let conditionCount = 0;

        function addCondition() {
            conditionCount++;

            const conditionDiv = document.createElement('div');
            conditionDiv.id = 'condition' + conditionCount;

            let conditionHTML = '';

            // Add 'Combine with' logical operator only if this is not the first condition
            if (conditionCount > 1) {
                conditionHTML += `
                    Combine with:
                    <select name="logical${conditionCount - 1}">
                        <option value="AND">AND</option>
                        <option value="OR">OR</option>
                    </select>
                    <br /><br />
                `;
            }

            conditionHTML += `
                <p>Condition ${conditionCount}:</p>
                Field:
                <select name="field${conditionCount}" onchange="updateOperators(${conditionCount})" id="field${conditionCount}">
                    <option value="price">Price</option>
                    <option value="name">Dish Name</option>
                    <option value="restaurant_name">Restaurant Name</option>
                </select>
                Operator:
                <select name="operator${conditionCount}" id="operator${conditionCount}">
                    <!-- Operators will be populated based on field selection -->
                </select>
                Value:
                <input type="text" name="value${conditionCount}">
                <button type="button" onclick="removeCondition(${conditionCount})">Remove</button>
                <hr />
            `;

            conditionDiv.innerHTML = conditionHTML;
            document.getElementById('conditions').appendChild(conditionDiv);

            // Initialize operators for the selected field
            updateOperators(conditionCount);
        }

        function removeCondition(id) {
            const conditionDiv = document.getElementById('condition' + id);
            conditionDiv.parentNode.removeChild(conditionDiv);
        }

        function updateOperators(conditionIndex) {
            const fieldSelect = document.getElementById(`field${conditionIndex}`);
            const operatorSelect = document.getElementById(`operator${conditionIndex}`);
            const selectedField = fieldSelect.value;

            // Clear existing operators
            operatorSelect.innerHTML = '';

            // Define operator options for numeric and string fields
            const numericOperators = ['=', '<>', '>', '<', '>=', '<='];
            const stringOperators = ['='];

            let operators = [];

            if (selectedField === 'price') {
                operators = numericOperators;
            } else {
                operators = stringOperators;
            }

            // Populate the operator dropdown
            operators.forEach(op => {
                const option = document.createElement('option');
                option.value = op;
                option.text = op;
                operatorSelect.add(option);
            });
        }

        // Automatically add the first condition when the page loads
        window.onload = function() {
            addCondition();
        };
    </script>

    <hr />

    <hr />

    <!-- View Employees Section -->
    <h2>View Employee Details</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="projectionRequest" name="projectionRequest">
        <p>Select attributes to display:</p>
        <input type="checkbox" name="attributes[]" value="ID" checked>ID
        <input type="checkbox" name="attributes[]" value="NAME" checked>Name
        <input type="checkbox" name="attributes[]" value="HOURS_PER_WEEK">Hours Per Week
        <input type="checkbox" name="attributes[]" value="HOURLY_WAGE">Hourly Wage
        <br /><br />
        <input type="submit" value="View Employees" name="projectionSubmit">
    </form>

    <hr />

    <!-- Find Chefs by Restaurant -->
    <h2>Find Chefs by Restaurant</h2>
    <form method="POST" action="">
        <input type="hidden" name="joinRequest">
        <p>Select the restaurant:</p>
        <select name="restaurantName" required>
            <?php echo getRestaurantOptions(); ?>
        </select>
        <br /><br />
        <input type="submit" value="Find Chefs" name="joinSubmit">
    </form>

    <hr />

    <!-- Aggregation with GROUP BY -->
    <h2>Average Dish Price by Restaurant</h2>
    <form method="POST" action="">
        <input type="hidden" id="aggregationRequest" name="aggregationRequest">
        <input type="submit" value="Show Average Prices" name="aggregationSubmit">
    </form>

    <hr />

    <!-- Aggregation with HAVING -->
    <h2>Restaurants with More Than 2 Dishes</h2>
    <form method="POST" action="">
        <input type="hidden" id="havingRequest" name="havingRequest">
        <input type="submit" value="Show Restaurants" name="havingSubmit">
    </form>

    <hr />

    <!-- Nested Aggregation with GROUP BY -->
    <h2>Restaurant with Highest Average Chef Skill Level:</h2>
    <form method="POST" action="">
        <input type="hidden" id="nestedAggregationRequest" name="nestedAggregationRequest">
        <input type="submit" value="Show Chefs" name="nestedAggregationSubmit">
    </form>

    <hr />

    <!-- Division Query -->
    <h2>Users Who Have Eaten All Dishes</h2>
    <form method="POST" action="">
        <input type="hidden" id="divisionRequest" name="divisionRequest">
        <input type="submit" value="Show Users" name="divisionSubmit">
    </form>

    <hr />

    <!-- View All Restaurants Query -->
    <h2>View All Restaurants</h2>
    <form method="POST" action="">
        <input type="hidden" name="viewAllRestaurants" value="1">
        <input type="submit" value="Show All Restaurants">
    </form>

    <?php
	// The following code will be parsed as PHP

    function debugAlertMessage($message)
    {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr)
    {
        //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
        global $db_conn, $success;

        $statement = oci_parse($db_conn, $cmdstr);
        //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = oci_error($db_conn);
            echo htmlentities($e['message']);
            $success = false;
        }

        $r = oci_execute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement);
            echo htmlentities($e['message']);
            $success = false;
        }

        return $statement;
    }

    function executeBoundSQL($cmdstr, $list)
    {
        /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */
        global $db_conn, $success;
        $statement = oci_parse($db_conn, $cmdstr);

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = oci_error($db_conn);
            echo htmlentities($e['message']);
            $success = false;
        }

        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
				//echo "<br>".$bind."<br>";
                oci_bind_by_name($statement, $bind, $val);
                unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
            }

            $r = oci_execute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For oci_execute errors, pass the statementhandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = false;
            }
        }

        return $statement;
     }

    function connectToDB()
    {
        global $db_conn, $config;

        // Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
        $db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

        if ($db_conn) {
            debugAlertMessage("Database is Connected");
            return true;
        } else {
            debugAlertMessage("Cannot connect to Database");
            $e = oci_error(); // For oci_connect errors pass no handle
            echo htmlentities($e['message']);
            return false;
        }
    }

    function disconnectFromDB()
    {
        global $db_conn;
        debugAlertMessage("Disconnect from Database");
        oci_close($db_conn);
    }

    // Function to get restaurant options for dropdown menus
    function getRestaurantOptions()
    {
        $options = "";
        if (connectToDB()) {
            global $db_conn;
            $query = "SELECT name FROM Restaurant";
            $stmt = oci_parse($db_conn, $query);
            oci_execute($stmt);
            while ($row = oci_fetch_assoc($stmt)) {
                $name = htmlspecialchars($row['NAME']);
                $options .= "<option value=\"$name\">$name</option>";
            }
            disconnectFromDB();
        } else {
            $options = "<option value=\"\">Error loading restaurants</option>";
        }
        return $options;
    }

    // Function to handle adding a new restaurant
    function handleAddRestaurant()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get input values and trim whitespace
            $name = trim($_POST['name']);
            $ownerName = trim($_POST['ownerName']);
            $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;

            // Check if the restaurant already exists
            $checkRestaurantQuery = "SELECT name FROM Restaurant WHERE name = :name";
            $checkRestaurantStmt = oci_parse($db_conn, $checkRestaurantQuery);
            oci_bind_by_name($checkRestaurantStmt, ":name", $name);
            oci_execute($checkRestaurantStmt);
            $restaurantRow = oci_fetch_assoc($checkRestaurantStmt);

            if ($restaurantRow) {
                // Restaurant already exists
                echo "<p>Error: A restaurant with the name '$name' already exists.</p>";
                disconnectFromDB();
                return;
            }

            // Check if the owner exists
            $getOwnerIdQuery = "SELECT ID FROM Owner WHERE name = :ownerName";
            $getOwnerIdStmt = oci_parse($db_conn, $getOwnerIdQuery);
            oci_bind_by_name($getOwnerIdStmt, ":ownerName", $ownerName);
            oci_execute($getOwnerIdStmt);
            $ownerRow = oci_fetch_assoc($getOwnerIdStmt);

            if ($ownerRow) {
                $ownerId = $ownerRow['ID'];
            } else {
                // Owner does not exist, create a new owner
                $newOwnerIdQuery = "SELECT NVL(MAX(ID), 0) + 1 AS new_id FROM Owner";
                $newOwnerIdStmt = oci_parse($db_conn, $newOwnerIdQuery);
                oci_execute($newOwnerIdStmt);
                $newIdRow = oci_fetch_assoc($newOwnerIdStmt);
                $ownerId = $newIdRow['NEW_ID'];

                $insertOwnerQuery = "INSERT INTO Owner (ID, name) VALUES (:id, :name)";
                $insertOwnerStmt = oci_parse($db_conn, $insertOwnerQuery);
                oci_bind_by_name($insertOwnerStmt, ":id", $ownerId);
                oci_bind_by_name($insertOwnerStmt, ":name", $ownerName);

                if (!oci_execute($insertOwnerStmt, OCI_DEFAULT)) {
                    oci_rollback($db_conn);
                    $e = oci_error($insertOwnerStmt);
                    echo "<p>Error: Unable to create new owner. " . htmlentities($e['message']) . "</p>";
                    disconnectFromDB();
                    return;
                }
            }

            // Insert the new restaurant
            $insertRestaurantQuery = "INSERT INTO Restaurant (name, owner_ID, rating) VALUES (:name, :ownerId, :rating)";
            $insertRestaurantStmt = oci_parse($db_conn, $insertRestaurantQuery);
            oci_bind_by_name($insertRestaurantStmt, ":name", $name);
            oci_bind_by_name($insertRestaurantStmt, ":ownerId", $ownerId);
            oci_bind_by_name($insertRestaurantStmt, ":rating", $rating);

            if (oci_execute($insertRestaurantStmt, OCI_DEFAULT)) {
                oci_commit($db_conn);
                echo "<p>Restaurant '$name' added successfully!</p>";
            } else {
                oci_rollback($db_conn);
                $e = oci_error($insertRestaurantStmt);
                echo "<p>Error: Unable to add restaurant. " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    function handleUpdateRestaurantRating()
    {
        if (connectToDB()) {
            global $db_conn;

            $restaurantName = trim($_POST['restaurantToUpdate']);
            $newRating = isset($_POST['newRating']) && $_POST['newRating'] !== '' ? floatval($_POST['newRating']) : null;
            $newOwnerName = trim($_POST['newOwnerName']);

            if (!is_null($newRating)) {
                $updateRatingQuery = "UPDATE Restaurant SET rating = :newRating WHERE name = :restaurantName";
                $updateRatingStmt = oci_parse($db_conn, $updateRatingQuery);
                oci_bind_by_name($updateRatingStmt, ":newRating", $newRating);
                oci_bind_by_name($updateRatingStmt, ":restaurantName", $restaurantName);

                if (!oci_execute($updateRatingStmt, OCI_DEFAULT)) {
                    oci_rollback($db_conn);
                    $e = oci_error($updateRatingStmt);
                    echo "<p>Error: Unable to update restaurant rating. " . htmlentities($e['message']) . "</p>";
                    disconnectFromDB();
                    return;
                }
            }

            if (!empty($newOwnerName)) {
                $getOwnerIdQuery = "SELECT ID FROM Owner WHERE name = :ownerName";
                $getOwnerIdStmt = oci_parse($db_conn, $getOwnerIdQuery);
                oci_bind_by_name($getOwnerIdStmt, ":ownerName", $newOwnerName);
                oci_execute($getOwnerIdStmt);
                $ownerRow = oci_fetch_assoc($getOwnerIdStmt);

                if ($ownerRow) {
                    $ownerId = $ownerRow['ID'];
                } else {
                    $newOwnerIdQuery = "SELECT NVL(MAX(ID), 0) + 1 AS new_id FROM Owner";
                    $newOwnerIdStmt = oci_parse($db_conn, $newOwnerIdQuery);
                    oci_execute($newOwnerIdStmt);
                    $newIdRow = oci_fetch_assoc($newOwnerIdStmt);
                    $ownerId = $newIdRow['NEW_ID'];

                    $insertOwnerQuery = "INSERT INTO Owner (ID, name) VALUES (:id, :name)";
                    $insertOwnerStmt = oci_parse($db_conn, $insertOwnerQuery);
                    oci_bind_by_name($insertOwnerStmt, ":id", $ownerId);
                    oci_bind_by_name($insertOwnerStmt, ":name", $newOwnerName);

                    if (!oci_execute($insertOwnerStmt, OCI_DEFAULT)) {
                        oci_rollback($db_conn);
                        $e = oci_error($insertOwnerStmt);
                        echo "<p>Error: Unable to create new owner. " . htmlentities($e['message']) . "</p>";
                        disconnectFromDB();
                        return;
                    }
                }

                $updateOwnerQuery = "UPDATE Restaurant SET owner_ID = :ownerId WHERE name = :restaurantName";
                $updateOwnerStmt = oci_parse($db_conn, $updateOwnerQuery);
                oci_bind_by_name($updateOwnerStmt, ":ownerId", $ownerId);
                oci_bind_by_name($updateOwnerStmt, ":restaurantName", $restaurantName);

                if (!oci_execute($updateOwnerStmt, OCI_DEFAULT)) {
                    oci_rollback($db_conn);
                    $e = oci_error($updateOwnerStmt);
                    echo "<p>Error: Unable to update restaurant owner. " . htmlentities($e['message']) . "</p>";
                    disconnectFromDB();
                    return;
                }
            }

            oci_commit($db_conn);
            echo "<p>Info for '$restaurantName' updated successfully!</p>";

            disconnectFromDB();
        }
    }

    // Function to handle deleting a restaurant
    function handleDeleteRestaurant()
    {
        if (connectToDB()) {
            global $db_conn;

            $restaurantName = $_POST['restaurantToDelete'];

            // Delete the restaurant
            $deleteRestaurantQuery = "DELETE FROM Restaurant WHERE name = :restaurantName";
            $deleteRestaurantStmt = oci_parse($db_conn, $deleteRestaurantQuery);
            oci_bind_by_name($deleteRestaurantStmt, ":restaurantName", $restaurantName);

            if (oci_execute($deleteRestaurantStmt, OCI_DEFAULT)) {
                oci_commit($db_conn);
                echo "<p>Restaurant '$restaurantName' deleted successfully!</p>";
            } else {
                oci_rollback($db_conn);
                $e = oci_error($deleteRestaurantStmt);
                echo "<p>Error: Unable to delete restaurant. " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    function handleSearchDishesRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Set NLS Numeric Characters (if needed)
            $nlsQuery = "ALTER SESSION SET NLS_NUMERIC_CHARACTERS = '. '";
            $nlsStmt = oci_parse($db_conn, $nlsQuery);
            oci_execute($nlsStmt);

            // Dynamically build query based on user input
            $conditions = [];
            $bindings = [];

            // Loop through the posted conditions
            $conditionCount = 0;
            foreach ($_POST as $key => $value) {
                if (preg_match('/^field(\d+)$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $_POST["field$index"];
                    $operator = $_POST["operator$index"];
                    $conditionValue = $_POST["value$index"];

                    // Validate field and operator
                    $validFields = ['price', 'name', 'restaurant_name'];
                    $numericOperators = ['=', '<>', '>', '<', '>=', '<='];
                    $stringOperators = ['='];

                    if (!in_array($field, $validFields)) {
                        echo "<p>Error: Invalid field selected.</p>";
                        disconnectFromDB();
                        return;
                    }

                    if ($field === 'price' && !in_array($operator, $numericOperators)) {
                        echo "<p>Error: Invalid operator for numeric field.</p>";
                        disconnectFromDB();
                        return;
                    } elseif ($field !== 'price' && !in_array($operator, $stringOperators)) {
                        echo "<p>Error: Invalid operator for string field.</p>";
                        disconnectFromDB();
                        return;
                    }

                    // Add condition to query
                    $placeholder = ":value$index";
                    $conditions[] = "$field $operator $placeholder";
                    $bindings[$placeholder] = $conditionValue;
                }
            }

            // Combine conditions with logical operators
            $query = "SELECT RESTAURANT_NAME, NAME, PRICE FROM Dishes";
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            // Prepare and execute the query
            $stmt = oci_parse($db_conn, $query);
            foreach ($bindings as $placeholder => $value) {
                oci_bind_by_name($stmt, $placeholder, $bindings[$placeholder]);
            }

            if (!oci_execute($stmt)) {
                $e = oci_error($stmt);
                echo "<p>Execute Error: " . htmlentities($e['message']) . "</p>";
                disconnectFromDB();
                return;
            }

            // Display the results
            echo "<h3>Search Results:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Dish Name</th><th>Price</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($stmt)) {
                $found = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PRICE']) . "</td>";
                echo "</tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='3'>No dishes match your criteria.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle projection query (View Employees)
    function handleProjectionRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get selected attributes
            $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : [];

            if (count($attributes) == 0) {
                echo "<p>Error: Please select at least one attribute.</p>";
                disconnectFromDB();
                return;
            }

            $attributesList = implode(", ", $attributes);

            // Build and execute the query
            $query = "SELECT $attributesList FROM Emp1";
            $projectionStmt = oci_parse($db_conn, $query);
            oci_execute($projectionStmt);

            // Display the results
            echo "<h3>Employee Details:</h3>";
            echo "<table border='1'>";
            echo "<tr>";
            foreach ($attributes as $attr) {
                echo "<th>$attr</th>";
            }
            echo "</tr>";

            while ($row = oci_fetch_assoc($projectionStmt)) {
                echo "<tr>";
                foreach ($attributes as $attr) {
                    echo "<td>" . $row[$attr] . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }


    function getAllRestaurantsWithOwners()
    {
        if (connectToDB()) {
            global $db_conn;

            // Query to fetch all restaurants along with owner names
            $query = "SELECT R.name AS restaurant_name, O.name AS owner_name, R.rating
                    FROM Restaurant R
                    LEFT JOIN Owner O ON R.owner_ID = O.ID";
            $stmt = oci_parse($db_conn, $query);
            oci_execute($stmt);

            // Display results in a table
            echo "<h3>All Restaurants with Owners:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Owner Name</th><th>Rating</th></tr>";
            while ($row = oci_fetch_assoc($stmt)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['OWNER_NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['RATING']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle viewing all restaurants
    function displayAllRestaurants()
    {
        if (connectToDB()) {
            global $db_conn;

            // Query to fetch all restaurants
            $query = "SELECT * FROM Restaurant";
            $stmt = oci_parse($db_conn, $query);
            oci_execute($stmt);

            // Display results in a table
            echo "<h3>All Restaurants:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Name</th><th>Owner ID</th><th>Rating</th></tr>";
            while ($row = oci_fetch_assoc($stmt)) {
                echo "<tr><td>" . htmlspecialchars($row['NAME']) . "</td><td>" . htmlspecialchars($row['OWNER_ID']) . "</td><td>" . htmlspecialchars($row['RATING']) . "</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle aggregation request (Average Dish Price by Restaurant)
    function handleAggregationRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            $query = "SELECT restaurant_name, AVG(price) AS average_price FROM Dishes GROUP BY restaurant_name";
            $aggregationStmt = oci_parse($db_conn, $query);
            oci_execute($aggregationStmt);

            // Display the results
            echo "<h3>Average Dish Price by Restaurant:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Average Price</th></tr>";
            while ($row = oci_fetch_assoc($aggregationStmt)) {
                echo "<tr><td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td><td>" . number_format($row['AVERAGE_PRICE'], 2) . "</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle having request (Restaurants with More Than 2 Dishes)
    function handleHavingRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            $query = "SELECT restaurant_name, COUNT(*) AS dish_count FROM Dishes GROUP BY restaurant_name HAVING COUNT(*) > 2";
            $havingStmt = oci_parse($db_conn, $query);
            oci_execute($havingStmt);

            // Display the results
            echo "<h3>Restaurants with More Than 2 Dishes:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Number of Dishes</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($havingStmt)) {
                $found = true;
                echo "<tr><td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td><td>" . $row['DISH_COUNT'] . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='2'>No restaurants have more than 2 dishes.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle nested aggregation request (Chefs with Above Average Skill Level)
    function handleNestedAggregationRequest()
    {
        if (connectToDB()) {
            global $db_conn;
        
            $query = "SELECT C.restaurant_name, AVG(H.skill_level) AS avg_skill_level
            FROM Cook C, Chef H
            WHERE C.chef_id = H.ID
            GROUP BY C.restaurant_name
            HAVING AVG(H.skill_level) >= ALL (
                SELECT AVG(H2.skill_level)
                FROM Cook C2, Chef H2
                WHERE C2.chef_id = H2.ID
                GROUP BY C2.restaurant_name
            )";
        
            $nestedAggStmt = oci_parse($db_conn, $query);
            oci_execute($nestedAggStmt);
        
            // Display the results
            echo "<h3>Highest Average Chef Skill Level by Restaurant:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Average Skill Level</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($nestedAggStmt)) {
                $found = true;
                echo "<tr><td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td><td>" . number_format($row['AVG_SKILL_LEVEL'], 2) . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='2'>No restaurants found.</td></tr>";
            }
            echo "</table>";
        
            disconnectFromDB();
        }
    }

    // Function to handle division request (Users Who Have Eaten All Dishes)
    function handleDivisionRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            $query = "SELECT U.ID, U.name FROM \"User\" U
                    WHERE NOT EXISTS (
                        SELECT D.name FROM Dishes D
                        MINUS
                        SELECT E.dish_name FROM Eat E WHERE E.user_id = U.ID
                    )";

            $divisionStmt = oci_parse($db_conn, $query);
            oci_execute($divisionStmt);

            // Display the results
            echo "<h3>Users Who Have Eaten All Dishes:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>User ID</th><th>User Name</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($divisionStmt)) {
                $found = true;
                echo "<tr><td>" . htmlspecialchars($row['ID']) . "</td><td>" . htmlspecialchars($row['NAME']) . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='2'>No users have eaten all dishes.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    function handleFindChefsByRestaurant()
    {
        if (connectToDB()) {
            global $db_conn;

            $restaurantName = trim($_POST['restaurantName']);

            // Query to find chefs associated with the selected restaurant
            $query = "SELECT DISTINCT H.ID, H.style, H.skill_level
                    FROM Cook C
                    JOIN Chef H ON C.chef_id = H.ID
                    WHERE C.restaurant_name = :restaurantName";

            $stmt = oci_parse($db_conn, $query);
            oci_bind_by_name($stmt, ":restaurantName", $restaurantName);

            if (oci_execute($stmt)) {
                echo "<h3>Chefs at " . htmlspecialchars($restaurantName) . ":</h3>";
                echo "<table border='1'>";
                echo "<tr><th>Chef ID</th><th>Style</th><th>Skill Level</th></tr>";
                $found = false;
                while ($row = oci_fetch_assoc($stmt)) {
                    $found = true;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['STYLE']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['SKILL_LEVEL']) . "</td>";
                    echo "</tr>";
                }
                if (!$found) {
                    echo "<tr><td colspan='3'>No chefs found for this restaurant.</td></tr>";
                }
                echo "</table>";
            } else {
                $e = oci_error($stmt);
                echo "<p>Error: " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    // Function to handle form submissions
    function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['addSubmit'])) {
                handleAddRestaurant();
            } elseif (isset($_POST['updateRestaurantSubmit'])) {
                handleUpdateRestaurantRating();
            } elseif (isset($_POST['deleteRestaurantSubmit'])) {
                handleDeleteRestaurant();
            } elseif (isset($_POST['searchDishesSubmit'])) {
                handleSearchDishesRequest();
            } elseif (isset($_POST['projectionSubmit'])) {
                handleProjectionRequest();
            } elseif (isset($_POST['aggregationSubmit'])) {
                handleAggregationRequest();
            } elseif (isset($_POST['havingSubmit'])) {
                handleHavingRequest();
            } elseif (isset($_POST['nestedAggregationSubmit'])) {
                handleNestedAggregationRequest();
            } elseif (isset($_POST['divisionSubmit'])) {
                handleDivisionRequest();
            } elseif (isset($_POST['viewAllRestaurants'])) {
                displayAllRestaurants();
            } elseif (isset($_POST['joinSubmit'])) {
                handleFindChefsByRestaurant();
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['searchRestaurantSubmit'])) {
                handleSearchRestaurant();
            }
        }
    }

    // End PHP parsing and send the rest of the HTML content
    ?>
</body>

</html>