function showSweetAlert(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Validation',
            text: message,
            confirmButtonColor: '#7c3aed'
        });
        return;
    }

    window.alert(message);
}

function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

function validateServiceForm() {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const delai = document.getElementById('delai_livraison').value.trim();
    const categorie = document.getElementById('id_categorie').value.trim();

    clearErrors();

    let isValid = true;

    if (titre === '') {
        showError('titre', 'Le titre du service est requis.');
        isValid = false;
    } else if (titre.length < 5) {
        showError('titre', 'Le titre doit contenir au moins 5 caracteres.');
        isValid = false;
    } else if (titre.length > 150) {
        showError('titre', 'Le titre ne doit pas depasser 150 caracteres.');
        isValid = false;
    }

    if (description === '') {
        showError('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caracteres.');
        isValid = false;
    } else if (description.length > 1000) {
        showError('description', 'La description ne doit pas depasser 1000 caracteres.');
        isValid = false;
    }

    if (prix === '') {
        showError('prix', 'Le prix est requis.');
        isValid = false;
    } else if (isNaN(prix) || Number(prix) <= 0) {
        showError('prix', 'Le prix doit etre un nombre positif.');
        isValid = false;
    } else if (Number(prix) > 99999) {
        showError('prix', 'Le prix ne doit pas depasser 99999.');
        isValid = false;
    }

    if (delai === '') {
        showError('delai_livraison', 'Le delai de livraison est requis.');
        isValid = false;
    } else if (isNaN(delai) || Number(delai) < 1) {
        showError('delai_livraison', 'Le delai doit etre au moins 1 jour.');
        isValid = false;
    } else if (Number(delai) > 365) {
        showError('delai_livraison', 'Le delai ne doit pas depasser 365 jours.');
        isValid = false;
    }

    if (categorie === '') {
        showError('id_categorie', 'Veuillez selectionner une categorie.');
        isValid = false;
    }

    return isValid;
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }

    field.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentElement.appendChild(errorDiv);
}

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach((el) => {
        el.classList.remove('is-invalid');
    });

    document.querySelectorAll('.invalid-feedback').forEach((el) => {
        el.remove();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const titreField = document.getElementById('titre');
    const thumbnailInput = document.getElementById('thumbnail');
    const form = document.querySelector('form');

    if (titreField) {
        titreField.addEventListener('input', function () {
            console.log('Titre: ' + this.value.length + '/150 caracteres');
        });
    }

    if (!form) {
        return;
    }

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
                showSweetAlert('La miniature doit etre en JPG, JPEG, PNG ou WEBP.');
            }
        }
    });
});
