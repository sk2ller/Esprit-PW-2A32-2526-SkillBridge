<footer style="background: var(--bg-secondary); border-top: 1px solid var(--border); padding: 3rem 2rem; margin-top: 5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
  <div style="max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
    <div style="color:var(--text-secondary); font-weight:600; font-family:'Space Grotesk',sans-serif;">
      <img src="views/assets/images/logo.png" alt="Geeks Services" style="height: 30px; width: auto;">
    </div>
    <div>© <?= date('Y') ?> Geeks Services. Tous droits réservés.</div>
    <div style="display:flex; gap:1rem;">
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-twitter"></i></a>
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-linkedin"></i></a>
      <a href="#" style="color:var(--text-muted); text-decoration:none; transition:color 0.2s" onmouseover="this.style.color='var(--accent-purple-light)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fab fa-github"></i></a>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('click', function (event) {
  const trigger = event.target.closest('.js-swal-confirm');

  if (!trigger) {
    return;
  }

  event.preventDefault();

  Swal.fire({
    title: trigger.dataset.swalTitle || 'Confirmer l action',
    text: trigger.dataset.swalText || 'Voulez-vous continuer ?',
    icon: trigger.dataset.swalIcon || 'warning',
    showCancelButton: true,
    confirmButtonText: trigger.dataset.swalConfirm || 'Oui',
    cancelButtonText: trigger.dataset.swalCancel || 'Annuler',
    confirmButtonColor: '#7c3aed',
    cancelButtonColor: '#6b7280'
  }).then(function (result) {
    if (result.isConfirmed) {
      window.location.href = trigger.href;
    }
  });
});
</script>
</body>
</html>
