<?php
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
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // This is a sign in request
        // Get the username and password from the form
        $enteredUsername = $_POST['username'];
        $enteredPassword = $_POST['password'];

        // Prepare SQL query to select user with the entered username and password
        $sql = "SELECT * FROM Users WHERE username = ? AND password = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("ss", $enteredUsername, $enteredPassword);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['username'] = $enteredUsername;
            echo json_encode(array("success" => "User found"));
        } else {
            echo json_encode(array("error" => "No user found with the entered username and password"));
        }

        // Close the statement
        $stmt->close();
    } elseif (isset($_POST['newUsername']) && isset($_POST['newPassword']) && isset($_POST['newEmail']) && isset($_POST['newFirstName']) && isset($_POST['newLastName']) && isset($_POST['newAddress']) && isset($_POST['newPhoneNumber'])) {
        // This is a sign up request
        // Get the username, password, email, first name, last name, address, and phone number from the form
        $enteredUsername = $_POST['newUsername'];
        $enteredPassword = $_POST['newPassword'];
        $enteredEmail = $_POST['newEmail'];
        $enteredFirstName = $_POST['newFirstName'];
        $enteredLastName = $_POST['newLastName'];
        $enteredAddress = $_POST['newAddress'];
        $enteredPhoneNumber = $_POST['newPhoneNumber'];
    
        // Prepare SQL query to insert new user
        $sql = "INSERT INTO Users (username, password, email, first_name, last_name, address, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
        // Create a prepared statement
        $stmt = $conn->prepare($sql);
    
        // Bind parameters
        $stmt->bind_param("sssssss", $enteredUsername, $enteredPassword, $enteredEmail, $enteredFirstName, $enteredLastName, $enteredAddress, $enteredPhoneNumber);
    
        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['username'] = $enteredUsername;
            echo json_encode(array("success" => "User signed up successfully"));
        } else {
            echo json_encode(array("error" => "Error signing up: " . $stmt->error));
        }
    
        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(array("error" => "Invalid request"));
    }
}

// Close the connection
$conn->close();
?>
