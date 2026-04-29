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

function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

function setFirstError(currentError, message) {
    return currentError || message;
}

function validateServiceForm() {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const delai = document.getElementById('delai_livraison').value.trim();
    const categorie = document.getElementById('id_categorie').value.trim();

    clearErrors();

    let firstError = '';

    if (titre === '') {
        showError('titre', 'Le titre du service est requis.');
        firstError = setFirstError(firstError, 'Le titre du service est requis.');
    } else if (titre.length < 5) {
        showError('titre', 'Le titre doit contenir au moins 5 caracteres.');
        firstError = setFirstError(firstError, 'Le titre doit contenir au moins 5 caracteres.');
    } else if (titre.length > 150) {
        showError('titre', 'Le titre ne doit pas depasser 150 caracteres.');
        firstError = setFirstError(firstError, 'Le titre ne doit pas depasser 150 caracteres.');
    }

    if (description === '') {
        showError('description', 'La description est requise.');
        firstError = setFirstError(firstError, 'La description est requise.');
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caracteres.');
        firstError = setFirstError(firstError, 'La description doit contenir au moins 20 caracteres.');
    } else if (description.length > 1000) {
        showError('description', 'La description ne doit pas depasser 1000 caracteres.');
        firstError = setFirstError(firstError, 'La description ne doit pas depasser 1000 caracteres.');
    }

    if (prix === '') {
        showError('prix', 'Le prix est requis.');
        firstError = setFirstError(firstError, 'Le prix est requis.');
    } else if (isNaN(prix) || Number(prix) <= 0) {
        showError('prix', 'Le prix doit etre un nombre positif.');
        firstError = setFirstError(firstError, 'Le prix doit etre un nombre positif.');
    } else if (Number(prix) > 99999) {
        showError('prix', 'Le prix ne doit pas depasser 99999.');
        firstError = setFirstError(firstError, 'Le prix ne doit pas depasser 99999.');
    }

    if (delai === '') {
        showError('delai_livraison', 'Le delai de livraison est requis.');
        firstError = setFirstError(firstError, 'Le delai de livraison est requis.');
    } else if (isNaN(delai) || Number(delai) < 1) {
        showError('delai_livraison', 'Le delai doit etre au moins 1 jour.');
        firstError = setFirstError(firstError, 'Le delai doit etre au moins 1 jour.');
    } else if (Number(delai) > 365) {
        showError('delai_livraison', 'Le delai ne doit pas depasser 365 jours.');
        firstError = setFirstError(firstError, 'Le delai ne doit pas depasser 365 jours.');
    }

    if (categorie === '') {
        showError('id_categorie', 'Veuillez selectionner une categorie.');
        firstError = setFirstError(firstError, 'Veuillez selectionner une categorie.');
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
    const thumbnailInput = document.getElementById('thumbnail');
    const form = document.getElementById('serviceForm');

    if (!form) {
        return;
    }

    ['titre', 'description', 'prix', 'delai_livraison', 'id_categorie'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) {
            return;
        }

        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
        field.addEventListener(eventName, function () {
            field.classList.remove('is-invalid');
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        });
    });

    form.addEventListener('submit', function (event) {
        if (!validateServiceForm()) {
            event.preventDefault();
            return;
        }

        if (thumbnailInput && thumbnailInput.files.length > 0) {
            const allowedThumb = ['jpg', 'jpeg', 'png', 'webp'];
            const ext = getExtension(thumbnailInput.files[0].name);

            if (!allowedThumb.includes(ext)) {
                event.preventDefault();
                showError('thumbnail', 'La miniature doit etre en JPG, JPEG, PNG ou WEBP.');
                showSweetAlert('La miniature doit etre en JPG, JPEG, PNG ou WEBP.');
            }
        }
    });
});
