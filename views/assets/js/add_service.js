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

function setAiStatus(message, isError) {
    const status = document.getElementById('aiGenerationStatus');
    if (!status) {
        return;
    }

    status.textContent = message;
    status.style.color = isError ? '#ef4444' : 'var(--text-muted)';
}

function applyAiSuggestion(data) {
    const titre = document.getElementById('titre');
    const description = document.getElementById('description');
    const prix = document.getElementById('prix');
    const priceBox = document.getElementById('aiPriceSuggestion');
    const priceText = document.getElementById('aiSuggestedPrice');

    if (titre && data.titre) {
        titre.value = data.titre;
    }

    if (description && data.description) {
        description.value = data.description;
    }

    if (prix && data.prix_suggere) {
        prix.value = data.prix_suggere;
    }

    if (priceBox && priceText && data.prix_suggere) {
        priceText.textContent = data.prix_suggere;
        priceBox.style.display = 'block';
    }
}

function initAiGeneration() {
    const button = document.getElementById('generateAiService');
    const competences = document.getElementById('competences_ai');
    const categorie = document.getElementById('id_categorie');

    if (!button || !competences || !categorie) {
        return;
    }

    button.addEventListener('click', function () {
        const skills = competences.value.trim();

        if (skills === '') {
            showSweetAlert('Veuillez saisir vos competences avant de generer le service.');
            return;
        }

        button.disabled = true;
        setAiStatus('Generation en cours...', false);

        const formData = new FormData();
        formData.append('competences', skills);
        formData.append('id_categorie', categorie.value);

        fetch('index.php?page=generate_service_ai', {
            method: 'POST',
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Generation impossible pour le moment.');
                }

                applyAiSuggestion(data);
                setAiStatus(data.source === 'gemini' ? 'Suggestion generee avec Gemini.' : 'Suggestion generee localement.', false);
            })
            .catch((error) => {
                setAiStatus(error.message, true);
                showSweetAlert(error.message);
            })
            .finally(() => {
                button.disabled = false;
            });
    });
}

function initAiImageGeneration() {
    const button = document.getElementById('generateAiImage');
    const competences = document.getElementById('competences_ai');
    const categorie = document.getElementById('id_categorie');
    const titre = document.getElementById('titre');
    const description = document.getElementById('description');
    const hiddenThumbnail = document.getElementById('generated_thumbnail');
    const previewBox = document.getElementById('aiImagePreview');
    const previewImage = document.getElementById('aiGeneratedImagePreview');
    const status = document.getElementById('aiImageStatus');
    const imageStyle = document.getElementById('aiImageStyle');

    if (!button || !hiddenThumbnail) {
        return;
    }

    button.addEventListener('click', function () {
        button.disabled = true;
        if (status) {
            status.textContent = 'Generation image en cours...';
            status.style.color = 'var(--text-muted)';
        }

        const formData = new FormData();
        formData.append('titre', titre ? titre.value.trim() : '');
        formData.append('description', description ? description.value.trim() : '');
        formData.append('competences', competences ? competences.value.trim() : '');
        formData.append('id_categorie', categorie ? categorie.value : '');
        formData.append('image_style', imageStyle ? imageStyle.value : 'modern');

        fetch('index.php?page=generate_service_image_ai', {
            method: 'POST',
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Generation image impossible pour le moment.');
                }

                hiddenThumbnail.value = data.thumbnail;
                if (previewBox && previewImage) {
                    previewImage.src = data.image_url;
                    previewBox.style.display = 'block';
                }
                if (status) {
                    status.textContent = 'Image IA generee et prete a etre utilisee.';
                    status.style.color = 'var(--text-muted)';
                }
            })
            .catch((error) => {
                if (status) {
                    status.textContent = error.message;
                    status.style.color = '#ef4444';
                }
                showSweetAlert(error.message);
            })
            .finally(() => {
                button.disabled = false;
            });
    });
}

function initAiTranslation() {
    const button = document.getElementById('translateAiDescription');
    const description = document.getElementById('description');
    const status = document.getElementById('aiTranslationStatus');
    const preview = document.getElementById('translationPreview');
    const translationFr = document.getElementById('translationFr');
    const translationEn = document.getElementById('translationEn');

    if (!button || !description) {
        return;
    }

    button.addEventListener('click', function () {
        const text = description.value.trim();

        if (text === '') {
            showSweetAlert('Veuillez saisir une description a traduire.');
            return;
        }

        button.disabled = true;
        if (status) {
            status.textContent = 'Traduction en cours...';
            status.style.color = 'var(--text-muted)';
        }

        const formData = new FormData();
        formData.append('description', text);

        fetch('index.php?page=translate_service_ai', {
            method: 'POST',
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Traduction impossible pour le moment.');
                }

                if (translationFr) {
                    translationFr.textContent = data.description_fr;
                }
                if (translationEn) {
                    translationEn.textContent = data.description_en;
                }
                if (preview) {
                    preview.style.display = 'block';
                }
                if (status) {
                    status.textContent = data.source === 'gemini'
                        ? 'Description traduite avec Gemini.'
                        : 'Langue detectee: ' + data.langue_detectee + '. Configurez GEMINI_API_KEY pour une vraie traduction IA.';
                    status.style.color = 'var(--text-muted)';
                }
            })
            .catch((error) => {
                if (status) {
                    status.textContent = error.message;
                    status.style.color = '#ef4444';
                }
                showSweetAlert(error.message);
            })
            .finally(() => {
                button.disabled = false;
            });
    });
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

    initAiGeneration();
    initAiImageGeneration();
    initAiTranslation();

    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function () {
            const generatedThumbnail = document.getElementById('generated_thumbnail');
            if (generatedThumbnail && thumbnailInput.files.length > 0) {
                generatedThumbnail.value = '';
            }
        });
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
