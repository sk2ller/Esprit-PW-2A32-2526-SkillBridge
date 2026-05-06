
<footer style="background: var(--bg-secondary); border-top: 1px solid var(--border); padding: 3rem 2rem; margin-top: 5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
  <div style="max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div style="color:var(--text-secondary); font-weight:600; font-family:'Playfair Display',serif;">
      <i class="fas fa-bolt" style="color:var(--accent-purple-light)"></i> SkillBridge Services
    </div>
    <div>© <?= date('Y') ?> SkillBridge Services. Tous droits réservés.</div>
    <div style="display:flex; gap:1rem;">
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-twitter"></i></a>
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-linkedin"></i></a>
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-github"></i></a>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
