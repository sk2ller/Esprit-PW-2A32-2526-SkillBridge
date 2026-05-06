const CANDIDATURE_RULES = {
    nom: { min: 3, max: 80, pattern: /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/ },
    email: { max: 254, pattern: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/ },
    message: { min: 20, max: 2000 },
    tarif: { min: 0, max: 10000000 },
    files: {
        cv: { maxSizeBytes: 5 * 1024 * 1024, allowedExt: ['pdf', 'doc', 'docx'] },
        portfolio: { maxSizeBytes: 10 * 1024 * 1024, allowedExt: ['pdf', 'doc', 'docx', 'zip'] }
    }
};

function normalizeSpaces(value) {
    return value.replace(/\s+/g, ' ').trim();
}

function validateCandidatureForm(event) {
    clearCandidatureErrors();

    const nomInput = document.getElementById('nom_freelancer');
    const emailInput = document.getElementById('email_freelancer');
    const tarifInput = document.getElementById('tarif_propose');
    const messageInput = document.getElementById('message');
    const cvInput = document.getElementById('cv_file');
    const portfolioInput = document.getElementById('portfolio_file');

    const nom = normalizeSpaces(nomInput.value);
    const email = emailInput.value.trim();
    const tarif = tarifInput.value.trim();
    const message = normalizeSpaces(messageInput.value);

    nomInput.value = nom;
    emailInput.value = email;
    messageInput.value = message;

    let isValid = true;
    const messages = [];

    if (nom === '') {
        showCandidatureError('nom_freelancer', 'Le nom est obligatoire.');
        messages.push('Le nom complet est obligatoire.');
        isValid = false;
    } else if (nom.length < CANDIDATURE_RULES.nom.min || nom.length > CANDIDATURE_RULES.nom.max) {
        showCandidatureError('nom_freelancer', 'Le nom doit contenir entre 3 et 80 caracteres.');
        messages.push('Le nom doit contenir entre 3 et 80 caracteres.');
        isValid = false;
    } else if (!CANDIDATURE_RULES.nom.pattern.test(nom)) {
        showCandidatureError('nom_freelancer', 'Le nom contient des caracteres non autorises.');
        messages.push('Le nom contient des caracteres non autorises.');
        isValid = false;
    }

    if (email === '') {
        showCandidatureError('email_freelancer', "L'email est obligatoire.");
        messages.push("L'email est obligatoire.");
        isValid = false;
    } else if (email.length > CANDIDATURE_RULES.email.max) {
        showCandidatureError('email_freelancer', "L'email est trop long.");
        messages.push("L'email est trop long.");
        isValid = false;
    } else if (!CANDIDATURE_RULES.email.pattern.test(email) || email.includes('..')) {
        showCandidatureError('email_freelancer', 'Veuillez saisir un email valide.');
        messages.push('Veuillez saisir un email valide.');
        isValid = false;
    }

    if (tarif !== '') {
        const tarifNumber = Number(tarif);
        const hasMoreThanTwoDecimals = !/^\d+(\.\d{1,2})?$/.test(tarif);
        if (!Number.isFinite(tarifNumber) || Number.isNaN(tarifNumber)) {
            showCandidatureError('tarif_propose', 'Le tarif doit etre un nombre valide.');
            messages.push('Le tarif doit etre un nombre valide.');
            isValid = false;
        } else if (tarifNumber < CANDIDATURE_RULES.tarif.min) {
            showCandidatureError('tarif_propose', 'Le tarif ne peut pas etre negatif.');
            messages.push('Le tarif ne peut pas etre negatif.');
            isValid = false;
        } else if (tarifNumber > CANDIDATURE_RULES.tarif.max) {
            showCandidatureError('tarif_propose', 'Le tarif est trop eleve.');
            messages.push('Le tarif est trop eleve.');
            isValid = false;
        } else if (hasMoreThanTwoDecimals) {
            showCandidatureError('tarif_propose', 'Le tarif doit avoir au maximum 2 decimales.');
            messages.push('Le tarif doit avoir au maximum 2 decimales.');
            isValid = false;
        }
    }

    if (message === '') {
        showCandidatureError('message', 'Le message est obligatoire.');
        messages.push('Le message est obligatoire.');
        isValid = false;
    } else if (message.length < CANDIDATURE_RULES.message.min) {
        showCandidatureError('message', 'Le message doit contenir au moins 20 caracteres.');
        messages.push('Le message doit contenir au moins 20 caracteres.');
        isValid = false;
    } else if (message.length > CANDIDATURE_RULES.message.max) {
        showCandidatureError('message', 'Le message ne doit pas depasser 2000 caracteres.');
        messages.push('Le message ne doit pas depasser 2000 caracteres.');
        isValid = false;
    }

    if (!validateFileInput(cvInput, CANDIDATURE_RULES.files.cv, 'cv_file', 'CV')) {
        messages.push('Le CV doit etre au format PDF, DOC ou DOCX et ne pas depasser 5MB.');
        isValid = false;
    }

    if (!validateFileInput(portfolioInput, CANDIDATURE_RULES.files.portfolio, 'portfolio_file', 'portfolio')) {
        messages.push('Le portfolio doit respecter les formats autorises et ne pas depasser 10MB.');
        isValid = false;
    }

    if (!isValid) {
        if (event) {
            event.preventDefault();
        }
        if (window.SkillBridgeAlerts) {
            window.SkillBridgeAlerts.dialog('error', 'Candidature invalide', messages[0] || 'Veuillez corriger les champs signales.');
        }
        return false;
    }

    return true;
}

function validateFileInput(fileInput, rule, fieldId, label) {
    const file = fileInput.files && fileInput.files[0];
    if (!file) {
        return true;
    }

    const ext = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';

    if (!rule.allowedExt.includes(ext)) {
        showCandidatureError(fieldId, `Format ${label} non autorise (${rule.allowedExt.join(', ').toUpperCase()}).`);
        return false;
    }

    if (file.size <= 0) {
        showCandidatureError(fieldId, `Le fichier ${label} est vide.`);
        return false;
    }

    if (file.size > rule.maxSizeBytes) {
        const maxMb = Math.round(rule.maxSizeBytes / (1024 * 1024));
        showCandidatureError(fieldId, `Le fichier ${label} ne doit pas depasser ${maxMb}MB.`);
        return false;
    }

    return true;
}

function showCandidatureError(fieldId, message) {
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

function clearCandidatureErrors() {
    document.querySelectorAll('#candidatureForm [id$="-error"]').forEach((element) => {
        element.textContent = '';
        element.style.display = 'none';
    });

    ['nom_freelancer', 'email_freelancer', 'tarif_propose', 'message', 'cv_file', 'portfolio_file'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.style.borderColor = '';
            field.style.backgroundColor = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('candidatureForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        if (!validateCandidatureForm(event)) {
            return;
        }
    });

    ['nom_freelancer', 'email_freelancer', 'tarif_propose', 'message', 'cv_file', 'portfolio_file'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) {
            return;
        }

        const eventName = field.type === 'file' ? 'change' : 'input';
        field.addEventListener(eventName, () => {
            const errorElement = document.getElementById(fieldId + '-error');
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
            field.style.borderColor = '';
            field.style.backgroundColor = '';
        });
    });
});
