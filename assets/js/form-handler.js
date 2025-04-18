document.addEventListener('DOMContentLoaded', function() {
    // Photo preview functionality
    initializePhotoPreview();

    // Initialize Technical Skills
    initializeTechnicalSkills();

    // Initialize other sections
    initializeOtherSections();
});

function initializePhotoPreview() {
    const photoInput = document.getElementById('profile_photo');
    const photoPreview = document.getElementById('photo_preview');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

function initializeTechnicalSkills() {
    const skillsContainer = document.querySelector('.skills-grid');
    const addSkillBtn = document.getElementById('add-skill-btn');
    const skillInputs = {
        technology: document.getElementById('new-skill'),
        level: document.getElementById('new-level'),
        levelDisplay: document.getElementById('level-display'),
        years: document.getElementById('new-years')
    };

    // Initialize level slider
    skillInputs.level.addEventListener('input', function() {
        skillInputs.levelDisplay.textContent = this.value;
    });

    // Add new skill handler
    addSkillBtn.addEventListener('click', function() {
        if (!validateSkillInputs(skillInputs)) return;
        addNewSkill(skillsContainer, skillInputs);
        clearSkillInputs(skillInputs);
    });

    // Load existing skills if any
    if (window.existingSkills) {
        window.existingSkills.forEach(skill => {
            addSkillToGrid(skillsContainer, skill);
        });
    }
}

function addSkillToGrid(container, skill) {
    const skillEntry = document.createElement('div');
    skillEntry.className = 'skill-entry';
    skillEntry.innerHTML = `
        <input type="text" name="technology[]" value="${skill.technology}" readonly>
        <div class="level-container">
            <input type="range" name="level[]" min="1" max="5" value="${skill.level}" class="level-slider" readonly>
            <span class="level-value">${skill.level}</span>
        </div>
        <input type="number" name="years[]" value="${skill.years}" readonly>
        <button type="button" class="remove-entry">×</button>
    `;

    container.appendChild(skillEntry);
    
    // Add remove handler
    skillEntry.querySelector('.remove-entry').addEventListener('click', function() {
        skillEntry.remove();
    });
}

function validateSkillInputs(inputs) {
    if (!inputs.technology.value.trim()) {
        alert('Please enter a technology name');
        inputs.technology.focus();
        return false;
    }
    if (!inputs.years.value) {
        alert('Please enter years of experience');
        inputs.years.focus();
        return false;
    }
    return true;
}

function addNewSkill(container, inputs) {
    const skillEntry = document.createElement('div');
    skillEntry.className = 'skill-entry';
    skillEntry.innerHTML = `
        <input type="text" name="technology[]" value="${inputs.technology.value}" readonly>
        <div class="level-container">
            <input type="range" name="level[]" min="1" max="5" value="${inputs.level.value}" class="level-slider" readonly>
            <span class="level-value">${inputs.level.value}</span>
        </div>
        <input type="number" name="years[]" value="${inputs.years.value}" readonly>
        <button type="button" class="remove-entry">×</button>
    `;

    container.appendChild(skillEntry);
    
    // Add remove handler
    skillEntry.querySelector('.remove-entry').addEventListener('click', function() {
        skillEntry.remove();
    });
}

function clearSkillInputs(inputs) {
    inputs.technology.value = '';
    inputs.level.value = 3;
    inputs.levelDisplay.textContent = '3';
    inputs.years.value = '';
    inputs.technology.focus();
}

function initializeOtherSections() {
    // Professional Experience Handler
    initializeSection('experience');
    
    // Academic Background Handler
    initializeSection('academic');
    
    // Certifications Handler
    initializeSection('certification');
    
    // Language Handler
    const addLanguageBtn = document.querySelector('.add-language');
    if (addLanguageBtn) {
        addLanguageBtn.addEventListener('click', function() {
            const container = document.querySelector('.language-entries');
            const template = document.querySelector('.language-entry').cloneNode(true);
            
            // Clear inputs
            template.querySelector('input[type="text"]').value = '';
            template.querySelector('select').selectedIndex = 0;
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-entry';
            removeBtn.textContent = 'Remove';
            removeBtn.onclick = function() {
                template.remove();
            };
            template.appendChild(removeBtn);
            
            // Add the new entry
            container.appendChild(template);
        });
    }
}

function initializeSection(type) {
    const addButton = document.querySelector(`.add-${type}`);
    if (addButton) {
        addButton.addEventListener('click', function() {
            const container = document.querySelector(`.${type}-entries`);
            const template = document.querySelector(`.${type}-entry`).cloneNode(true);
            
            // Clear all inputs in the cloned template
            template.querySelectorAll('input, textarea').forEach(input => {
                input.value = '';
            });
            
            // Add remove button if it doesn't exist
            if (!template.querySelector('.remove-entry')) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-entry';
                removeBtn.textContent = 'Remove';
                removeBtn.onclick = function() {
                    template.remove();
                };
                template.appendChild(removeBtn);
            }
            
            container.appendChild(template);
        });
    }

    // Add remove buttons to existing entries
    document.querySelectorAll(`.${type}-entry`).forEach(entry => {
        if (!entry.querySelector('.remove-entry') && document.querySelectorAll(`.${type}-entry`).length > 1) {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-entry';
            removeBtn.textContent = 'Remove';
            removeBtn.onclick = function() {
                entry.remove();
            };
            entry.appendChild(removeBtn);
        }
    });
}