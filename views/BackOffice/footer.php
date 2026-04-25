
</div><!-- end admin-content -->
</main><!-- end admin-main -->
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
    confirmButtonColor: '#2563eb',
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
