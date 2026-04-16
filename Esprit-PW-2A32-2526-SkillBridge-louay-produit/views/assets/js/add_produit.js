// Validation pour le formulaire d'ajout de produit
function validateProduitForm() {
    const nom = document.getElementById('nom').value.trim();
    const description = document.getElementById('description').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const quantite = document.getElementById('quantite').value.trim();
    const categorie = document.getElementById('id_categorie').value.trim();

    // Réinitialiser les erreurs
    clearErrors();

    let isValid = true;

    // Valider le nom
    if (nom === '') {
        showError('nom', 'Le nom du produit est requis.');
        isValid = false;
    } else if (nom.length < 5) {
        showError('nom', 'Le nom doit contenir au moins 5 caractères.');
        isValid = false;
    } else if (nom.length > 200) {
        showError('nom', 'Le nom ne doit pas dépasser 200 caractères.');
        isValid = false;
    }

    // Valider la description
    if (description === '') {
        showError('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caractères.');
        isValid = false;
    } else if (description.length > 2000) {
        showError('description', 'La description ne doit pas dépasser 2000 caractères.');
        isValid = false;
    }

    // Valider le prix
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

    // Valider la quantité
    if (quantite === '') {
        showError('quantite', 'La quantité est requise.');
        isValid = false;
    } else if (isNaN(quantite) || quantite < 1) {
        showError('quantite', 'La quantité doit être au moins 1.');
        isValid = false;
    } else if (quantite > 99999) {
        showError('quantite', 'La quantité ne doit pas dépasser 99999.');
        isValid = false;
    }

    // Valider la catégorie
    if (categorie === '') {
        showError('id_categorie', 'Veuillez sélectionner une catégorie.');
        isValid = false;
    }

    return isValid;
}

// Afficher une erreur
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ef4444';
        field.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.style.color = '#f87171';
        errorDiv.style.fontSize = '0.78rem';
        errorDiv.style.marginTop = '4px';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }
}

// Effacer toutes les erreurs
function clearErrors() {
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
    document.querySelectorAll('.form-control').forEach(el => {
        el.style.borderColor = '';
        el.style.boxShadow = '';
    });
}

// Attacher la validation au formulaire
document.addEventListener('DOMContentLoaded', function() {
    const nomField = document.getElementById('nom');
    if (nomField) {
        nomField.addEventListener('input', function() {
            const count = this.value.length;
            console.log('Nom: ' + count + '/200 caractères');
        });
    }

    const form = document.getElementById('produitForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateProduitForm()) {
                e.preventDefault();
            }
        });
    }
});
