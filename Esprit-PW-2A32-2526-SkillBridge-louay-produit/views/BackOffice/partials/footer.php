
</main>

<!-- AI Assistant Widget (Admin — Sigma) -->
<link rel="stylesheet" href="views/assets/css/ai-assistant.css">
<script src="views/assets/js/ai-assistant.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert confirm dialogs
document.addEventListener('click', function (event) {
  const trigger = event.target.closest('.js-swal-confirm');
  const exportTrigger = event.target.closest('.js-admin-export');

  if (exportTrigger) {
    event.preventDefault();
    const exportFrame = document.getElementById('adminExportFrame');
    if (exportFrame) {
      exportFrame.src = exportTrigger.href;
    } else {
      window.open(exportTrigger.href, 'adminExportFrame');
    }
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Export lancé',
        text: 'La préparation du document a commencé sans quitter cette page.',
        timer: 1800,
        showConfirmButton: false
      });
    }
    return;
  }

  if (!trigger) return;

  event.preventDefault();
  Swal.fire({
    title: trigger.dataset.swalTitle || 'Confirmer l\'action',
    text: trigger.dataset.swalText || 'Voulez-vous continuer ?',
    icon: trigger.dataset.swalIcon || 'warning',
    showCancelButton: true,
    confirmButtonText: trigger.dataset.swalConfirm || 'Oui',
    cancelButtonText: trigger.dataset.swalCancel || 'Annuler',
    confirmButtonColor: '#7e56ff',
    cancelButtonColor: '#6b7280',
    customClass: { popup: 'sb-admin-modal' }
  }).then(function (result) {
    if (result.isConfirmed) {
      window.location.href = trigger.href;
    }
  });
});

// Dark/Light Theme Toggle
function toggleTheme() {
  const html = document.documentElement;
  const icon = document.getElementById('themeIcon');
  const isDark = !html.hasAttribute('data-theme') || html.getAttribute('data-theme') === 'dark';
  
  if (isDark) {
    html.setAttribute('data-theme', 'light');
    if (icon) { icon.className = 'fas fa-sun'; }
    localStorage.setItem('sb-theme', 'light');
  } else {
    html.setAttribute('data-theme', 'dark');
    if (icon) { icon.className = 'fas fa-moon'; }
    localStorage.setItem('sb-theme', 'dark');
  }
}

// Apply saved theme on load
(function() {
  const saved = localStorage.getItem('sb-theme');
  if (saved === 'light') {
    document.documentElement.setAttribute('data-theme', 'light');
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = 'fas fa-sun';
  }
})();
</script>

<?php
  $aiAdminContext = [
    'currentPage' => $_GET['page'] ?? 'admin_dashboard',
    'userName' => $_SESSION['user']['name'] ?? 'Admin'
  ];
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof AiAssistant !== 'undefined') {
    AiAssistant.init({
      role: 'admin',
      context: <?= json_encode($aiAdminContext, JSON_UNESCAPED_UNICODE) ?>
    });
  }
});
</script>
</body>
</html>
