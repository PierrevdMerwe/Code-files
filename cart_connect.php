<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servername = "sql205.infinityfree.com";
$db_username = "if0_36538934";
$username = "";
$password = "7pY61jTV0zevczY";
$dbname_users = "if0_36538934_users";
$dbname_products = "if0_36538934_products";

$response = array();

try {
    // Create connection to Users database
    $conn_users = new mysqli($servername, $db_username, $password, $dbname_users);

    if ($conn_users->connect_error) {
        throw new Exception("Connection to Users database failed: " . $conn_users->connect_error);
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Fetch user details
        $sql = "SELECT user_id FROM Users WHERE username = ?";
        $stmt = $conn_users->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // Create connection to Products database
            $conn_products = new mysqli($servername, $db_username, $password, $dbname_products);
            if ($conn_products->connect_error) {
                throw new Exception("Connection to Products database failed: " . $conn_products->connect_error);
            }

            // Handle POST requests for removing or updating items
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['product_id'])) {
                    $product_id = intval($_POST['product_id']);

                    if (isset($_POST['remove']) && $_POST['remove'] == 'true') {
                        // Remove item from cart
                        $sql = "DELETE FROM Cart WHERE user_id = ? AND product_id = ?";
                        $stmt = $conn_products->prepare($sql);
                        $stmt->bind_param("ii", $user_id, $product_id);
                        $stmt->execute();

                        if ($stmt->affected_rows > 0) {
                            $response['success'] = true;
                        } else {
                            throw new Exception("Failed to remove item from cart");
                        }
                    } elseif (isset($_POST['quantity'])) {
                        $quantity = intval($_POST['quantity']);
                        // Update item quantity in cart
                        $sql = "UPDATE Cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                        $stmt = $conn_products->prepare($sql);
                        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                        $stmt->execute();

                        if ($stmt->affected_rows > 0) {
                            $response['success'] = true;
                        } else {
                            throw new Exception("Failed to update cart item quantity");
                        }
                    }
                } else {
                    throw new Exception("Product ID not set");
                }
            } else {
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
                }

                // Fetch product names, descriptions, prices, and images
                $sql = "SELECT product_id, product_name, description, price, image FROM Products";
                $stmt = $conn_products->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();

                $products = array();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $products[$row['product_id']] = array(
                            'product_name' => $row['product_name'],
                            'description' => $row['description'],
                            'price' => $row['price'],
                            'image' => $row['image']
                        );
                    }
                }

                // Combine cart items with product details
                foreach ($cart_items as &$item) {
                    $item['product_name'] = $products[$item['product_id']]['product_name'];
                    $item['description'] = $products[$item['product_id']]['description'];
                    $item['price'] = $products[$item['product_id']]['price'];
                    $item['image'] = $products[$item['product_id']]['image'];
                }

                $response = $cart_items;
            }
        } else {
            throw new Exception("No user found with the username: " . $username);
        }
    } else {
        throw new Exception("No user is signed in");
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Close connections
if (isset($conn_users) && $conn_users->ping()) {
    $conn_users->close();
}
if (isset($conn_products) && $conn_products->ping()) {
    $conn_products->close();
}

echo json_encode($response);
?>
