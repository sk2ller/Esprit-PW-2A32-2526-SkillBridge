
</div><!-- end admin-content -->
</main><!-- end admin-main -->
<iframe name="adminExportFrame" id="adminExportFrame" style="position:absolute; width:1px; height:1px; border:0; left:-9999px; top:-9999px;"></iframe>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
        title: 'Export lance',
        text: 'La preparation du document a commence sans quitter cette page.',
        timer: 1800,
        showConfirmButton: false
      });
    }
    return;
  }

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
