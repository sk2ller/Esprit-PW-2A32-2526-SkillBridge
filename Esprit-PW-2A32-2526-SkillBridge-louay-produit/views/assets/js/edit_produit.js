// Validation pour le formulaire de modification de produit
// Réutilise la même logique que add_produit.js

function validateProduitForm() {
    const nom = document.getElementById('nom').value.trim();
    const description = document.getElementById('description').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const quantite = document.getElementById('quantite').value.trim();
    const categorie = document.getElementById('id_categorie').value.trim();

    clearErrors();
    let isValid = true;

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

    if (description === '') {
        showError('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 20) {
        showError('description', 'La description doit contenir au moins 20 caractères.');
        isValid = false;
    }

    if (prix === '') {
        showError('prix', 'Le prix est requis.');
        isValid = false;
    } else if (isNaN(prix) || prix <= 0) {
        showError('prix', 'Le prix doit être un nombre positif.');
        isValid = false;
    }

    if (quantite === '') {
        showError('quantite', 'La quantité est requise.');
        isValid = false;
    } else if (isNaN(quantite) || quantite < 1) {
        showError('quantite', 'La quantité doit être au moins 1.');
        isValid = false;
    }

    if (categorie === '') {
        showError('id_categorie', 'Veuillez sélectionner une catégorie.');
        isValid = false;
    }

    return isValid;
}

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

function clearErrors() {
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    document.querySelectorAll('.form-control').forEach(el => {
        el.style.borderColor = '';
        el.style.boxShadow = '';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('produitForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateProduitForm()) {
                e.preventDefault();
            }
        });
    }
});
