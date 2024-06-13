<?php
session_start();

if (isset($_SESSION['username'])) {
    unset($_SESSION['username']);  // Unset the username session variable
}

echo json_encode(array("signedOut" => true));  // Return a JSON response indicating that the user has been signed out
?>
