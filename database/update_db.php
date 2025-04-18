<?php
include '../includes/config.php';

try {
    $sql = "ALTER TABLE resumes ADD COLUMN other_info TEXT NULL AFTER location";
    $pdo->exec($sql);
    echo "Database updated successfully";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}