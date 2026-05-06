<?php
/**
 * Reusable pagination bar.
 *
 * Required variables (set before including this file):
 *   $currentPage  int  – active page number
 *   $totalPages   int  – total number of pages
 *
 * Optional:
 *   $totalItems   int    – total record count (shown as info label)
 *   $perPage      int    – items per page   (shown as info label)
 *
 * The component auto-builds the base URL from the current $_GET parameters,
 * replacing (or appending) the "p" parameter for each link.
 */

if (!isset($totalPages) || $totalPages <= 1) return;

$_paginParams = $_GET;
unset($_paginParams['p']);
$_paginBase = 'index.php?' . http_build_query($_paginParams);

$_window = 2; // page links on each side of current
?>

<style>
.pag-wrap {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}
.pag-info {
  font-size: .8rem;
  color: var(--text-muted, #8b8791);
}
.pag-bar {
  display: flex;
  align-items: center;
  gap: .25rem;
  flex-wrap: wrap;
}
.pag-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 34px;
  padding: 0 .45rem;
  border-radius: 8px;
  font-size: .82rem;
  font-weight: 600;
  text-decoration: none;
  color: var(--charcoal, #1f1f23);
  background: var(--paper, #fffaf4);
  border: 1px solid var(--border, #e4ddd2);
  transition: all .15s;
  cursor: pointer;
  white-space: nowrap;
}
.pag-btn:hover:not(.pag-disabled):not(.pag-active) {
  background: rgba(124,58,237,.08);
  border-color: rgba(124,58,237,.35);
  color: #7c3aed;
}
.pag-active {
  background: linear-gradient(135deg,#7c3aed,#a855f7) !important;
  color: #fff !important;
  border-color: transparent !important;
  box-shadow: 0 2px 8px rgba(124,58,237,.3);
}
.pag-disabled {
  opacity: .35;
  cursor: not-allowed;
  pointer-events: none;
}
.pag-ellipsis {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  color: var(--text-muted, #8b8791);
  font-size: .84rem;
  user-select: none;
}
</style>

<div class="pag-wrap">
  <?php if (isset($totalItems, $perPage)): ?>
  <div class="pag-info">
    Page <strong><?= $currentPage ?></strong> sur <strong><?= $totalPages ?></strong>
    &mdash; <?= $totalItems ?> résultat<?= $totalItems != 1 ? 's' : '' ?>
  </div>
  <?php else: ?>
  <div class="pag-info">Page <strong><?= $currentPage ?></strong> sur <strong><?= $totalPages ?></strong></div>
  <?php endif; ?>

  <nav class="pag-bar" aria-label="Pagination">

    <?php if ($currentPage > 1): ?>
    <a href="<?= $_paginBase ?>&amp;p=<?= $currentPage - 1 ?>" class="pag-btn pag-arrow" title="Page précédente">
      <i class="fas fa-chevron-left"></i>
    </a>
    <?php else: ?>
    <span class="pag-btn pag-arrow pag-disabled"><i class="fas fa-chevron-left"></i></span>
    <?php endif; ?>

    <?php
    $_from = max(1, $currentPage - $_window);
    $_to   = min($totalPages, $currentPage + $_window);

    if ($_from > 1):
        echo '<a href="' . $_paginBase . '&p=1" class="pag-btn">1</a>';
        if ($_from > 2) echo '<span class="pag-ellipsis">…</span>';
    endif;

    for ($_i = $_from; $_i <= $_to; $_i++):
        $cls = $_i === $currentPage ? 'pag-btn pag-active' : 'pag-btn';
        echo '<a href="' . $_paginBase . '&p=' . $_i . '" class="' . $cls . '">' . $_i . '</a>';
    endfor;

    if ($_to < $totalPages):
        if ($_to < $totalPages - 1) echo '<span class="pag-ellipsis">…</span>';
        echo '<a href="' . $_paginBase . '&p=' . $totalPages . '" class="pag-btn">' . $totalPages . '</a>';
    endif;
    ?>

    <?php if ($currentPage < $totalPages): ?>
    <a href="<?= $_paginBase ?>&amp;p=<?= $currentPage + 1 ?>" class="pag-btn pag-arrow" title="Page suivante">
      <i class="fas fa-chevron-right"></i>
    </a>
    <?php else: ?>
    <span class="pag-btn pag-arrow pag-disabled"><i class="fas fa-chevron-right"></i></span>
    <?php endif; ?>

  </nav>
</div>
