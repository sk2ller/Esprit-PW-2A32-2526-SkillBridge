// Validation pour le formulaire de catégorie (admin)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('categorieForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nom = document.getElementById('nom_categorie');
            if (nom && nom.value.trim() === '') {
                e.preventDefault();
                nom.style.borderColor = '#ef4444';
                nom.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
                
                // Supprimer les anciens messages d'erreur
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.style.color = '#f87171';
                errorDiv.style.fontSize = '0.78rem';
                errorDiv.style.marginTop = '4px';
                errorDiv.textContent = 'Le nom de la catégorie est requis.';
                nom.parentElement.appendChild(errorDiv);
            } else if (nom && nom.value.trim().length < 3) {
                e.preventDefault();
                nom.style.borderColor = '#ef4444';
                nom.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
                
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.style.color = '#f87171';
                errorDiv.style.fontSize = '0.78rem';
                errorDiv.style.marginTop = '4px';
                errorDiv.textContent = 'Le nom doit contenir au moins 3 caractères.';
                nom.parentElement.appendChild(errorDiv);
            }
        });
    }
});
