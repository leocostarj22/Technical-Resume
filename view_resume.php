<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';

try {
    // Fetch resume with all related data
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

    // Organize data - modify this section to prevent duplicates
    $resume = $data[0];
    $skills = [];
    $experiences = [];
    $education = [];
    $certifications = [];
    $languages = [];

    $processed = [
        'technology' => [],
        'company' => [],
        'institution' => [],
        'title' => [],
        'language' => []
    ];

    foreach ($data as $row) {
        if ($row['technology'] && !in_array($row['technology'], $processed['technology'])) {
            $skills[] = $row;
            $processed['technology'][] = $row['technology'];
        }
        if ($row['company'] && !in_array($row['company'] . $row['position'], $processed['company'])) {
            $experiences[] = $row;
            $processed['company'][] = $row['company'] . $row['position'];
        }
        // Fix education data
        if ($row['institution'] && $row['course'] && !in_array($row['institution'] . $row['course'], $processed['institution'])) {
            $education[] = [
                'institution' => $row['institution'],
                'course' => $row['course'],
                'additional_info' => $row['additional_info']
            ];
            $processed['institution'][] = $row['institution'] . $row['course'];
        }

        // Fix certifications data
        if ($row['title'] && $row['cert_institution'] && !in_array($row['title'], $processed['title'])) {
            $certifications[] = [
                'title' => $row['title'],
                'cert_institution' => $row['cert_institution'],
                'year' => $row['year']
            ];
            $processed['title'][] = $row['title'];
        }

        // Fix languages data
        if ($row['language'] && !in_array($row['language'], $processed['language'])) {
            $languages[] = [
                'language' => $row['language'],
                'language_level' => $row['language_level']
            ];
            $processed['language'][] = $row['language'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/view.css">
</head>
<body>
    <div class="container resume-view">
        <!-- Display basic information -->
        <div class="section">
            <h1><?php echo htmlspecialchars($resume['name']); ?></h1>
            <h2><?php echo htmlspecialchars($resume['profession']); ?></h2>
            <?php if (!empty($resume['photo_path'])): ?>
                <?php
                    $photo_path = "uploads/" . $resume['photo_path'];
                    if (file_exists($photo_path)) {
                        echo '<img src="' . htmlspecialchars($photo_path) . '" alt="Profile Photo" class="profile-photo">';
                    } else {
                        echo '<!-- Photo file not found: ' . htmlspecialchars($photo_path) . ' -->';
                    }
                ?>
            <?php endif; ?>
            <p>Availability: <?php echo htmlspecialchars($resume['availability']); ?></p>
            <p>Location: <?php echo htmlspecialchars($resume['location']); ?></p>
        </div>

        <!-- Experience Summary -->
        <?php if (!empty($resume['experience_summary'])): ?>
        <div class="section">
            <h2>Experience Summary</h2>
            <div class="experience-summary">
                <?php echo nl2br(htmlspecialchars($resume['experience_summary'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Display skills -->
        <div class="section">
            <h2>Technical Skills</h2>
            <div class="skills-list">
                <?php foreach ($skills as $skill): ?>
                    <div class="skill-item">
                        <span class="technology"><?php echo htmlspecialchars($skill['technology']); ?></span>
                        <span class="level">Level: <?php echo $skill['level']; ?></span>
                        <span class="years"><?php echo $skill['years']; ?> years</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Professional Experience -->
        <div class="section">
            <h2>Professional Experience</h2>
            <?php foreach ($experiences as $exp): ?>
                <div class="experience-item">
                    <h3><?php echo htmlspecialchars($exp['position']); ?> at <?php echo htmlspecialchars($exp['company']); ?></h3>
                    <div class="dates"><?php echo $exp['start_date']; ?> - <?php echo $exp['end_date'] ?: 'Present'; ?></div>
                    <p><?php echo nl2br(htmlspecialchars($exp['main_activities'])); ?></p>
                    <p><strong>Technologies:</strong> <?php echo htmlspecialchars($exp['technologies']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Education -->
        <div class="section">
            <h2>Education</h2>
            <?php foreach ($education as $edu): ?>
                <div class="education-item">
                    <h3><?php echo htmlspecialchars($edu['course']); ?></h3>
                    <p><?php echo htmlspecialchars($edu['institution']); ?></p>
                    <?php if (!empty($edu['additional_info'])): ?>
                        <p><?php echo htmlspecialchars($edu['additional_info']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Certifications -->
        <div class="section">
            <h2>Certifications</h2>
            <?php foreach ($certifications as $cert): ?>
                <div class="certification-item">
                    <h3><?php echo htmlspecialchars($cert['title']); ?></h3>
                    <p><?php echo htmlspecialchars($cert['cert_institution']); ?> (<?php echo $cert['year']; ?>)</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Languages -->
        <div class="section">
            <h2>Languages</h2>
            <?php foreach ($languages as $lang): ?>
                <div class="language-item">
                    <span class="language"><?php echo htmlspecialchars($lang['language']); ?></span>
                    <span class="level"><?php echo htmlspecialchars($lang['language_level']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Other Information -->
        <?php if (!empty($resume['other_info'])): ?>
        <div class="section">
            <h2>Other Information</h2>
            <div class="other-info">
                <?php echo nl2br(htmlspecialchars($resume['other_info'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="actions">
            <a href="form.php?id=<?php echo $_GET['id']; ?>" class="button">Edit Resume</a>
            <a href="form.php" class="button">Create New Resume</a>
        </div>
    </div>
</body>
</html>
<?php
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: form.php");
    exit();
}
?>