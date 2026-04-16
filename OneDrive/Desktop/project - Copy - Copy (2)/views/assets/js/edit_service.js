// Validation for Editing Service Form (same as add_service.js)
function validateServiceForm() {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const delai = document.getElementById('delai_livraison').value.trim();
    const categorie = document.getElementById('id_categorie').value.trim();

    // Reset errors
    clearErrors();

    let isValid = true;

    // Validate title
    if (titre === '') {
        showError('titre', 'Le titre du service est requis.');
        isValid = false;
    } else if (titre.length < 5) {
        showError('titre', 'Le titre doit contenir au moins 5 caractères.');
        isValid = false;
    } else if (titre.length > 150) {
        showError('titre', 'Le titre ne doit pas dépasser 150 caractères.');
        isValid = false;
    }

    // Validate description
    if (description === '') {
        showError('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caractères.');
        isValid = false;
    } else if (description.length > 1000) {
        showError('description', 'La description ne doit pas dépasser 1000 caractères.');
        isValid = false;
    }

    // Validate price
    if (prix === '') {
        showError('prix', 'Le prix est requis.');
        isValid = false;
    } else if (isNaN(prix) || prix <= 0) {
        showError('prix', 'Le prix doit être un nombre positif.');
        isValid = false;
    } else if (prix > 99999) {
        showError('prix', 'Le prix ne doit pas dépasser 99999.');
        isValid = false;
    }

    // Validate delivery time
    if (delai === '') {
        showError('delai_livraison', 'Le délai de livraison est requis.');
        isValid = false;
    } else if (isNaN(delai) || delai < 1) {
        showError('delai_livraison', 'Le délai doit être au moins 1 jour.');
        isValid = false;
    } else if (delai > 365) {
        showError('delai_livraison', 'Le délai ne doit pas dépasser 365 jours.');
        isValid = false;
    }

    // Validate category
    if (categorie === '') {
        showError('id_categorie', 'Veuillez sélectionner une catégorie.');
        isValid = false;
    }

    return isValid;
}

// Helper: Show error message
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }
}

// Helper: Clear all errors
function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
}

// Real-time character count for title
document.addEventListener('DOMContentLoaded', function() {
    const titreField = document.getElementById('titre');
    if (titreField) {
        titreField.addEventListener('input', function() {
            const count = this.value.length;
            console.log('Titre: ' + count + '/150 caractères');
        });
    }

    // Attach validation to form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateServiceForm()) {
                e.preventDefault();
            }
        });
    }
});
