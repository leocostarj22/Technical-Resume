<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
checkAuth();

$resume = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($resume) {
    // Fetch skills
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE resume_id = ?");
    $stmt->execute([$resume['id']]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch experiences
    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE resume_id = ?");
    $stmt->execute([$resume['id']]);
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch education
    $stmt = $pdo->prepare("SELECT * FROM education WHERE resume_id = ?");
    $stmt->execute([$resume['id']]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch certifications
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE resume_id = ?");
    $stmt->execute([$resume['id']]);
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch languages
    $stmt = $pdo->prepare("SELECT * FROM languages WHERE resume_id = ?");
    $stmt->execute([$resume['id']]);
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Resume Builder - New Resume</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <img src="assets/images/banner2.jpeg" alt="Banner" class="banner-image">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo" class="logo-image">
            </div>
            <div class="user-actions">
                <?php
                // Get user's latest resume
                $stmt = $pdo->prepare("SELECT id FROM resumes WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $latestResume = $stmt->fetch();
                if ($latestResume): ?>
                    <a href="view_resume.php?id=<?php echo $latestResume['id']; ?>" class="view-button">View Resume</a>
                <?php endif; ?>
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        </div>

        <form action="save.php" method="POST" enctype="multipart/form-data">
            <div class="container">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
            
            <!-- Photo Upload Section -->
            <div class="form-section">
                <h2>Profile Photo</h2>
                <div class="photo-upload">
                    <input type="file" name="profile_photo" accept="image/*" id="profile_photo" onchange="previewImage(this);">
                    <div class="photo-preview" id="photo_preview">
                        <?php if ($resume && $resume['photo_path']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($resume['photo_path']); ?>" alt="Current photo" class="preview-image">
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($resume['photo_path']); ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="form-section">
                <h2>Basic Information</h2>
                <input type="text" name="name" placeholder="Full Name" required 
                    value="<?php echo $resume ? htmlspecialchars($resume['name']) : ''; ?>">
                <input type="text" name="profession" placeholder="Profession" required
                    value="<?php echo $resume ? htmlspecialchars($resume['profession']) : ''; ?>">
                <input type="text" name="availability" placeholder="Availability"
                    value="<?php echo $resume ? htmlspecialchars($resume['availability']) : ''; ?>">
                <input type="text" name="location" placeholder="Location"
                    value="<?php echo $resume ? htmlspecialchars($resume['location']) : ''; ?>">
            </div>

            <!-- Experience Summary -->
            <div class="form-section">
                <h2>Experience Summary</h2>
                <textarea name="experience_summary" placeholder="Provide a brief summary of your professional experience" rows="4"><?php echo isset($resume) ? htmlspecialchars($resume['experience_summary']) : ''; ?></textarea>
            </div>

            <!-- Technical Skills -->
            <div class="form-section" id="technical-skills">
                <h2>Technical Skills</h2>
                <p class="skill-legend">Grades range from 1 (lowest) to 5 (highest).</p>
                
                <div class="skill-input-form">
                    <input type="text" id="new-skill" placeholder="Enter technology" class="skill-input">
                    <div class="level-container">
                        <input type="range" id="new-level" min="1" max="5" value="3" class="level-slider">
                        <span id="level-display" class="level-value">3</span>
                    </div>
                    <input type="number" id="new-years" placeholder="Years" min="0" max="50" class="years-input">
                    <button type="button" id="add-skill-btn" class="add-btn">Add</button>
                </div>

                <div class="skills-grid">
                    <div class="skill-entry header">
                        <div>Technology</div>
                        <div>Level</div>
                        <div>Years</div>
                        <div></div>
                    </div>
                </div>
            </div>

            <!-- Professional Experience -->
            <div class="form-section" id="professional-experience">
                <h2>Professional Experience</h2>
                <div class="experience-entries">
                    <?php if ($resume && !empty($experiences)): ?>
                        <?php foreach ($experiences as $exp): ?>
                            <div class="experience-entry">
                                <input type="text" name="company[]" placeholder="Company Name" 
                                    value="<?php echo htmlspecialchars($exp['company']); ?>">
                                <input type="text" name="position[]" placeholder="Position"
                                    value="<?php echo htmlspecialchars($exp['position']); ?>">
                                <div class="date-inputs">
                                    <label>Start Date:</label>
                                    <input type="date" name="start_date[]" 
                                        value="<?php echo $exp['start_date']; ?>">
                                    <label>End Date:</label>
                                    <input type="date" name="end_date[]"
                                        value="<?php echo $exp['end_date']; ?>">
                                </div>
                                <textarea name="main_activities[]" placeholder="Main Activities"><?php echo htmlspecialchars($exp['main_activities']); ?></textarea>
                                <textarea name="technologies[]" placeholder="Technologies Used"><?php echo htmlspecialchars($exp['technologies']); ?></textarea>
                                <button type="button" class="remove-entry">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="experience-entry">
                            <input type="text" name="company[]" placeholder="Company Name">
                            <input type="text" name="position[]" placeholder="Position">
                            <div class="date-inputs">
                                <label>Start Date:</label>
                                <input type="date" name="start_date[]">
                                <label>End Date:</label>
                                <input type="date" name="end_date[]">
                            </div>
                            <textarea name="main_activities[]" placeholder="Main Activities"></textarea>
                            <textarea name="technologies[]" placeholder="Technologies Used"></textarea>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="add-experience">Add More Experience</button>
            </div>

            <!-- Add resume ID if editing -->
            <?php if ($resume): ?>
                <input type="hidden" name="resume_id" value="<?php echo $resume['id']; ?>">
            <?php endif; ?>

            <!-- Academic Background -->
            <div class="form-section" id="academic-background">
                <h2>Academic Background</h2>
                <div class="academic-entries">
                    <?php if ($resume && !empty($education)): ?>
                        <?php foreach ($education as $edu): ?>
                            <div class="academic-entry">
                                <input type="text" name="institution[]" placeholder="University/Institution Name" 
                                    value="<?php echo htmlspecialchars($edu['institution']); ?>">
                                <input type="text" name="course[]" placeholder="Course Name"
                                    value="<?php echo htmlspecialchars($edu['course']); ?>">
                                <textarea name="additional_info[]" placeholder="Additional Information"><?php echo htmlspecialchars($edu['additional_info']); ?></textarea>
                                <button type="button" class="remove-entry">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="academic-entry">
                            <input type="text" name="institution[]" placeholder="University/Institution Name">
                            <input type="text" name="course[]" placeholder="Course Name">
                            <textarea name="additional_info[]" placeholder="Additional Information"></textarea>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="add-academic">Add More Education</button>
            </div>

            <!-- Certifications -->
            <div class="form-section" id="certifications">
                <h2>Certifications</h2>
                <div class="certification-entries">
                    <?php if ($resume && !empty($certifications)): ?>
                        <?php foreach ($certifications as $cert): ?>
                            <div class="certification-entry">
                                <input type="text" name="cert_title[]" placeholder="Certification Title" 
                                    value="<?php echo htmlspecialchars($cert['title']); ?>">
                                <input type="text" name="cert_institution[]" placeholder="Institution"
                                    value="<?php echo htmlspecialchars($cert['institution']); ?>">
                                <input type="number" name="cert_year[]" placeholder="Year"
                                    value="<?php echo htmlspecialchars($cert['year']); ?>">
                                <button type="button" class="remove-entry">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="certification-entry">
                            <input type="text" name="cert_title[]" placeholder="Certification Title">
                            <input type="text" name="cert_institution[]" placeholder="Institution">
                            <input type="number" name="cert_year[]" placeholder="Year">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="add-certification">Add More Certification</button>
            </div>

            <!-- Language Proficiency -->
            <div class="form-section" id="language-proficiency">
                <h2>Language Proficiency</h2>
                <div class="language-entries">
                    <?php if ($resume && !empty($languages)): ?>
                        <?php foreach ($languages as $lang): ?>
                            <div class="language-entry">
                                <input type="text" name="language[]" placeholder="Language" 
                                    value="<?php echo htmlspecialchars($lang['language']); ?>">
                                <select name="language_level[]">
                                    <option value="">Select Level</option>
                                    <option value="A1" <?php echo $lang['level'] === 'A1' ? 'selected' : ''; ?>>A1 - Beginner</option>
                                    <option value="A2" <?php echo $lang['level'] === 'A2' ? 'selected' : ''; ?>>A2 - Elementary</option>
                                    <option value="B1" <?php echo $lang['level'] === 'B1' ? 'selected' : ''; ?>>B1 - Intermediate</option>
                                    <option value="B2" <?php echo $lang['level'] === 'B2' ? 'selected' : ''; ?>>B2 - Upper Intermediate</option>
                                    <option value="C1" <?php echo $lang['level'] === 'C1' ? 'selected' : ''; ?>>C1 - Advanced</option>
                                    <option value="C2" <?php echo $lang['level'] === 'C2' ? 'selected' : ''; ?>>C2 - Proficient</option>
                                </select>
                                <button type="button" class="remove-entry">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="language-entry">
                            <input type="text" name="language[]" placeholder="Language">
                            <select name="language_level[]">
                                <option value="">Select Level</option>
                                <option value="A1">A1 - Beginner</option>
                                <option value="A2">A2 - Elementary</option>
                                <option value="B1">B1 - Intermediate</option>
                                <option value="B2">B2 - Upper Intermediate</option>
                                <option value="C1">C1 - Advanced</option>
                                <option value="C2">C2 - Proficient</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="add-language">Add More Language</button>
            </div>

            <!-- Other Information -->
            <div class="form-section">
                <h2>Other Information</h2>
                <textarea name="other_info" placeholder="Additional information you want to include" class="other-info-input"><?php echo $resume ? htmlspecialchars($resume['other_info']) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="action" value="save">Save Resume</button>
                <a href="generate_pdf.php?id=<?php echo $resume ? $resume['id'] : ''; ?>" class="button" target="_blank">Generate PDF</a>
                <a href="generate_word.php?id=<?php echo $resume ? $resume['id'] : ''; ?>" class="button" target="_blank">Generate Word</a>
            </div>

        </form>

        <footer class="page-footer">
            <img src="assets/images/banner.jpg" alt="Footer Banner" class="footer-banner">
        </footer>

        <!-- Add this before including form-handler.js -->
        <script>
            window.existingSkills = <?php 
                if ($resume && !empty($skills)) {
                    echo json_encode(array_map(function($skill) {
                        return [
                            'technology' => htmlspecialchars($skill['technology']),
                            'level' => (int)$skill['level'],
                            'years' => (int)$skill['years']
                        ];
                    }, $skills));
                } else {
                    echo '[]';
                }
            ?>;
        </script>
        <script>
            function previewImage(input) {
                const preview = document.getElementById('photo_preview');
                preview.innerHTML = '';
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('preview-image');
                        preview.appendChild(img);
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
        <script src="assets/js/form-handler.js"></script>
    </body>
</html>