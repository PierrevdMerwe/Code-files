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
} else {
  // Prepare SQL query to select products
  $sql = "SELECT product_id, product_name, description, price, stock, image FROM Products LIMIT 6"; // Add image to your SQL query

  $result = $conn->query($sql);

  if ($result) {
    if ($result->num_rows > 0) {
      $products = array();
      while ($row = $result->fetch_assoc()) {
        // Escape any special characters in product data
        $row['product_id'] = htmlspecialchars($row['product_id']);
        $row['product_name'] = htmlspecialchars($row['product_name']);
        $row['description'] = htmlspecialchars($row['description']);
        $products[] = $row;
      }
      $response['products'] = $products; // Add the products to the response array
    } else {
      error_log("No products found");
      $response['error'] = "No products found";
    }
  } else {
    // Log the error message
    error_log("Error executing SQL query: " . $conn->error);
    // Send a more informative error message in the JSON response
    $response['error'] = "Error retrieving products";
  }

  // Prepare SQL query to select categories
  $sql = "SELECT category_name, image FROM Categories LIMIT 2, 2"; // Fetch the second and third items from the categories table and include the image field

  $result = $conn->query($sql);

  if ($result) {
    if ($result->num_rows > 0) {
      $categories = array();
      while ($row = $result->fetch_assoc()) {
        // Escape any special characters in category data
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
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response); // Output the response as a single JSON object
?>
