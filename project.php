<?php
// Start the session
session_start();

// Check if the user is logged in by verifying session variables
if (!isset($_SESSION['username'])) {
    // User not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

// Retrieve user information from session variables
$username = $_SESSION['username'];
$age = $_SESSION['age'];
$is_new_user = isset($_SESSION['is_new_user']) ? $_SESSION['is_new_user'] : false;

// Prepare the welcome message based on whether the user is new or returning
if ($is_new_user) {
    $welcomeMessage = "Welcome, new user $username (Age: $age)!";
} else {
    $welcomeMessage = "Welcome back, $username (Age: $age)!";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Discover Your Next Favorite Restaurant</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>


<body>
    <h1>Discover Your Next Favorite Restaurant</h1>
    <p>Rate, review, and explore the best dining experiences in your area.</p>

    <!-- User Information Section -->
    <h2>User Information</h2>
    <p><?php echo $welcomeMessage; ?></p> <!-- Display the welcome message -->

    <hr />

    <!-- Search for Restaurants Section -->
    <form method="GET" action="project.php">
        <input type="hidden" id="searchRestaurantRequest" name="searchRestaurantRequest">
        <p>Enter the name of the restaurant to search for:</p>
        <input type="text" name="restaurantName" placeholder="Enter restaurant name" required>
        <br /><br />
        <input type="submit" value="Search" name="searchRestaurantSubmit">
    </form>


    <hr />

    <!-- Add a Restaurant Section -->
    <h2>Add a Restaurant</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="addRestaurantRequest" name="addRestaurantRequest">
        Restaurant Name: <input type="text" name="name" required> <br /><br />
        Owner Name: <input type="text" name="ownerName" required> <br /><br />
        Rating (0-5): <input type="number" name="rating" min="0" max="5" step="0.1"> <br /><br />
        <input type="submit" value="Add Restaurant" name="addSubmit"></p>
    </form>

    <hr />

    <!-- Update Restaurant Rating Section -->
    <h2>Update Restaurant Rating</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="updateRestaurantRequest" name="updateRestaurantRequest">
        <p>Enter the name of the restaurant to update:</p>
        <input type="text" name="restaurantToUpdate" placeholder="Enter restaurant name" required>
        <br /><br />
        New Rating (0-5): <input type="number" name="newRating" min="0" max="5" step="0.1" required> <br /><br />
        <input type="submit" value="Update Rating" name="updateRestaurantSubmit">
    </form>


    <hr />

    <!-- Delete a Restaurant Section -->
    <h2>Delete a Restaurant</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="deleteRestaurantRequest" name="deleteRestaurantRequest">
        <p>Enter the name of the restaurant to delete:</p>
        <input type="text" name="restaurantToDelete" placeholder="Enter restaurant name">
        <br /><br />
        <input type="submit" value="Delete Restaurant" name="deleteRestaurantSubmit">
    </form>


    <hr />

    <!-- Search Dishes Section -->
    <h2>Search Dishes</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="searchDishesRequest" name="searchDishesRequest">
        <p>Enter your search criteria (e.g., "price > 5 AND name LIKE '%Burger%'"):</p>
        <input type="text" name="searchCriteria" required style="width: 400px;"> <br /><br />
        <input type="submit" value="Search Dishes" name="searchDishesSubmit">
    </form>

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
    <form method="POST" action="project.php">
        <input type="hidden" id="joinRequest" name="joinRequest">
        Enter Restaurant Name: <input type="text" name="restaurantName" required> <br /><br />
        <input type="submit" value="Find Chefs" name="joinSubmit">
    </form>

    <hr />

    <!-- Aggregation with GROUP BY -->
    <h2>Average Dish Price by Restaurant</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="aggregationRequest" name="aggregationRequest">
        <input type="submit" value="Show Average Prices" name="aggregationSubmit">
    </form>

    <hr />

    <!-- Aggregation with HAVING -->
    <h2>Restaurants with More Than 2 Dishes</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="havingRequest" name="havingRequest">
        <input type="submit" value="Show Restaurants" name="havingSubmit">
    </form>

    <hr />

    <!-- Nested Aggregation with GROUP BY -->
    <h2>Chefs with Above Average Skill Level</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="nestedAggregationRequest" name="nestedAggregationRequest">
        <input type="submit" value="Show Chefs" name="nestedAggregationSubmit">
    </form>

    <hr />

    <!-- Division Query -->
    <h2>Users Who Have Eaten All Dishes</h2>
    <form method="POST" action="project.php">
        <input type="hidden" id="divisionRequest" name="divisionRequest">
        <input type="submit" value="Show Users" name="divisionSubmit">
    </form>

    <hr />

    <!-- View All Restaurants Query -->
    <h2>View All Restaurants</h2>
    <form method="POST" action="project.php">
        <input type="hidden" name="viewAllRestaurants" value="1">
        <input type="submit" value="Show All Restaurants">
    </form>

    <hr />


    <?php
    // Include necessary PHP code to handle form submissions and database interactions

    // Database access configuration
    $config["dbuser"] = "ora_jhuang74"; // Replace with your CWL username
    $config["dbpassword"] = "a66382623"; // Replace with 'a' + your student number
    $config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
    $db_conn = NULL; // Database connection handle

    // Function to connect to the Oracle database
    function connectToDB()
    {
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
    function disconnectFromDB()
    {
        global $db_conn;
        oci_close($db_conn);
    }

    if (isset($_GET['searchRestaurantSubmit'])) {
        if (connectToDB()) {
            $restaurantName = trim($_GET['restaurantName']); // Trim input
    
            // Prepare and execute the query (case-insensitive, trimmed comparison)
            $query = "SELECT name, rating, owner_ID FROM Restaurant WHERE UPPER(TRIM(name)) = UPPER(TRIM(:restaurantName))";
            $statement = oci_parse($db_conn, $query);
    
            // Bind the parameter
            oci_bind_by_name($statement, ":restaurantName", $restaurantName);
    
            // Execute the query
            if (oci_execute($statement)) {
                echo "<h3>Restaurant Information:</h3>";
                echo "<table border='1'>"; // Start the table
                echo "<tr><th>Name</th><th>Rating</th><th>Owner ID</th></tr>"; // Table headers
    
                if ($row = oci_fetch_assoc($statement)) {
                    // Display the result in a row
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['RATING']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['OWNER_ID']) . "</td>";
                    echo "</tr>";
                } else {
                    // No result found
                    echo "<tr><td colspan='3'>Sorry, no information found for the restaurant: " . htmlspecialchars($restaurantName) . "</td></tr>";
                }
    
                echo "</table>"; // End the table
            } else {
                $error = oci_error($statement);
                echo "<p>Error: " . htmlspecialchars($error['message']) . "</p>";
            }
    
            disconnectFromDB();
        }
    }
    
    
    // Function to handle adding a restaurant (INSERT operation)
    function handleAddRestaurantRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get input values from the form
            $name = trim($_POST['name']);
            $ownerName = trim($_POST['ownerName']);
            $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;

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

                if (!oci_execute($insertOwnerStmt)) {
                    oci_rollback($db_conn);
                    echo "<p>Error: Unable to create new owner. Please try again.</p>";
                    disconnectFromDB();
                    return;
                }
            }

            // Insert the new restaurant into the database
            $insertRestaurantQuery = "INSERT INTO Restaurant (name, owner_ID, rating) VALUES (:name, :ownerId, :rating)";
            $insertStmt = oci_parse($db_conn, $insertRestaurantQuery);
            oci_bind_by_name($insertStmt, ":name", $name);
            oci_bind_by_name($insertStmt, ":ownerId", $ownerId);
            oci_bind_by_name($insertStmt, ":rating", $rating);

            if (oci_execute($insertStmt)) {
                oci_commit($db_conn);
                // Display a success message within the HTML
                echo "<p>Restaurant '$name' added successfully!</p>";
            } else {
                // Display an error message within the HTML
                $e = oci_error($insertStmt);
                echo "<p>Error: Unable to add restaurant. " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    // Function to handle updating restaurant rating (UPDATE operation)
    function handleUpdateRestaurantRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get input values from the form
            $restaurantName = $_POST['restaurantToUpdate'];
            $newRating = floatval($_POST['newRating']);

            // Update the restaurant rating
            $updateRestaurantQuery = "UPDATE Restaurant SET rating = :newRating WHERE name = :restaurantName";
            $updateRestaurantStmt = oci_parse($db_conn, $updateRestaurantQuery);
            oci_bind_by_name($updateRestaurantStmt, ":newRating", $newRating);
            oci_bind_by_name($updateRestaurantStmt, ":restaurantName", $restaurantName);

            if (oci_execute($updateRestaurantStmt)) {
                oci_commit($db_conn);
                // Display a success message within the HTML
                echo "<p>Rating of '$restaurantName' updated successfully!</p>";
            } else {
                // Display an error message within the HTML
                $e = oci_error($updateRestaurantStmt);
                echo "<p>Error: Unable to update restaurant rating. " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    // Function to handle deleting a restaurant (DELETE operation)
    function handleDeleteRestaurantRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get the restaurant name to delete
            $restaurantName = $_POST['restaurantToDelete'];

            // Delete the restaurant
            $deleteRestaurantQuery = "DELETE FROM Restaurant WHERE name = :restaurantName";
            $deleteRestaurantStmt = oci_parse($db_conn, $deleteRestaurantQuery);
            oci_bind_by_name($deleteRestaurantStmt, ":restaurantName", $restaurantName);

            if (oci_execute($deleteRestaurantStmt)) {
                oci_commit($db_conn);
                // Display a success message within the HTML
                echo "<p>Restaurant '$restaurantName' deleted successfully!</p>";
            } else {
                // Display an error message within the HTML
                $e = oci_error($deleteRestaurantStmt);
                echo "<p>Error: Unable to delete restaurant. " . htmlentities($e['message']) . "</p>";
            }

            disconnectFromDB();
        }
    }

    // Function to handle selection query (Search Dishes)
    function handleSearchDishesRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            // Get the search criteria
            $searchCriteria = trim($_POST['searchCriteria']);

            // Basic validation to prevent SQL injection
            // Note: For a production system, use prepared statements and parameter binding
            $allowedOperators = ['=', '>', '<', '>=', '<=', '<>', '!=', 'AND', 'OR', 'LIKE'];
            $tokens = preg_split('/\s+/', strtoupper($searchCriteria));

            foreach ($tokens as $token) {
                if (preg_match('/^[A-Z_][A-Z0-9_]*$/', $token) && !in_array($token, $allowedOperators)) {
                    // This is an attribute name, check if it exists in Dishes table
                    $columns = ['RESTAURANT_NAME', 'NAME', 'PRICE', 'PHOTO'];
                    if (!in_array($token, $columns)) {
                        echo "<p>Error: Invalid attribute '$token' in search criteria.</p>";
                        disconnectFromDB();
                        return;
                    }
                }
            }

            // Build the query
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
                    echo "<tr><td>" . $row['RESTAURANT_NAME'] . "</td><td>" . $row['NAME'] . "</td><td>" . $row['PRICE'] . "</td></tr>";
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

    // Function to handle join query (Find Chefs by Restaurant)
    function handleJoinRequest()
    {
        if (connectToDB()) {
            global $db_conn;

            $restaurantName = trim($_POST['restaurantName']);

            $query = "SELECT DISTINCT Chef.ID, Chef.style, Chef.skill_level
                      FROM Chef
                      JOIN Cook ON Chef.ID = Cook.chef_id
                      JOIN Dishes ON Cook.dish_name = Dishes.name AND Cook.restaurant_name = Dishes.restaurant_name
                      WHERE Dishes.restaurant_name = :restaurantName";

            $joinStmt = oci_parse($db_conn, $query);
            oci_bind_by_name($joinStmt, ":restaurantName", $restaurantName);
            oci_execute($joinStmt);

            // Display the results
            echo "<h3>Chefs at '$restaurantName':</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Chef ID</th><th>Style</th><th>Skill Level</th></tr>";
            $found = false;
            while ($row = oci_fetch_assoc($joinStmt)) {
                $found = true;
                echo "<tr><td>" . $row['ID'] . "</td><td>" . $row['STYLE'] . "</td><td>" . $row['SKILL_LEVEL'] . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='3'>No chefs found for this restaurant.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle Aggregation with GROUP BY
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
                echo "<tr><td>" . $row['RESTAURANT_NAME'] . "</td><td>" . number_format($row['AVERAGE_PRICE'], 2) . "</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle Aggregation with HAVING
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
                echo "<tr><td>" . $row['RESTAURANT_NAME'] . "</td><td>" . $row['DISH_COUNT'] . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='2'>No restaurants have more than 2 dishes.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle Nested Aggregation with GROUP BY
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
                echo "<tr><td>" . $row['ID'] . "</td><td>" . $row['STYLE'] . "</td><td>" . $row['SKILL_LEVEL'] . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='3'>No chefs have above average skill level.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to handle Division query
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
                echo "<tr><td>" . $row['ID'] . "</td><td>" . $row['NAME'] . "</td></tr>";
            }
            if (!$found) {
                echo "<tr><td colspan='2'>No users have eaten all dishes.</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }

    // Function to display all restaurants
    function displayAllRestaurants() {
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
                echo "</td><td>" . $row['NAME'] . "</td><td>" . $row['OWNER_ID'] . "</td><td>" . $row['RATING'] . "</td></tr>";
            }
            echo "</table>";

            disconnectFromDB();
        }
    }


    // Handle POST requests for form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['addSubmit'])) {
            handleAddRestaurantRequest();
        } elseif (isset($_POST['updateRestaurantSubmit'])) {
            handleUpdateRestaurantRequest();
        } elseif (isset($_POST['deleteRestaurantSubmit'])) {
            handleDeleteRestaurantRequest();
        } elseif (isset($_POST['searchDishesSubmit'])) {
            handleSearchDishesRequest();
        } elseif (isset($_POST['projectionSubmit'])) {
            handleProjectionRequest();
        } elseif (isset($_POST['joinSubmit'])) {
            handleJoinRequest();
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
    }
    ?>
</body>
</html>
