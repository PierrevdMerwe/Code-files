<?php
session_start();

if (isset($_SESSION['username'])) {
    echo json_encode(array("signedIn" => true, "username" => $_SESSION['username']));
} else {
    echo json_encode(array("signedIn" => false));
}
?>
