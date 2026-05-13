<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($reportTitle) ?></title>
<style>
  @page { margin: 16mm; }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f1f23;
    background: #ffffff;
  }
  .page {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 8px;
  }
  .report {
    border: 1px solid #decdb7;
    border-radius: 22px;
    overflow: hidden;
    background: #fffdfa;
  }
  .hero {
    padding: 24px 28px 20px;
    background: linear-gradient(135deg, #232326 0%, #2f2f34 100%);
    color: #fff;
    position: relative;
  }
  .hero::after {
    content: '';
    position: absolute;
    left: 28px;
    right: 28px;
    bottom: 0;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, #e07020, #f08a3b);
  }
  .brand-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 16px;
  }
  .brand {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: rgba(255,255,255,.78);
  }
  .meta-top {
    text-align: right;
    font-size: 12px;
    line-height: 1.6;
    color: rgba(255,255,255,.74);
  }
  .title {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 700;
    letter-spacing: -.02em;
  }
  .subtitle {
    margin: 0;
    max-width: 700px;
    font-size: 14px;
    line-height: 1.65;
    color: rgba(255,255,255,.82);
  }
  .summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    padding: 18px 22px 10px;
  }
  .summary-card {
    padding: 14px 14px 12px;
    border: 1px solid #ead7c0;
    border-radius: 16px;
    background: #fff8ef;
  }
  .summary-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #8d765a;
    margin-bottom: 8px;
  }
  .summary-value {
    font-size: 24px;
    font-weight: 700;
    color: #1f1f23;
    line-height: 1.25;
  }
  .body {
    padding: 8px 22px 22px;
  }
  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f1f23;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .section-title::before {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #e07020;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ead7c0;
    border-radius: 16px;
    overflow: hidden;
  }
  thead th {
    background: #faf1e4;
    color: #7f6a52;
    text-transform: uppercase;
    letter-spacing: .08em;
    font-size: 10px;
    text-align: left;
    padding: 13px 14px;
    border-bottom: 1px solid #e6d4bf;
  }
  tbody td {
    padding: 13px 14px;
    font-size: 13px;
    line-height: 1.55;
    border-bottom: 1px solid #f0e5d8;
  }
  tbody tr:nth-child(even) td {
    background: #fffaf4;
  }
  tbody tr:last-child td {
    border-bottom: none;
  }
  .empty {
    text-align: center;
    color: #8b8791;
    padding: 28px 16px;
  }
  .footer {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 0 22px 22px;
    font-size: 11px;
    color: #7e7982;
  }
  .footer-note {
    padding: 10px 12px;
    border: 1px solid #e3d3c0;
    background: #faf1e4;
    border-radius: 12px;
  }
  @media print {
    body { background: #fff; }
    .page { padding: 0; }
  }
</style>
</head>
<body>
  <div class="page">
    <section class="report">
      <header class="hero">
        <div class="brand-row">
          <div class="brand">SkillBridge Admin</div>
          <div class="meta-top">
            Rapport genere le <?= date('d/m/Y') ?><br>
            Heure : <?= date('H:i') ?>
          </div>
        </div>
        <h1 class="title"><?= htmlspecialchars($reportTitle) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($reportSubtitle) ?></p>
      </header>

      <div class="summary">
        <?php foreach ($reportMeta as $meta): ?>
        <div class="summary-card">
          <div class="summary-label"><?= htmlspecialchars($meta['label']) ?></div>
          <div class="summary-value"><?= htmlspecialchars((string) $meta['value']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="body">
        <h2 class="section-title">Synthese detaillee</h2>
        <table>
          <thead>
            <tr>
              <?php foreach ($reportTableHeaders as $header): ?>
              <th><?= htmlspecialchars($header) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($reportRows)): ?>
            <tr>
              <td colspan="<?= count($reportTableHeaders) ?>" class="empty">Aucune donnee a exporter.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($reportRows as $row): ?>
            <tr>
              <?php foreach ($row as $cell): ?>
              <td><?= htmlspecialchars((string) $cell) ?></td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <footer class="footer">
        <div class="footer-note">Rapport genere le <?= date('d/m/Y \a H:i') ?></div>
        <div class="footer-note">Enregistrez en PDF depuis la fenetre d impression.</div>
      </footer>
    </section>
  </div>
<script>
window.addEventListener('load', function () {
  window.print();
});
</script>
</body>
</html>
