<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
require_once('vendor/autoload.php');

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;

try {
    // Fetch resume data
    $stmt = $pdo->prepare("
        SELECT r.id, r.name, r.profession, r.availability, r.location, r.photo_path, r.experience_summary, r.other_info,
               s.technology, s.level, s.years,
               e.company, e.position, e.start_date, e.end_date, e.main_activities, e.technologies,
               ed.institution, ed.course, ed.additional_info,
               c.title, c.institution as cert_institution, c.year,
               l.language, l.level as language_level
        FROM resumes r
        LEFT JOIN skills s ON r.id = s.resume_id
        LEFT JOIN experiences e ON r.id = e.resume_id
        LEFT JOIN education ed ON r.id = ed.resume_id
        LEFT JOIN certifications c ON r.id = c.resume_id
        LEFT JOIN languages l ON r.id = l.resume_id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        throw new Exception("Resume not found");
    }

    // Create new Word document
    $phpWord = new PhpWord();
    
    // Add styles
    $phpWord->addTitleStyle(1, ['size' => 20, 'bold' => true]);
    $phpWord->addTitleStyle(2, ['size' => 16, 'bold' => true, 'color' => '375280']);
    
    // Add a section
    $section = $phpWord->addSection();

    // Add logo
    $section->addImage('assets/images/logo.jpg', [
        'width' => 40,
        'height' => 40,
        'alignment' => 'center'
    ]);

    // Basic Information
    $section->addTitle($data[0]['name'], 1);
    $section->addText($data[0]['profession'], ['bold' => true, 'size' => 14]);
    $section->addText('Location: ' . $data[0]['location']);
    $section->addText('Availability: ' . $data[0]['availability']);

    // Experience Summary
    if (!empty($data[0]['experience_summary'])) {
        $section->addTitle('Experience Summary', 2);
        $section->addText($data[0]['experience_summary']);
    }

    // Technical Skills
    $section->addTitle('Technical Skills', 2);
    $table = $section->addTable(['borderSize' => 1]);
    $table->addRow();
    $table->addCell(3000)->addText('Technology', ['bold' => true]);
    $table->addCell(2000)->addText('Level', ['bold' => true]);
    $table->addCell(2000)->addText('Years', ['bold' => true]);

    $processed = ['technology' => []];
    foreach ($data as $row) {
        if ($row['technology'] && !in_array($row['technology'], $processed['technology'])) {
            $table->addRow();
            $table->addCell(3000)->addText($row['technology']);
            $table->addCell(2000)->addText($row['level']);
            $table->addCell(2000)->addText($row['years']);
            $processed['technology'][] = $row['technology'];
        }
    }

    // Professional Experience
    $section->addTitle('Professional Experience', 2);
    $processed = ['company' => []];
    foreach ($data as $row) {
        if ($row['company'] && !in_array($row['company'] . $row['position'], $processed['company'])) {
            $section->addText($row['position'] . ' at ' . $row['company'], ['bold' => true]);
            $section->addText($row['start_date'] . ' - ' . ($row['end_date'] ?: 'Present'));
            $section->addText($row['main_activities']);
            $section->addText('Technologies: ' . $row['technologies']);
            $processed['company'][] = $row['company'] . $row['position'];
        }
    }

    // Education
    $section->addTitle('Education', 2);
    $processed = ['institution' => []];
    foreach ($data as $row) {
        if ($row['institution'] && !in_array($row['institution'] . $row['course'], $processed['institution'])) {
            $section->addText($row['course'], ['bold' => true]);
            $section->addText($row['institution']);
            if (!empty($row['additional_info'])) {
                $section->addText($row['additional_info']);
            }
            $processed['institution'][] = $row['institution'] . $row['course'];
        }
    }

    // Certifications
    if (!empty($data[0]['title'])) {
        $section->addTitle('Certifications', 2);
        $processed = ['title' => []];
        foreach ($data as $row) {
            if ($row['title'] && !in_array($row['title'], $processed['title'])) {
                $section->addText($row['title'] . ' - ' . $row['cert_institution'] . ' (' . $row['year'] . ')');
                $processed['title'][] = $row['title'];
            }
        }
    }

    // Languages
    if (!empty($data[0]['language'])) {
        $section->addTitle('Languages', 2);
        $processed = ['language' => []];
        foreach ($data as $row) {
            if ($row['language'] && !in_array($row['language'], $processed['language'])) {
                $section->addText($row['language'] . ' - ' . $row['language_level']);
                $processed['language'][] = $row['language'];
            }
        }
    }

    // Other Information
    if (!empty($data[0]['other_info'])) {
        $section->addTitle('Other Information', 2);
        $section->addText($data[0]['other_info']);
    }

    // Add footer banner
    $section->addImage('assets/images/banner.jpg', [
        'width' => 400,
        'height' => 50,
        'alignment' => 'center'
    ]);

    // Save file
    $filename = 'Technical_Resume_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data[0]['name']) . '.docx';
    
    // Limpar qualquer saída anterior
    if (ob_get_length()) ob_end_clean();
    
    // Set headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Salvar diretamente na saída
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: form.php?id=" . $_GET['id']);
    exit();
}