// Validation for Offre Job Form
function validateOffreForm() {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const budget = document.getElementById('budget').value.trim();
    const niveau_requis = document.getElementById('niveau_requis').value.trim();
    const competences = document.getElementById('competences_requises').value.trim();

    // Reset errors
    clearErrors();

    let isValid = true;

    // Validate titre
    if (titre === '') {
        showError('titre', 'Le titre de l\'offre est requis.');
        isValid = false;
    } else if (titre.length < 5) {
        showError('titre', 'Le titre doit contenir au moins 5 caractères.');
        isValid = false;
    } else if (titre.length > 200) {
        showError('titre', 'Le titre ne doit pas dépasser 200 caractères.');
        isValid = false;
    }

    // Validate description
    if (description === '') {
        showError('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caractères.');
        isValid = false;
    } else if (description.length > 5000) {
        showError('description', 'La description ne doit pas dépasser 5000 caractères.');
        isValid = false;
    }

    // Validate budget
    if (budget === '') {
        showError('budget', 'Le budget est requis.');
        isValid = false;
    } else if (isNaN(budget) || budget <= 0) {
        showError('budget', 'Le budget doit être un nombre positif.');
        isValid = false;
    } else if (budget > 999999) {
        showError('budget', 'Le budget ne doit pas dépasser 999999.');
        isValid = false;
    }

    // Validate niveau_requis
    if (niveau_requis === '') {
        showError('niveau', 'Veuillez sélectionner un niveau requis.');
        isValid = false;
    }

    // Validate competences (optional but check format if provided)
    if (competences !== '') {
        if (competences.length > 500) {
            showError('competences', 'Les compétences ne doivent pas dépasser 500 caractères.');
            isValid = false;
        }
        // Check for valid comma-separated format
        const comptences_list = competences.split(',').map(c => c.trim());
        if (comptences_list.length > 20) {
            showError('competences', 'Maximum 20 compétences autorisées.');
            isValid = false;
        }
    }

    if (!isValid) {
        event.preventDefault();
        return false;
    }

    return true;
}

// Show error message in a specific field
function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    // Highlight the field
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ef4444';
        field.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
    }
}

// Clear all error messages
function clearErrors() {
    const errorElements = document.querySelectorAll('[id$="-error"]');
    errorElements.forEach(element => {
        element.textContent = '';
        element.style.display = 'none';
    });
    
    // Reset field styles
    const fields = ['titre', 'description', 'budget', 'niveau_requis', 'competences_requises'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.style.borderColor = '';
            field.style.backgroundColor = '';
        }
    });
}

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('offreForm');
    if (!form) return;

    // Titre validation
    const titleField = document.getElementById('titre');
    if (titleField) {
        titleField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && (value.length < 5 || value.length > 200)) {
                showError('titre', 'Le titre doit contenir entre 5 et 200 caractères.');
            } else {
                document.getElementById('titre-error').textContent = '';
            }
        });
        titleField.addEventListener('input', function() {
            const charCount = this.value.length;
            if (charCount > 200) {
                this.value = this.value.substring(0, 200);
            }
        });
    }

    // Description validation
    const descField = document.getElementById('description');
    if (descField) {
        descField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value.length < 20) {
                showError('description', 'La description doit contenir au moins 20 caractères.');
            } else {
                document.getElementById('description-error').textContent = '';
            }
        });
        descField.addEventListener('input', function() {
            const charCount = this.value.length;
            if (charCount > 5000) {
                this.value = this.value.substring(0, 5000);
            }
        });
    }

    // Budget validation
    const budgetField = document.getElementById('budget');
    if (budgetField) {
        budgetField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && (isNaN(value) || value <= 0)) {
                showError('budget', 'Le budget doit être un nombre positif.');
            } else {
                document.getElementById('budget-error').textContent = '';
            }
        });
    }

    // Niveau validation
    const niveauField = document.getElementById('niveau_requis');
    if (niveauField) {
        niveauField.addEventListener('change', function() {
            if (this.value === '') {
                showError('niveau', 'Veuillez sélectionner un niveau requis.');
            } else {
                document.getElementById('niveau-error').textContent = '';
            }
        });
    }

    // Competences validation
    const competencesField = document.getElementById('competences_requises');
    if (competencesField) {
        competencesField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value.length > 500) {
                showError('competences', 'Les compétences ne doivent pas dépasser 500 caractères.');
            } else {
                document.getElementById('competences-error').textContent = '';
            }
        });
    }
});
