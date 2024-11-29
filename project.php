<?php
// Start the session
session_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database access configuration
$config["dbuser"] = "ora_jhuang74";       // Replace with your CWL username
$config["dbpassword"] = "a66382623";     // Replace with 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;    // Database connection handle

$success = true;    // Keep track of errors for transaction handling
$show_debug_alert_messages = false; // Set to true to show debug messages

// Function to display debug messages
function debugAlertMessage($message)
{
    global $show_debug_alert_messages;

    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alert('" . $message . "');</script>";
    }
}

// Function to connect to the Oracle database
function connectToDB()
{
    global $db_conn, $config;

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

// Function to disconnect from the database
function disconnectFromDB()
{
    global $db_conn;
    debugAlertMessage("Disconnect from Database");
    oci_close($db_conn);
}

// Function to execute a plain SQL command without bound variables
function executePlainSQL($cmdstr)
{
    global $db_conn, $success;

    $statement = oci_parse($db_conn, $cmdstr);

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

// Function to execute a SQL command with bound variables
function executeBoundSQL($cmdstr, $list)
{
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
            // Bind each variable in the tuple
            oci_bind_by_name($statement, $bind, $val);
            unset($val);
        }

        $r = oci_execute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement);
            echo htmlentities($e['message']);
            echo "<br>";
            $success = false;
        }
    }

    return $statement;
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

// // Function to handle updating a restaurant's rating
// function handleUpdateRestaurantRating()
// {
//     if (connectToDB()) {
//         global $db_conn;

//         $restaurantName = $_POST['restaurantToUpdate'];
//         $newRating = floatval($_POST['newRating']);

//         // Update the rating
//         $updateRestaurantQuery = "UPDATE Restaurant SET rating = :newRating WHERE name = :restaurantName";
//         $updateRestaurantStmt = oci_parse($db_conn, $updateRestaurantQuery);
//         oci_bind_by_name($updateRestaurantStmt, ":newRating", $newRating);
//         oci_bind_by_name($updateRestaurantStmt, ":restaurantName", $restaurantName);

//         if (oci_execute($updateRestaurantStmt, OCI_DEFAULT)) {
//             oci_commit($db_conn);
//             echo "<p>Rating of '$restaurantName' updated successfully!</p>";
//         } else {
//             oci_rollback($db_conn);
//             $e = oci_error($updateRestaurantStmt);
//             echo "<p>Error: Unable to update restaurant rating. " . htmlentities($e['message']) . "</p>";
//         }

//         disconnectFromDB();
//     }
// }

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

// Function to handle searching for a restaurant
function handleSearchRestaurant()
{
    if (connectToDB()) {
        global $db_conn;

        $restaurantName = $_GET['restaurantName'];

        // Prepare and execute the query
        $query = "SELECT name, rating, owner_ID FROM Restaurant WHERE name = :restaurantName";
        $statement = oci_parse($db_conn, $query);
        oci_bind_by_name($statement, ":restaurantName", $restaurantName);

        if (oci_execute($statement)) {
            $row = oci_fetch_assoc($statement);
            if ($row) {
                // Display restaurant information
                echo "<h3>Restaurant Information:</h3>";
                echo "Name: " . htmlspecialchars($row['NAME']) . "<br>";
                echo "Rating: " . htmlspecialchars($row['RATING']) . "<br>";
                echo "Owner ID: " . htmlspecialchars($row['OWNER_ID']) . "<br>";
            } else {
                echo "<p>No information found for the restaurant: " . htmlspecialchars($restaurantName) . "</p>";
            }
        } else {
            $e = oci_error($statement);
            echo "<p>Error: " . htmlentities($e['message']) . "</p>";
        }

        disconnectFromDB();
    }
}

// Function to handle searching for dishes based on criteria
function handleSearchDishesRequest()
{
    if (connectToDB()) {
        global $db_conn;

        // Get the search criteria
        $searchCriteria = trim($_POST['searchCriteria']);

        // For security, only allow certain fields and operators
        // Define allowed fields and operators
        $allowedFields = ['price', 'name', 'restaurant_name'];
        $allowedOperators = ['=', '>', '<', '>=', '<=', '<>', 'LIKE', 'AND', 'OR'];

        // Simple validation (this can be improved)
        $isValid = true;
        $tokens = preg_split('/\s+/', strtoupper($searchCriteria));
        foreach ($tokens as $token) {
            if (!in_array($token, $allowedFields) && !in_array($token, $allowedOperators) && !preg_match('/^[\'%].*[\'%]$/', $token) && !is_numeric($token)) {
                $isValid = false;
                break;
            }
        }

        if (!$isValid) {
            echo "<p>Error: Invalid search criteria.</p>";
            disconnectFromDB();
            return;
        }

        // Build the query (this is still risky; in production, you should use parameterized queries)
        $query = "SELECT restaurant_name, name, price FROM Dishes WHERE " . $searchCriteria;
        $searchStmt = oci_parse($db_conn, $query);

        if (@oci_execute($searchStmt)) {
            // Display the results
            echo "<h3>Search Results:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Restaurant Name</th><th>Dish Name</th><th>Price</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($searchStmt)) {
                $found = true;
                echo "<tr><td>" . htmlspecialchars($row['RESTAURANT_NAME']) . "</td><td>" . htmlspecialchars($row['NAME']) . "</td><td>" . htmlspecialchars($row['PRICE']) . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='3'>No dishes match your criteria.</td></tr>";
            }
            echo "</table>";
        } else {
            // Display an error message within the HTML
            $e = oci_error($searchStmt);
            echo "<p>Error: Invalid search criteria. " . htmlentities($e['message']) . "</p>";
        }

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

        $query = "SELECT ID, style, skill_level FROM Chef WHERE skill_level > (SELECT AVG(skill_level) FROM Chef)";
        $nestedAggStmt = oci_parse($db_conn, $query);
        oci_execute($nestedAggStmt);

        // Display the results
        echo "<h3>Chefs with Above Average Skill Level:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Chef ID</th><th>Style</th><th>Skill Level</th></tr>";
        $found = false;
        while ($row = oci_fetch_assoc($nestedAggStmt)) {
            $found = true;
            echo "<tr><td>" . htmlspecialchars($row['ID']) . "</td><td>" . htmlspecialchars($row['STYLE']) . "</td><td>" . $row['SKILL_LEVEL'] . "</td></tr>";
        }
        if (!$found) {
            echo "<tr><td colspan='3'>No chefs have above average skill level.</td></tr>";
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
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['searchRestaurantSubmit'])) {
            handleSearchRestaurant();
        }
    }
}

// Handle the request
handleRequest();

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
        <p>Enter your search criteria (e.g., "price > 5 AND name LIKE '%Burger%'"):</p>
        <input type="text" name="searchCriteria" required style="width: 400px;"> <br /><br />
        <input type="submit" value="Search Dishes" name="searchDishesSubmit">
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
    <h2>Chefs with Above Average Skill Level</h2>
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

</body>
</html>
