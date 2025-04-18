<?php
session_start();
include 'includes/config.php';

try {
    // Validate form submission
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        throw new Exception("Please fill in all fields");
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Fetch user data
    $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user exists and password matches
    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception("Invalid email or password");
    }

    // Set session and redirect
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    
    // Clear any existing error messages
    unset($_SESSION['error']);
    
    header("Location: form.php");
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: login.php");
    exit();
}