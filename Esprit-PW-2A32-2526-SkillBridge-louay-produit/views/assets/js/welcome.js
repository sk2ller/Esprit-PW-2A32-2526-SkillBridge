/**
 * SkillBridge — Welcome & Authentication Page JS
 * Vanilla ES6+ — No jQuery, no frameworks
 * Handles: modal toggling, tab switching, form validation, tags input, password toggle
 */

'use strict';

// =========================================================
// DOM REFERENCES
// =========================================================
const authOverlay = document.getElementById('authOverlay');
const authModal = document.getElementById('authModal');
const authRoleBadge = document.getElementById('authRoleBadge');
const authTabs = document.getElementById('authTabs');
const loginTab = document.getElementById('loginTab');
const registerTab = document.getElementById('registerTab');
const faceAuthTab = document.getElementById('faceAuthTab');

// Form containers
const loginFormClient = document.getElementById('loginFormClient');
const registerFormClient = document.getElementById('registerFormClient');
const loginFormFreelancer = document.getElementById('loginFormFreelancer');
const registerFormFreelancer = document.getElementById('registerFormFreelancer');
const loginFormAdmin = document.getElementById('loginFormAdmin');
const faceAuthForm = document.getElementById('faceAuthForm');

// All form sections
const allForms = document.querySelectorAll('.auth-form');

// Current state
let currentRole = null; // 'client', 'freelancer', 'admin'
let currentTab = 'login';

// =========================================================
// MODAL OPEN / CLOSE
// =========================================================

/**
 * Opens the auth modal for a specific role
 * @param {string} role — 'client', 'freelancer', or 'admin'
 */
function openAuthModal(role) {
  currentRole = role;
  currentTab = 'login';

  // Update role badge
  authRoleBadge.className = 'auth-modal-role-badge auth-modal-role-badge--' + role;
  const roleLabels = { client: '👤 Client', freelancer: '🎨 Freelancer', admin: '🔒 Admin' };
  authRoleBadge.textContent = roleLabels[role] || role;

  // Show/hide tabs (admin has no register tab)
  if (role === 'admin') {
    authTabs.classList.add('auth-tabs--hidden');
  } else {
    authTabs.classList.remove('auth-tabs--hidden');
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    if (faceAuthTab) faceAuthTab.classList.remove('active');
  }

  // Show the correct form
  showForm(role, 'login');

  // Show overlay
  authOverlay.classList.add('active');
  document.body.style.overflow = 'hidden';
}

/**
 * Closes the auth modal
 */
function closeAuthModal() {
  authOverlay.classList.remove('active');
  document.body.style.overflow = '';
  // Reset validation errors after close
  setTimeout(() => { clearAllErrors(); }, 300);
}

// Close on overlay background click
authOverlay.addEventListener('click', (e) => {
  if (e.target === authOverlay) closeAuthModal();
});

// Close on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && authOverlay.classList.contains('active')) {
    closeAuthModal();
  }
});

// =========================================================
// TAB SWITCHING
// =========================================================

loginTab.addEventListener('click', () => {
  currentTab = 'login';
  loginTab.classList.add('active');
  registerTab.classList.remove('active');
  if (faceAuthTab) faceAuthTab.classList.remove('active');
  showForm(currentRole, 'login');
  
  // Dispatch custom event to tell face-auth to stop camera
  document.dispatchEvent(new CustomEvent('faceAuthClosed'));
});

registerTab.addEventListener('click', () => {
  currentTab = 'register';
  registerTab.classList.add('active');
  loginTab.classList.remove('active');
  if (faceAuthTab) faceAuthTab.classList.remove('active');
  showForm(currentRole, 'register');
  
  // Dispatch custom event
  document.dispatchEvent(new CustomEvent('faceAuthClosed'));
});

if (faceAuthTab) {
  faceAuthTab.addEventListener('click', () => {
    currentTab = 'face';
    faceAuthTab.classList.add('active');
    loginTab.classList.remove('active');
    registerTab.classList.remove('active');
    showForm(currentRole, 'face');
    
    // Dispatch custom event to tell face-auth to start
    document.dispatchEvent(new CustomEvent('faceAuthOpened', { detail: { role: currentRole } }));
  });
}

/**
 * Shows the correct form based on role + tab
 */
function showForm(role, tab) {
  // Hide all forms
  allForms.forEach(f => f.classList.remove('active'));
  if (faceAuthForm) faceAuthForm.classList.remove('active');
  clearAllErrors();

  // Determine which form to show
  if (tab === 'face') {
    faceAuthForm.classList.add('active');
    return;
  }

  if (role === 'admin') {
    loginFormAdmin.classList.add('active');
  } else if (role === 'client') {
    if (tab === 'login') loginFormClient.classList.add('active');
    else registerFormClient.classList.add('active');
  } else if (role === 'freelancer') {
    if (tab === 'login') loginFormFreelancer.classList.add('active');
    else registerFormFreelancer.classList.add('active');
  }
}

// =========================================================
// PASSWORD TOGGLE
// =========================================================
document.querySelectorAll('.auth-input-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('.auth-input');
    if (input.type === 'password') {
      input.type = 'text';
      btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
      input.type = 'password';
      btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
  });
});

