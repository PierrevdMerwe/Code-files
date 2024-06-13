<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: profile.html");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "sql205.infinityfree.com";
$username = "if0_36538934";
$password = "7pY61jTV0zevczY";
$dbname = "if0_36538934_products";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$response = array(); // Initialize an empty array to hold the response

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $response['error'] = "Connection failed: " . $conn->connect_error;
    echo json_encode($response); // Output the response as a single JSON object
    exit();
} else {
    // Prepare SQL query to select categories
    $sql = "SELECT category_id, category_name FROM Categories"; 
    error_log($sql); // Log the SQL query

    $result = $conn->query($sql);

    $categories = array(); // Initialize an empty array to hold the categories

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Escape any special characters in category name
                $row['category_name'] = htmlspecialchars($row['category_name']);
                $categories[] = $row;
            }
            $response['categories'] = $categories; // Add the categories to the response array
        } else {
            error_log("No categories found");
            $response['error'] = "No categories found";
        }
    } else {
        // Log the error message
        error_log("Error executing SQL query: " . $conn->error);
        // Send a more informative error message in the JSON response
        $response['error'] = "Error retrieving categories";
    }

    // Prepare SQL query to select products
    $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : 1;
    if ($categoryId == 1) {
        $sql = "SELECT product_id, product_name, description, price, stock, category_id, image FROM Products";
    } else {
        $sql = "SELECT product_id, product_name, description, price, stock, category_id, image FROM Products WHERE category_id = " . $categoryId;
    }
    error_log($sql); // Log the SQL query

    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $products = array();
            while ($row = $result->fetch_assoc()) {
                // Escape any special characters in product data
                $row['product_name'] = htmlspecialchars($row['product_name']);
                $row['description'] = htmlspecialchars($row['description']);
                $products[] = $row;
            }
            $response['products'] = $products; // Add the products to the response array
        } else {
            error_log("No products found");
            $response['error'] = "No products found";
            echo json_encode($response); // Output the response as a single JSON object
            exit();
        }
    } else {
        // Log the error message
        error_log("Error executing SQL query: " . $conn->error);
        // Send a more informative error message in the JSON response
        $response['error'] = "Error retrieving products";
        echo json_encode($response); // Output the response as a single JSON object
        exit();
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response); // Output the response as a single JSON object
?>
