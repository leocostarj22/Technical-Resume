<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
require_once('vendor/autoload.php');

try {
    // Fetch resume data (using the same query from view_resume.php)
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

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($data[0]['name']);
    $pdf->SetTitle('Technical Resume - ' . $data[0]['name']);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins - ajustando margem inferior para acomodar o banner
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 40); // Ajusta quebra de página automática

    // Add a page
    $pdf->AddPage();

    // Add logo at the top - centered with less padding
    $pageWidth = $pdf->GetPageWidth();
    $logoWidth = 40;
    $logoX = ($pageWidth - $logoWidth) / 2;
    $pdf->Image('assets/images/logo.jpg', $logoX, 10, $logoWidth);
    $pdf->Ln(25); // Reduzido de 45 para 30

    // Basic Information
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 8, $data[0]['name'], 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 8, $data[0]['profession'], 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Location: ' . $data[0]['location'], 0, 1, 'L');
    $pdf->Cell(0, 8, 'Availability: ' . $data[0]['availability'], 0, 1, 'L');

    // Experience Summary
    if (!empty($data[0]['experience_summary'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(55, 82, 128); // #375280
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, 'Experience Summary', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0); // Volta texto para preto
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 8, $data[0]['experience_summary'], 0, 'L');
    }

    // Technical Skills
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(55, 82, 128); // #375280
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Technical Skills', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);
    
    // Configure table columns - ajustado para largura total
    $pageWidth = $pdf->GetPageWidth();
    $margins = 30; // margem total (15 de cada lado)
    $availableWidth = $pageWidth - $margins;
    $w = array($availableWidth * 0.47, $availableWidth * 0.265, $availableWidth * 0.265);  // proporção 47%, 26.5%, 26.5%
    
    // Table header
    $pdf->SetFillColor(245, 246, 250);
    $pdf->Cell($w[0], 8, 'Technology', 1, 0, 'L', true);
    $pdf->Cell($w[1], 8, 'Level', 1, 0, 'C', true);
    $pdf->Cell($w[2], 8, 'Years', 1, 1, 'C', true);
    
    // Table content
    $processed = ['technology' => []];
    foreach ($data as $row) {
        if ($row['technology'] && !in_array($row['technology'], $processed['technology'])) {
            $pdf->Cell($w[0], 8, $row['technology'], 1, 0, 'L');
            $pdf->Cell($w[1], 8, $row['level'], 1, 0, 'C');
            $pdf->Cell($w[2], 8, $row['years'], 1, 1, 'C');
            $processed['technology'][] = $row['technology'];
        }
    }

    // Professional Experience
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(55, 82, 128); // #375280
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Professional Experience', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $processed = ['company' => []];
    foreach ($data as $row) {
        if ($row['company'] && !in_array($row['company'] . $row['position'], $processed['company'])) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $row['position'] . ' at ' . $row['company'], 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, $row['start_date'] . ' - ' . ($row['end_date'] ?: 'Present'), 0, 1, 'L');
            $pdf->MultiCell(0, 10, $row['main_activities'], 0, 'L');
            $pdf->Cell(0, 10, 'Technologies: ' . $row['technologies'], 0, 1, 'L');
            $pdf->Ln(5);
            $processed['company'][] = $row['company'] . $row['position'];
        }
    }

    // Education
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(55, 82, 128); // #375280
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Education', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $processed = ['institution' => []];
    foreach ($data as $row) {
        if ($row['institution'] && !in_array($row['institution'] . $row['course'], $processed['institution'])) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, $row['course'], 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 8, $row['institution'], 0, 1, 'L');
            if (!empty($row['additional_info'])) {
                $pdf->MultiCell(0, 8, $row['additional_info'], 0, 'L');
            }
            $pdf->Ln(3); // Reduzido de 5 para 3
            $processed['institution'][] = $row['institution'] . $row['course'];
        }
    }

    // Certifications
    if (!empty($data[0]['title'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Certifications', 0, 1, 'L');
        
        $processed = ['title' => []];
        foreach ($data as $row) {
            if ($row['title'] && !in_array($row['title'], $processed['title'])) {
                $pdf->Cell(0, 10, $row['title'] . ' - ' . $row['cert_institution'] . ' (' . $row['year'] . ')', 0, 1, 'L');
                $processed['title'][] = $row['title'];
            }
        }
    }

    // Languages
    if (!empty($data[0]['language'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(55, 82, 128); // #375280
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, 'Languages', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        
        $processed = ['language' => []];
        foreach ($data as $row) {
            if ($row['language'] && !in_array($row['language'], $processed['language'])) {
                $pdf->Cell(0, 10, $row['language'] . ' - ' . $row['language_level'], 0, 1, 'L');
                $processed['language'][] = $row['language'];
            }
        }
    }

    // Other Information
    if (!empty($data[0]['other_info'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(55, 82, 128); // #375280
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, 'Other Information', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, $data[0]['other_info'], 0, 'L');
    }

    // Adiciona banner no rodapé
    $bannerWidth = 160;
    $bannerHeight = 18;
    $bannerX = ($pageWidth - $bannerWidth) / 2;
    $pdf->SetY(-72);
    $pdf->Image('assets/images/banner.jpg', $bannerX, $pdf->GetY(), $bannerWidth, $bannerHeight);

    // Output PDF
    $pdf->Output('Technical_Resume_' . $data[0]['name'] . '.pdf', 'D');

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: view_resume.php?id=" . $_GET['id']);
    exit();
}