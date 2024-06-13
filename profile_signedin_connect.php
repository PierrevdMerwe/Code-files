<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servername = "sql205.infinityfree.com";
$username = "if0_36538934";
$password = "7pY61jTV0zevczY";
$dbname = "if0_36538934_users";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
    exit(); // Exit script on connection error
} else {
    if (isset($_SESSION['username'])) {
        // Get the username from the session
        $username = $_SESSION['username'];

        // Prepare SQL query to select user with the username
        $sql = "SELECT user_id, username, first_name, last_name FROM Users WHERE username = ?";

        // Create a prepared statement
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param("s", $username);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Now fetch the user's order history
            $servername = "sql205.infinityfree.com";
            $username = "if0_36538934";
            $password = "7pY61jTV0zevczY";
            $dbname = "if0_36538934_users";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
                exit(); // Exit script on connection error
            } else {
                // Prepare SQL query to select orders for the user
                $sql = "SELECT Orders.order_id, Orders.date_ordered, Orders.status, Order_Details.product_id, Order_Details.quantity FROM Orders INNER JOIN Order_Details ON Orders.order_id = Order_Details.order_id WHERE Orders.user_id = ?";

                // Create a prepared statement
                $stmt = $conn->prepare($sql);

                // Bind parameters
                $stmt->bind_param("i", $user['user_id']);

                // Execute the statement
                $stmt->execute();

                // Get the result
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $orders = array();
                    while ($row = $result->fetch_assoc()) {
                        $orders[] = $row;
                    }
                    $user['orders'] = $orders;  // Add the orders to the user's details
                }

                // Close the statement
                $stmt->close();
            }

            // Now fetch the product names
            $servername = "sql205.infinityfree.com";
            $username = "if0_36538934";
            $password = "7pY61jTV0zevczY";
            $dbname = "if0_36538934_products";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
                exit(); // Exit script on connection error
            } else {
                // Prepare SQL query to select product names
                $sql = "SELECT product_id, product_name FROM Products";

                // Create a prepared statement
                $stmt = $conn->prepare($sql);

                // Execute the statement
                $stmt->execute();

                // Get the result
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $products = array();
                    while ($row = $result->fetch_assoc()) {
                        $products[$row['product_id']] = $row['product_name'];
                    }
                    $user['products'] = $products;  // Add the product names to the user's details
                }

                // Close the statement
                $stmt->close();
            }

            echo json_encode($user);  // Return the user's details
        } else {
            echo json_encode(array("error" => "No user found with the username: " . $username));
        }
    } else {
        echo json_encode(array("error" => "No user is signed in"));
    }
}

// Close the connection
$conn->close();
?>
