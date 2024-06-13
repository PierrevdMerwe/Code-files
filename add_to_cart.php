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
$dbname_users = "if0_36538934_users";
$dbname_products = "if0_36538934_products";

// Create connection
$conn_users = new mysqli($servername, $username, $password, $dbname_users);
$conn_products = new mysqli($servername, $username, $password, $dbname_products);

$response = array();

// Check connection
if ($conn_users->connect_error || $conn_products->connect_error) {
  error_log("Connection failed: " . $conn_users->connect_error . " " . $conn_products->connect_error);
  $response['error'] = "Connection failed: " . $conn_users->connect_error . " " . $conn_products->connect_error;
} else {
  if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $username = $_SESSION['username'];

    // Fetch user ID from Users table
    $sql = "SELECT user_id FROM Users WHERE username = ?";
    $stmt = $conn_users->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $user_id = $user['user_id'];

      // Insert the product into the Cart table
      $sql = "INSERT INTO Cart (product_id, user_id, quantity) VALUES (?, ?, 1)";
      $stmt = $conn_products->prepare($sql);
      $stmt->bind_param("ii", $product_id, $user_id);
      $stmt->execute();

      if ($stmt->affected_rows > 0) {
        $response['success'] = true;
      } else {
        error_log("Failed to add product to cart: " . $conn_products->error);
        $response['error'] = "Failed to add product to cart";
      }
    } else {
      error_log("No user found with the username: " . $username);
      $response['error'] = "No user found with the username: " . $username;
    }
  } else {
    error_log("Product ID not set");
    $response['error'] = "Product ID not set";
  }
}

$conn_users->close();
$conn_products->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
