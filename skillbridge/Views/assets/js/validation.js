/**
 * Validation côté client pour les formulaires SkillBridge
 * Fournit des validations JavaScript de base pour améliorer l'expérience utilisateur
 */

class ClientValidation {
    constructor() {
        this.errors = {};
    }

    /**
     * Valide un email
     */
    validateEmail(email, fieldName = 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        email = email.trim();

        if (!email) {
            this.errors[fieldName] = "Le champ email est obligatoire.";
            return false;
        }

        if (!emailRegex.test(email)) {
            this.errors[fieldName] = "Format d'email invalide.";
            return false;
        }

        if (email.length > 255) {
            this.errors[fieldName] = "L'email ne peut pas dépasser 255 caractères.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide un mot de passe
     */
    validatePassword(password, fieldName = 'password') {
        if (!password) {
            this.errors[fieldName] = "Le mot de passe est obligatoire.";
            return false;
        }

        if (password.length < 8) {
            this.errors[fieldName] = "Le mot de passe doit contenir au moins 8 caractères.";
            return false;
        }

        if (password.length > 128) {
            this.errors[fieldName] = "Le mot de passe ne peut pas dépasser 128 caractères.";
            return false;
        }

        // Vérifier la complexité
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasDigit = /\d/.test(password);

        if (!hasLower || !hasUpper || !hasDigit) {
            this.errors[fieldName] = "Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide la confirmation du mot de passe
     */
    validatePasswordConfirm(password, confirm, fieldName = 'confirm') {
        if (!confirm) {
            this.errors[fieldName] = "La confirmation du mot de passe est obligatoire.";
            return false;
        }

        if (password !== confirm) {
            this.errors[fieldName] = "Les mots de passe ne correspondent pas.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide un nom ou prénom
     */
    validateName(name, fieldName = 'name') {
        name = name.trim();

        if (!name) {
            this.errors[fieldName] = "Le champ " + fieldName + " est obligatoire.";
            return false;
        }

        if (name.length < 2) {
            this.errors[fieldName] = "Le " + fieldName + " doit contenir au moins 2 caractères.";
            return false;
        }

        if (name.length > 50) {
            this.errors[fieldName] = "Le " + fieldName + " ne peut pas dépasser 50 caractères.";
            return false;
        }

        // Autoriser seulement les lettres, espaces, tirets et apostrophes
        const nameRegex = /^[a-zA-ZÀ-ÿ\s\-\']+$/;
        if (!nameRegex.test(name)) {
            this.errors[fieldName] = "Le " + fieldName + " ne peut contenir que des lettres, espaces, tirets et apostrophes.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide un texte générique
     */
    validateText(text, fieldName = 'text', minLength = 1, maxLength = 1000) {
        text = text.trim();

        if (!text) {
            this.errors[fieldName] = "Le champ " + fieldName + " est obligatoire.";
            return false;
        }

        if (text.length < minLength) {
            this.errors[fieldName] = "Le " + fieldName + " doit contenir au moins " + minLength + " caractères.";
            return false;
        }

        if (text.length > maxLength) {
            this.errors[fieldName] = "Le " + fieldName + " ne peut pas dépasser " + maxLength + " caractères.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Valide une date
     */
    validateDate(date, fieldName = 'date') {
        if (!date) {
            this.errors[fieldName] = "Le champ date est obligatoire.";
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
            this.errors[fieldName] = "La date ne peut pas être dans le passé.";
            return false;
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Vérifie si il y a des erreurs
     */
    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    /**
     * Retourne toutes les erreurs
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

        if (this.hasErrors()) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '<ul class="mb-0">' +
                Object.values(this.errors).map(error => '<li>' + error + '</li>').join('') +
                '</ul>';
            container.appendChild(alertDiv);
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
     * Valide un formulaire d'inscription
     */
    validateRegistrationForm(formData) {
        this.validateName(formData.get('nom'), 'nom');
        this.validateName(formData.get('prenom'), 'prénom');
        this.validateEmail(formData.get('email'));
        this.validatePassword(formData.get('password'));
        this.validatePasswordConfirm(formData.get('password'), formData.get('confirm'));

        return !this.hasErrors();
    }

    /**
     * Valide un formulaire de connexion
     */
    validateLoginForm(formData) {
        this.validateEmail(formData.get('email'));
        this.validateText(formData.get('password'), 'mot de passe', 1, 128);

        return !this.hasErrors();
    }

    /**
     * Valide un formulaire de brainstorming
     */
    validateBrainstormingForm(formData) {
        this.validateText(formData.get('titre'), 'titre', 5, 100);
        this.validateText(formData.get('description'), 'description', 20, 2000);
        this.validateDate(formData.get('date_debut'));

        return !this.hasErrors();
    }
}

// Fonction utilitaire pour la validation en temps réel
function setupRealTimeValidation() {
    const validator = new ClientValidation();

    // Validation des emails en temps réel
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function() {
            validator.validateEmail(this.value, this.name);
            updateFieldValidation(this, validator.getErrors()[this.name]);
        });
    });

    // Validation des mots de passe en temps réel
    document.querySelectorAll('input[type="password"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.name === 'confirm') {
                const password = document.querySelector('input[name="password"]')?.value || '';
                validator.validatePasswordConfirm(password, this.value);
            } else {
                validator.validatePassword(this.value);
            }
            updateFieldValidation(this, validator.getErrors()[this.name]);
        });
    });

    // Validation des noms en temps réel
    document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(input => {
        input.addEventListener('blur', function() {
            validator.validateName(this.value, this.name === 'nom' ? 'nom' : 'prénom');
            updateFieldValidation(this, validator.getErrors()[this.name === 'nom' ? 'nom' : 'prénom']);
        });
    });
}

// Fonction utilitaire pour mettre à jour l'affichage de validation d'un champ
function updateFieldValidation(input, error) {
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

// Initialiser la validation en temps réel au chargement de la page
document.addEventListener('DOMContentLoaded', setupRealTimeValidation);