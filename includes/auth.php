<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please login to continue";
        header("Location: login.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}