/**
 * face-auth.js — SkillBridge
 * Handles face-api.js initialization, webcam stream, and authentication requests
 */

document.addEventListener('DOMContentLoaded', async () => {
    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    const loadingMessage = document.getElementById('faceLoading');
    const btnFaceLogin = document.getElementById('btnFaceLogin');
    const btnFaceRegister = document.getElementById('btnFaceRegister');
    const faceEmail = document.getElementById('faceEmail');
    const facePasswordGroup = document.getElementById('facePasswordGroup');
    const facePassword = document.getElementById('facePassword');
    const alertBox = document.getElementById('faceAlert');

    let stream = null;
    let isModelsLoaded = false;
    let detectionInterval = null;

    // Show alert
    function showAlert(msg, isError = true) {
        alertBox.style.display = 'block';
        alertBox.className = 'auth-alert ' + (isError ? 'auth-alert--error' : 'auth-alert--success');
        alertBox.innerHTML = msg;
    }

    function hideAlert() {
        alertBox.style.display = 'none';
    }

    // Load models
    async function loadModels() {
        if (isModelsLoaded) return true;
        try {
            loadingMessage.textContent = 'Chargement des modèles IA...';
            await faceapi.nets.tinyFaceDetector.loadFromUri('views/assets/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('views/assets/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('views/assets/models');
            isModelsLoaded = true;
            return true;
        } catch (error) {
            console.error(error);
            loadingMessage.textContent = 'Erreur lors du chargement des modèles.';
            return false;
        }
    }

    // Start video
    async function startVideo() {
        hideAlert();
        if (!await loadModels()) return;
        
        loadingMessage.textContent = 'Démarrage de la caméra...';
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch (err) {
            console.error(err);
            loadingMessage.textContent = 'Veuillez autoriser l\'accès à la caméra.';
            showAlert("Impossible d'accéder à la caméra.", true);
        }
    }

    // Stop video
    function stopVideo() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        if (detectionInterval) clearInterval(detectionInterval);
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        loadingMessage.style.display = 'flex';
        loadingMessage.textContent = 'Caméra arrêtée.';
    }

    // Process face and get descriptor
    async function getFaceDescriptor() {
        if (!video.srcObject || video.paused) return null;
        
        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();
            
        return detection ? Array.from(detection.descriptor) : null;
    }

    // Video play event to draw bounding box
    video.addEventListener('play', () => {
        loadingMessage.style.display = 'none';
        const displaySize = { width: video.width, height: video.height };
        faceapi.matchDimensions(canvas, displaySize);

        detectionInterval = setInterval(async () => {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (detection) {
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
            }
        }, 100);
    });

    // Listen to custom events from welcome.js
    document.addEventListener('faceAuthOpened', (e) => {
        const role = e.detail.role;
        startVideo();
        
        // Setup button styles based on role
        if (role === 'admin') {
            btnFaceLogin.className = 'auth-submit auth-submit--admin';
        } else if (role === 'freelancer') {
            btnFaceLogin.className = 'auth-submit auth-submit--freelancer';
        } else {
            btnFaceLogin.className = 'auth-submit auth-submit--client';
        }
    });

    document.addEventListener('faceAuthClosed', () => {
        stopVideo();
        facePasswordGroup.style.display = 'none';
    });

    // Handle Login
    btnFaceLogin.addEventListener('click', async () => {
        const email = faceEmail.value.trim();
        if (!email) return showAlert('Veuillez entrer votre email d\'abord.');
        
        btnFaceLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse...';
        btnFaceLogin.disabled = true;

        const descriptor = await getFaceDescriptor();
        
        if (!descriptor) {
            showAlert('Aucun visage détecté. Veuillez vous placer en face de la caméra.');
            resetBtns();
            return;
        }

        try {
            const response = await fetch('face_auth_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', email: email, descriptor: descriptor })
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert('Visage reconnu ! Connexion en cours...', false);
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                showAlert(data.error);
                resetBtns();
            }
        } catch (e) {
            showAlert('Erreur de connexion au serveur.');
            resetBtns();
        }
    });

    // Handle Register
    btnFaceRegister.addEventListener('click', async () => {
        if (facePasswordGroup.style.display === 'none') {
            // First click: show password field
            facePasswordGroup.style.display = 'block';
            showAlert('Veuillez entrer votre mot de passe pour confirmer votre identité.', false);
            return;
        }

        const email = faceEmail.value.trim();
        const password = facePassword.value;
        if (!email || !password) return showAlert('L\'email et le mot de passe sont requis.');

        btnFaceRegister.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
        btnFaceRegister.disabled = true;

        const descriptor = await getFaceDescriptor();
        
        if (!descriptor) {
            showAlert('Aucun visage détecté. Veuillez vous placer en face de la caméra.');
            resetBtns();
            return;
        }

        try {
            const response = await fetch('face_auth_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'register', email: email, password: password, descriptor: descriptor })
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert('Visage enregistré avec succès ! Vous pouvez maintenant utiliser Face ID.', false);
                facePassword.value = '';
                facePasswordGroup.style.display = 'none';
            } else {
                showAlert(data.error);
            }
        } catch (e) {
            showAlert('Erreur de connexion au serveur.');
        }
        resetBtns();
    });

    function resetBtns() {
        btnFaceLogin.innerHTML = '<i class="fas fa-unlock"></i> Se connecter';
        btnFaceLogin.disabled = false;
        btnFaceRegister.innerHTML = '<i class="fas fa-user-check"></i> Enregistrer';
        btnFaceRegister.disabled = false;
    }
});
