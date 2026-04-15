/**
 * Validation côté client pour les formulaires de brainstorming
 * Validation spécifique pour les formulaires de création et édition de brainstormings
 */

class BrainstormingValidator {
    constructor() {
        this.errors = {};
    }

    /**
     * Valide le titre du brainstorming
     */
    validateTitre(titre, fieldName = 'titre') {
        titre = titre.trim();

        if (!titre) {
            this.errors[fieldName] = "Le titre est obligatoire.";
            return false;
        }

        if (titre.length < 5) {
            this.errors[fieldName] = "Le titre doit contenir au moins 5 caractères.";
            return false;
        }

        if (titre.length > 100) {
            this.errors[fieldName] = "Le titre ne peut pas dépasser 100 caractères.";
            return false;
        }

        // Vérifier les caractères autorisés
        const allowedPattern = /^[a-zA-Z0-9À-ÿ\s\-\.,!?\'"]+$/;
        if (!allowedPattern.test(titre)) {
            this.errors[fieldName] = "Le titre contient des caractères non autorisés.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide la description du brainstorming
     */
    validateDescription(description, fieldName = 'description') {
        description = description.trim();

        if (!description) {
            this.errors[fieldName] = "La description est obligatoire.";
            return false;
        }

        if (description.length < 20) {
            this.errors[fieldName] = "La description doit contenir au moins 20 caractères.";
            return false;
        }

        if (description.length > 2000) {
            this.errors[fieldName] = "La description ne peut pas dépasser 2000 caractères.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide la date de début
     */
    validateDateDebut(date, fieldName = 'date_debut') {
        if (!date) {
            this.errors[fieldName] = "La date de début est obligatoire.";
            return false;
        }

        const dateObj = new Date(date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (isNaN(dateObj.getTime())) {
            this.errors[fieldName] = "Format de date invalide.";
            return false;
        }

        if (dateObj < today) {
            this.errors[fieldName] = "La date de début ne peut pas être dans le passé.";
            return false;
        }

        // Vérifier que la date n'est pas trop lointaine (max 1 an)
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        if (dateObj > maxDate) {
            this.errors[fieldName] = "La date de début ne peut pas être supérieure à un an.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide un formulaire de brainstorming complet
     */
    validateForm(formData) {
        this.errors = {}; // Reset errors

        const titre = formData.get('titre') || '';
        const description = formData.get('description') || '';
        const dateDebut = formData.get('date_debut') || '';

        this.validateTitre(titre);
        this.validateDescription(description);
        this.validateDateDebut(dateDebut);

        return Object.keys(this.errors).length === 0;
    }

    /**
     * Retourne les erreurs de validation
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Retourne la première erreur
     */
    getFirstError() {
        const keys = Object.keys(this.errors);
        return keys.length > 0 ? this.errors[keys[0]] : null;
    }

    /**
     * Affiche les erreurs dans le DOM
     */
    displayErrors(containerId = 'error-container') {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';

        if (Object.keys(this.errors).length > 0) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '<ul class="mb-0">' +
                Object.values(this.errors).map(error => '<li>' + error + '</li>').join('') +
                '</ul>';
            container.appendChild(alertDiv);

            // Scroll vers les erreurs
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Efface les erreurs affichées
     */
    clearErrors(containerId = 'error-container') {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
        }
        this.errors = {};
    }

    /**
     * Met à jour l'affichage de validation d'un champ
     */
    updateFieldValidation(input, error) {
        // Supprimer les anciennes classes de validation
        input.classList.remove('is-valid', 'is-invalid');

        // Supprimer l'ancien message d'erreur
        const existingFeedback = input.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        if (error) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = error;
            input.parentNode.appendChild(feedback);
        } else if (input.value.trim() !== '') {
            input.classList.add('is-valid');
        }
    }
}

// Instance globale du validateur
const brainstormingValidator = new BrainstormingValidator();

// Fonction d'initialisation de la validation en temps réel
function initBrainstormingValidation() {
    // Validation du titre en temps réel
    const titreInput = document.getElementById('titre');
    if (titreInput) {
        titreInput.addEventListener('blur', function() {
            brainstormingValidator.validateTitre(this.value);
            brainstormingValidator.updateFieldValidation(this, brainstormingValidator.getErrors()['titre']);
        });

        // Compteur de caractères pour le titre
        titreInput.addEventListener('input', function() {
            updateCharCounter(this, 100);
        });
    }

    // Validation de la description en temps réel
    const descriptionInput = document.getElementById('description');
    if (descriptionInput) {
        descriptionInput.addEventListener('blur', function() {
            brainstormingValidator.validateDescription(this.value);
            brainstormingValidator.updateFieldValidation(this, brainstormingValidator.getErrors()['description']);
        });

        // Compteur de caractères pour la description
        descriptionInput.addEventListener('input', function() {
            updateCharCounter(this, 2000);
        });
    }

    // Validation de la date en temps réel
    const dateInput = document.getElementById('date_debut');
    if (dateInput) {
        dateInput.addEventListener('blur', function() {
            brainstormingValidator.validateDateDebut(this.value);
            brainstormingValidator.updateFieldValidation(this, brainstormingValidator.getErrors()['date_debut']);
        });

        // Définir la date minimale (aujourd'hui)
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
}

// Fonction pour mettre à jour le compteur de caractères
function updateCharCounter(input, maxLength) {
    const counter = input.parentNode.querySelector('.char-counter');
    if (counter) {
        const remaining = maxLength - input.value.length;
        counter.textContent = `${remaining} caractères restants`;
        counter.className = 'char-counter ' + (remaining < 0 ? 'text-danger' : remaining < 20 ? 'text-warning' : 'text-muted');
    }
}

// Fonction de validation avant soumission du formulaire
function validateBrainstormingForm(form) {
    const formData = new FormData(form);

    if (!brainstormingValidator.validateForm(formData)) {
        brainstormingValidator.displayErrors();
        return false;
    }

    brainstormingValidator.clearErrors();
    return true;
}

// Initialisation automatique au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initBrainstormingValidation();

    // Validation avant soumission des formulaires de brainstorming
    const brainstormingForms = document.querySelectorAll('form[id*="brainstorming"]');
    brainstormingForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateBrainstormingForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
});