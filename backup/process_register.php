<?php
session_start();
include 'includes/config.php';

try {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate password match
    if ($password !== $confirm_password) {
        throw new Exception("Passwords do not match");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email already registered");
    }

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hashed_password]);

    $_SESSION['success'] = "Registration successful! Please login.";
    header("Location: login.php");
    exit();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: register.php");
    exit();
}