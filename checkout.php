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

$response = array();
$conn_users = null;
$conn_products = null;

try {
    // Create connection to Users database
    $conn_users = new mysqli($servername, $username, $password, $dbname_users);
    if ($conn_users->connect_error) {
        throw new Exception("Connection to Users database failed: " . $conn_users->connect_error);
    }

    if (isset($_SESSION['username'])) {
        $session_username = $_SESSION['username'];
        error_log("Session username: $session_username");

        // Fetch user details
        $sql = "SELECT user_id FROM Users WHERE username = ?";
        $stmt = $conn_users->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $conn_users->error);
        }
        $stmt->bind_param("s", $session_username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // Create connection to Products database
            $conn_products = new mysqli($servername, $username, $password, $dbname_products);
            if ($conn_products->connect_error) {
                throw new Exception("Connection to Products database failed: " . $conn_products->connect_error);
            }

            // Fetch cart items for the user
            $sql = "SELECT cart_id, product_id, quantity FROM Cart WHERE user_id = ?";
            $stmt = $conn_products->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $cart_items = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $cart_items[] = $row;
                }
            } else {
                throw new Exception("No items in cart");
            }

            // Start transaction
            $conn_users->begin_transaction();
            $conn_products->begin_transaction();

            // Fetch the latest order_id and increment it
            $sql = "SELECT MAX(order_id) AS max_order_id FROM Orders";
            $stmt = $conn_users->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $new_order_id = $row['max_order_id'] + 1;

            // Insert new order into Orders table with the new order_id
            $sql = "INSERT INTO Orders (order_id, user_id, date_ordered, status) VALUES (?, ?, NOW(), 'pending')";
            $stmt = $conn_users->prepare($sql);
            $stmt->bind_param("ii", $new_order_id, $user_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Insert each cart item into Order_Details table
                foreach ($cart_items as $item) {
                    $sql = "INSERT INTO Order_Details (order_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt = $conn_users->prepare($sql);
                    $stmt->bind_param("iii", $new_order_id, $item['product_id'], $item['quantity']);
                    $stmt->execute();
                    if ($stmt->affected_rows <= 0) {
                        throw new Exception("Failed to insert order details");
                    }
                }

                // Remove items from Cart
                $sql = "DELETE FROM Cart WHERE user_id = ?";
                $stmt = $conn_products->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                if ($stmt->affected_rows <= 0) {
                    throw new Exception("Failed to remove items from cart");
                }

                // Commit transaction
                $conn_users->commit();
                $conn_products->commit();

                $response['success'] = true;
            } else {
                throw new Exception("Failed to create order");
            }
        } else {
            throw new Exception("No user found with the username: " . $session_username);
        }
    } else {
        throw new Exception("No user is signed in");
    }
} catch (Exception $e) {
    // Rollback transaction if something failed
    if ($conn_users && $conn_users->ping()) {
        $conn_users->rollback();
    }
    if ($conn_products && $conn_products->ping()) {
        $conn_products->rollback();
    }
    $response['error'] = $e->getMessage();
}

// Close connections
if ($conn_users && $conn_users->ping()) {
    $conn_users->close();
}
if ($conn_products && $conn_products->ping()) {
    $conn_products->close();
}

echo json_encode($response);
?>
