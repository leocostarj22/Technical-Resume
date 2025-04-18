<?php
session_start();

// If user is already logged in, redirect to form
if (isset($_SESSION['user_id'])) {
    header("Location: form.php");
    exit();
}

// If not logged in, redirect to login page
header("Location: login.php");
exit();
require_once 'includes/config.php';
require_once 'includes/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Resume Builder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <img src="assets/images/banner2.jpeg" alt="Banner" class="banner-image">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo-image">
            </div>
        </div>
        
        <header>
            <h1>Technical Resume Builder</h1>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success">Resume saved successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert error">An error occurred. Please try again.</div>
            <?php endif; ?>
        </header>

        <main>
            <div class="actions">
                <a href="form.php" class="button">Create New Resume</a>
            </div>

            <div class="resume-list">
                <h2>Recent Resumes</h2>
                <?php
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $stmt = $pdo->query("SELECT id, name, profession, created_at FROM candidates ORDER BY created_at DESC LIMIT 10");
                    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($resumes): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Profession</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resumes as $resume): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($resume['name']) ?></td>
                                        <td><?= htmlspecialchars($resume['profession']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($resume['created_at'])) ?></td>
                                        <td>
                                            <a href="generate_resume.php?id=<?= $resume['id'] ?>&format=pdf" class="button small">PDF</a>
                                            <a href="generate_resume.php?id=<?= $resume['id'] ?>&format=word" class="button small">Word</a>
                                            <a href="form.php?id=<?= $resume['id'] ?>" class="button small">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No resumes found. Create your first resume!</p>
                    <?php endif;
                } catch (PDOException $e) {
                    echo '<p class="error">Unable to fetch resumes. Please try again later.</p>';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>