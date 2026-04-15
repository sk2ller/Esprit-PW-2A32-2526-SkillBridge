// Validation for Adding Category Form
function validateCategoryForm() {
    const nom = document.getElementById('nom_categorie').value.trim();
    const description = document.getElementById('description').value.trim();
    const icone = document.getElementById('icone').value.trim();

    // Reset errors
    clearErrors();

    let isValid = true;

    // Validate name
    if (nom === '') {
        showError('nom_categorie', 'Le nom de la catégorie est requis.');
        isValid = false;
    } else if (nom.length < 3) {
        showError('nom_categorie', 'Le nom doit contenir au moins 3 caractères.');
        isValid = false;
    } else if (nom.length > 100) {
        showError('nom_categorie', 'Le nom ne doit pas dépasser 100 caractères.');
        isValid = false;
    }

    // Validate description
    if (description.length > 500) {
        showError('description', 'La description ne doit pas dépasser 500 caractères.');
        isValid = false;
    }

    // Validate icon
    if (icone === '') {
        showError('icone', 'Veuillez sélectionner une icône.');
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

// Attach validation to form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateCategoryForm()) {
                e.preventDefault();
            }
        });
    }
});
