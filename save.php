<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';

try {
    // Process photo upload first
    $photo_path = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadFile)) {
            $photo_path = $fileName;
        }
    } elseif (isset($_POST['existing_photo'])) {
        $photo_path = $_POST['existing_photo'];
    }

    // Validate form data
    if (empty($_POST['name']) || empty($_POST['profession'])) {
        throw new Exception("Name and profession are required fields");
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert basic information
    $stmt = $pdo->prepare("INSERT INTO resumes (user_id, name, profession, availability, location, experience_summary, other_info, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['name'],
        $_POST['profession'],
        $_POST['availability'],
        $_POST['location'],
        $_POST['experience_summary'],
        $_POST['other_info'],
        $photo_path
    ]);
    $resume_id = $pdo->lastInsertId();

    // Insert technical skills
    if (isset($_POST['technology'])) {
        $stmt = $pdo->prepare("INSERT INTO skills (resume_id, technology, level, years) VALUES (?, ?, ?, ?)");
        foreach ($_POST['technology'] as $key => $technology) {
            $stmt->execute([
                $resume_id,
                $technology,
                $_POST['level'][$key],
                $_POST['years'][$key]
            ]);
        }
    }

    // Insert professional experience
    if (isset($_POST['company'])) {
        $stmt = $pdo->prepare("INSERT INTO experiences (resume_id, company, position, start_date, end_date, main_activities, technologies) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($_POST['company'] as $key => $company) {
            $stmt->execute([
                $resume_id,
                $company,
                $_POST['position'][$key],
                $_POST['start_date'][$key] ?: null,
                $_POST['end_date'][$key] ?: null,
                $_POST['main_activities'][$key],
                $_POST['technologies'][$key]
            ]);
        }
    }

    // Insert education
    if (isset($_POST['institution'])) {
        $stmt = $pdo->prepare("INSERT INTO education (resume_id, institution, course, additional_info) VALUES (?, ?, ?, ?)");
        foreach ($_POST['institution'] as $key => $institution) {
            $stmt->execute([
                $resume_id,
                $institution,
                $_POST['course'][$key],
                $_POST['additional_info'][$key]
            ]);
        }
    }

    // Insert certifications
    if (isset($_POST['cert_title'])) {
        $stmt = $pdo->prepare("INSERT INTO certifications (resume_id, title, institution, year) VALUES (?, ?, ?, ?)");
        foreach ($_POST['cert_title'] as $key => $title) {
            $year = !empty($_POST['cert_year'][$key]) ? (int)$_POST['cert_year'][$key] : null;
            $stmt->execute([
                $resume_id,
                $title,
                $_POST['cert_institution'][$key],
                $year
            ]);
        }
    }

    // Insert languages
    if (isset($_POST['language'])) {
        $stmt = $pdo->prepare("INSERT INTO languages (resume_id, language, level) VALUES (?, ?, ?)");
        foreach ($_POST['language'] as $key => $language) {
            $stmt->execute([
                $resume_id,
                $language,
                $_POST['language_level'][$key]
            ]);
        }
    }

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Resume saved successfully!";
    header("Location: view_resume.php?id=" . $resume_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Error saving resume: " . $e->getMessage();
    header("Location: form.php");
    exit();
}