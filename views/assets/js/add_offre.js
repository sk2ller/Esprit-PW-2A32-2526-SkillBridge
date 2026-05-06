function validateOffreForm(event) {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const budget = document.getElementById('budget').value.trim();
    const niveauRequis = document.getElementById('niveau_requis').value.trim();
    const competences = document.getElementById('competences_requises').value.trim();

    clearErrors();

    let isValid = true;
    const messages = [];

    if (titre === '') {
        showError('titre', "Le titre de l'offre est requis.");
        messages.push("Le titre de l'offre est obligatoire.");
        isValid = false;
    } else if (titre.length < 5) {
        showError('titre', 'Le titre doit contenir au moins 5 caracteres.');
        messages.push('Le titre doit contenir au moins 5 caracteres.');
        isValid = false;
    } else if (titre.length > 200) {
        showError('titre', 'Le titre ne doit pas depasser 200 caracteres.');
        messages.push('Le titre ne doit pas depasser 200 caracteres.');
        isValid = false;
    }

    if (description === '') {
        showError('description', 'La description est requise.');
        messages.push('La description est obligatoire.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caracteres.');
        messages.push('La description doit contenir au moins 20 caracteres.');
        isValid = false;
    } else if (description.length > 5000) {
        showError('description', 'La description ne doit pas depasser 5000 caracteres.');
        messages.push('La description ne doit pas depasser 5000 caracteres.');
        isValid = false;
    }

    if (budget === '') {
        showError('budget', 'Le budget est requis.');
        messages.push('Le budget est obligatoire.');
        isValid = false;
    } else if (isNaN(budget) || Number(budget) <= 0) {
        showError('budget', 'Le budget doit etre un nombre positif.');
        messages.push('Le budget doit etre un nombre positif.');
        isValid = false;
    } else if (Number(budget) > 999999) {
        showError('budget', 'Le budget ne doit pas depasser 999999.');
        messages.push('Le budget ne doit pas depasser 999999.');
        isValid = false;
    }

    if (niveauRequis === '') {
        showError('niveau_requis', 'Veuillez selectionner un niveau requis.');
        messages.push('Veuillez selectionner un niveau requis.');
        isValid = false;
    }

    if (competences !== '') {
        if (competences.length > 500) {
            showError('competences_requises', 'Les competences ne doivent pas depasser 500 caracteres.');
            messages.push('Les competences ne doivent pas depasser 500 caracteres.');
            isValid = false;
        }
        const competencesList = competences.split(',').map((item) => item.trim()).filter(Boolean);
        if (competencesList.length > 20) {
            showError('competences_requises', 'Maximum 20 competences autorisees.');
            messages.push('Maximum 20 competences autorisees.');
            isValid = false;
        }
    }

    if (!isValid) {
        if (event) {
            event.preventDefault();
        }
        if (window.SkillBridgeAlerts) {
            window.SkillBridgeAlerts.dialog('error', 'Formulaire incomplet', messages[0] || 'Veuillez corriger les champs signales.');
        }
        return false;
    }

    return true;
}

function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ef4444';
        field.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
    }
}

function clearErrors() {
    document.querySelectorAll('[id$="-error"]').forEach((element) => {
        element.textContent = '';
        element.style.display = 'none';
    });

    ['titre', 'description', 'budget', 'niveau_requis', 'competences_requises'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.style.borderColor = '';
            field.style.backgroundColor = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('offreForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (!validateOffreForm(event)) {
            return;
        }
    });

    ['titre', 'description', 'budget', 'niveau_requis', 'competences_requises'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) {
            return;
        }

        const resetFn = function () {
            const error = document.getElementById(fieldId + '-error');
            if (error) {
                error.textContent = '';
                error.style.display = 'none';
            }
            field.style.borderColor = '';
            field.style.backgroundColor = '';
        };

        field.addEventListener('input', resetFn);
        field.addEventListener('change', resetFn);
    });
});
