
</div><!-- end admin-content -->
</main><!-- end admin-main -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.SkillBridgeAlerts = {
  toast(icon, title) {
    if (!window.Swal) return;
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 2800,
      timerProgressBar: true,
      background: '#fffaf4',
      color: '#1f1f23'
    });
  },
  dialog(icon, title, text) {
    if (!window.Swal) return;
    Swal.fire({
      icon,
      title,
      text,
      confirmButtonColor: '#e07020',
      background: '#fffaf4',
      color: '#1f1f23'
    });
  },
  bindConfirm(selector, config) {
    document.querySelectorAll(selector).forEach((link) => {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        if (!window.Swal) {
          window.location.href = this.href;
          return;
        }
        Swal.fire({
          title: config.title || 'Confirmer cette action ?',
          text: config.text || '',
          icon: config.icon || 'warning',
          showCancelButton: true,
          confirmButtonColor: config.confirmColor || '#e07020',
          cancelButtonColor: '#8b8791',
          confirmButtonText: config.confirmText || 'Continuer',
          cancelButtonText: 'Annuler',
          background: '#fffaf4',
          color: '#1f1f23'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = this.href;
          }
        });
      });
    });
  }
};
</script>
</body>
</html>