// =========================================================
// TAGS INPUT (Skills)
// =========================================================
const tagsWrapper = document.getElementById('skillsTagsWrapper');
const tagsField = document.getElementById('skillsTagsField');
const tagsHidden = document.getElementById('skillsHidden');

if (tagsWrapper && tagsField && tagsHidden) {
  // Focus the input when clicking the wrapper
  tagsWrapper.addEventListener('click', () => tagsField.focus());

  tagsField.addEventListener('keydown', (e) => {
    const val = tagsField.value.trim();

    // Add tag on Enter or comma
    if ((e.key === 'Enter' || e.key === ',') && val) {
      e.preventDefault();
      addTag(val);
      tagsField.value = '';
    }

    // Remove last tag on Backspace when input is empty
    if (e.key === 'Backspace' && !tagsField.value) {
      const tags = tagsWrapper.querySelectorAll('.tag-item');
      if (tags.length > 0) {
        tags[tags.length - 1].remove();
        updateTagsHidden();
      }
    }
  });

  // Also add tag on blur if there's content
  tagsField.addEventListener('blur', () => {
    const val = tagsField.value.trim();
    if (val) {
      addTag(val);
      tagsField.value = '';
    }
  });
}

/**
 * Adds a tag element to the skills input
 */
function addTag(text) {
  // Prevent duplicates
  const existing = tagsWrapper.querySelectorAll('.tag-item');
  for (const tag of existing) {
    if (tag.dataset.value.toLowerCase() === text.toLowerCase()) return;
  }

  // Max 10 tags
  if (existing.length >= 10) return;

  const tag = document.createElement('span');
  tag.className = 'tag-item';
  tag.dataset.value = text;
  tag.innerHTML = `${escapeHtml(text)} <button type="button" class="tag-remove" onclick="removeTag(this)">×</button>`;
  tagsWrapper.insertBefore(tag, tagsField);
  updateTagsHidden();
}

/**
 * Removes a tag element
 */
function removeTag(btn) {
  btn.parentElement.remove();
  updateTagsHidden();
}

/**
 * Updates the hidden input value with comma-separated tags
 */
function updateTagsHidden() {
  const tags = tagsWrapper.querySelectorAll('.tag-item');
  const values = Array.from(tags).map(t => t.dataset.value);
  tagsHidden.value = values.join(',');
}

// =========================================================
// CLIENT-SIDE VALIDATION
// =========================================================

/**
 * Validates a form and returns true if valid
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
function validateForm(form) {
  let isValid = true;
  clearFormErrors(form);

  // Validate each field with a data-validate attribute
  form.querySelectorAll('[data-validate]').forEach(field => {
    const rules = field.dataset.validate.split('|');
    const fieldWrapper = field.closest('.auth-field');
    const errorEl = fieldWrapper.querySelector('.auth-field-error');
    let value = field.value.trim();

    // For tags input, check the hidden field
    if (field.id === 'skillsTagsField') {
      value = tagsHidden.value;
    }

    for (const rule of rules) {
      let errorMsg = null;

      if (rule === 'required' && !value) {
        errorMsg = 'Ce champ est requis.';
      } else if (rule === 'email' && value && !isValidEmail(value)) {
        errorMsg = 'Email invalide.';
      } else if (rule.startsWith('min:')) {
        const min = parseInt(rule.split(':')[1]);
        if (value && value.length < min) {
          errorMsg = `Minimum ${min} caractères.`;
        }
      } else if (rule === 'url' && value && !isValidUrl(value)) {
        errorMsg = 'URL invalide.';
      } else if (rule.startsWith('match:')) {
        const targetId = rule.split(':')[1];
        const targetField = form.querySelector('#' + targetId);
        if (targetField && value !== targetField.value) {
          errorMsg = 'Les mots de passe ne correspondent pas.';
        }
      }

      if (errorMsg) {
        fieldWrapper.classList.add('has-error');
        if (errorEl) {
          errorEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${errorMsg}`;
        }
        isValid = false;
        break; // Stop at first error for this field
      }
    }
  });

  return isValid;
}

/**
 * Clears validation errors for a specific form
 */
function clearFormErrors(form) {
  form.querySelectorAll('.auth-field').forEach(field => {
    field.classList.remove('has-error');
  });
}

/**
 * Clears all validation errors across all forms
 */
function clearAllErrors() {
  document.querySelectorAll('.auth-field').forEach(field => {
    field.classList.remove('has-error');
  });
}

// =========================================================
// FORM SUBMISSION
// =========================================================
document.querySelectorAll('.auth-form form').forEach(form => {
  form.addEventListener('submit', function(e) {
    // Validate before submitting
    if (!validateForm(this)) {
      e.preventDefault();
      return;
    }

    // Show loading state on submit button
    const submitBtn = this.querySelector('.auth-submit');
    if (submitBtn) {
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
    }

    // Let the form submit naturally (server-side handling)
  });
});

// =========================================================
// LIVE VALIDATION (on input)
// =========================================================
document.querySelectorAll('[data-validate]').forEach(field => {
  field.addEventListener('input', () => {
    const fieldWrapper = field.closest('.auth-field');
    // Only clear errors on input, don't re-validate immediately
    if (fieldWrapper.classList.contains('has-error')) {
      fieldWrapper.classList.remove('has-error');
    }
  });
});

// =========================================================
// UTILITY FUNCTIONS
// =========================================================

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidUrl(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
