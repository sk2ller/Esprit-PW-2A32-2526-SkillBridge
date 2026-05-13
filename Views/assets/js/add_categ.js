function showSweetAlert(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Formulaire invalide',
            text: message,
            confirmButtonColor: '#f07c22',
            background: '#fffaf4',
            color: '#1f1f23'
        });
        return;
    }

    window.alert(message);
}

function setFirstError(currentError, message) {
    return currentError || message;
}

function validateCategoryForm() {
    const nom = document.getElementById('nom_categorie').value.trim();
    const description = document.getElementById('description').value.trim();
    const icone = document.getElementById('icone_custom').value.trim();

    clearErrors();

    let firstError = '';

    if (nom === '') {
        showError('nom_categorie', 'Le nom de la categorie est requis.');
        firstError = setFirstError(firstError, 'Le nom de la categorie est requis.');
    } else if (nom.length < 3) {
        showError('nom_categorie', 'Le nom doit contenir au moins 3 caracteres.');
        firstError = setFirstError(firstError, 'Le nom doit contenir au moins 3 caracteres.');
    } else if (nom.length > 100) {
        showError('nom_categorie', 'Le nom ne doit pas depasser 100 caracteres.');
        firstError = setFirstError(firstError, 'Le nom ne doit pas depasser 100 caracteres.');
    }

    if (description.length > 500) {
        showError('description', 'La description ne doit pas depasser 500 caracteres.');
        firstError = setFirstError(firstError, 'La description ne doit pas depasser 500 caracteres.');
    }

    if (icone === '') {
        showError('icone_custom', 'Veuillez selectionner ou saisir une icone.');
        firstError = setFirstError(firstError, 'Veuillez selectionner ou saisir une icone.');
    }

    if (firstError) {
        showSweetAlert(firstError);
        return false;
    }

    return true;
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }

    field.classList.add('is-invalid');
    field.style.borderColor = '#ef4444';
    field.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';

    const existing = field.parentElement.querySelector('.invalid-feedback');
    if (existing) {
        existing.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.82rem';
    errorDiv.style.marginTop = '6px';
    errorDiv.style.display = 'flex';
    errorDiv.style.alignItems = 'center';
    errorDiv.style.gap = '4px';
    field.parentElement.appendChild(errorDiv);
}

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach((el) => {
        el.classList.remove('is-invalid');
        el.style.borderColor = '';
        el.style.backgroundColor = '';
    });

    document.querySelectorAll('.invalid-feedback').forEach((el) => {
        el.remove();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('categoryForm');

    if (!form) {
        return;
    }

    ['nom_categorie', 'description', 'icone_custom'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) {
            return;
        }

        field.addEventListener('input', function () {
            field.classList.remove('is-invalid');
            field.style.borderColor = '';
            field.style.backgroundColor = '';
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        });
    });

    form.addEventListener('submit', function (event) {
        if (!validateCategoryForm()) {
            event.preventDefault();
        }
    });
});
