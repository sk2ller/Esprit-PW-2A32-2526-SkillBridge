<?php
$pageTitle = htmlspecialchars($offre['titre']) . ' - SkillBridge Jobs';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="container" style="padding-top:2rem;">
  
  <!-- Breadcrumb -->
  <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:2rem; display:flex; align-items:center; gap:8px;">
    <a href="index.php?page=offres" style="color:var(--accent-purple-light); text-decoration:none;">Offres</a>
    <span>›</span>
    <span><?= htmlspecialchars(substr($offre['titre'], 0, 40)) ?>...</span>
  </div>

  <div style="display:grid; grid-template-columns:1fr 350px; gap:2rem; align-items:start;">
    <!-- Main Content -->
    <div>
      <!-- Header -->
      <div style="margin-bottom:2rem;">
        <div style="display:flex; align-items:start; gap:1rem; margin-bottom:1rem;">
          <div style="background: linear-gradient(135deg, #1a0533, #0a2240); border-radius:12px; width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-briefcase" style="font-size:2.5rem; color:rgba(255,255,255,0.3);"></i>
          </div>
          <div style="flex:1;">
            <h1 style="font-family:'Space Grotesk',sans-serif; font-size:1.8rem; font-weight:800; margin:0 0 0.5rem 0;">
              <?= htmlspecialchars($offre['titre']) ?>
            </h1>
            <div style="color:var(--text-muted); font-size:0.9rem; display:flex; gap:1rem; flex-wrap:wrap;">
              <span><i class="fas fa-user"></i> <?= htmlspecialchars($offre['nom_client'] ?? 'Client Anonyme') ?></span>
              <span><i class="fas fa-calendar"></i> Publié le <?= date('d/m/Y', strtotime($offre['created_at'])) ?></span>
            </div>
          </div>
        </div>

        <!-- Status Badge -->
        <?php 
        $statusColors = [
          'actif' => ['bg' => 'rgba(16,185,129,0.15)', 'color' => 'var(--accent-green)', 'text' => '✓ Actif'],
          'en_attente' => ['bg' => 'rgba(245,158,11,0.15)', 'color' => 'var(--accent-orange)', 'text' => '⏳ Approbation en cours'],
          'suspendu' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => 'var(--danger)', 'text' => '⛔ Suspendu']
        ];
        $status = $statusColors[$offre['statut']] ?? $statusColors['en_attente'];
        ?>
        <span style="background:<?= $status['bg'] ?>; color:<?= $status['color'] ?>; padding:8px 16px; border-radius:20px; font-size:0.85rem; font-weight:600; display:inline-block;">
          <?= $status['text'] ?>
        </span>
      </div>

      <!-- Description -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-bottom:2rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Description de l'offre</h2>
        <p style="color:var(--text-secondary); line-height:1.8; white-space:pre-wrap;">
          <?= htmlspecialchars($offre['description']) ?>
        </p>
      </div>

      <!-- Compétences requises -->
      <?php if (!empty($offre['competences_requises'])): ?>
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-bottom:2rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Compétences requises</h2>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
          <?php 
          $competences = array_filter(array_map('trim', explode(',', $offre['competences_requises'])));
          foreach ($competences as $comp):
          ?>
          <span style="background:var(--accent-glow); color:var(--accent-light); padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:600;">
            <?= htmlspecialchars($comp) ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Additional Info -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem;">
        <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Détails supplémentaires</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:0.25rem;">Niveau requis</div>
            <div style="font-weight:700; font-size:1rem;">
              <i class="fas fa-<?php
                $icons = ['debutant' => 'star-half-alt', 'intermediaire' => 'star', 'expert' => 'star'];
              ?> <?= $icons[$offre['niveau_requis']] ?? 'star' ?>"></i>
              <?= ucfirst($offre['niveau_requis']) ?>
            </div>
          </div>
          <div>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:0.25rem;">Durée de publication</div>
            <div style="font-weight:700; font-size:1rem;">
              <?= $offre['delai_publication'] ?? 30 ?> jours
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Sidebar -->
    <div style="position:sticky; top:calc(var(--nav-height) + 1rem);">
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-bottom:1rem;">
        
        <!-- Budget -->
        <div style="margin-bottom:1.5rem;">
          <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:0.5rem;">Budget estimé</div>
          <div style="font-size:2rem; font-weight:800; color:var(--accent-green);">
            <?= number_format($offre['budget'], 2) ?> DT
          </div>
        </div>

        <!-- Client Info -->
        <div style="background:var(--bg-light); border-radius:8px; padding:1rem; margin-bottom:1.5rem;">
          <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.5rem;">Publié par</div>
          <div style="font-weight:700; font-size:1rem; margin-bottom:0.5rem;">
            <?= htmlspecialchars($offre['nom_client'] ?? 'Client') ?>
          </div>
          <?php if (!empty($offre['email_client'])): ?>
          <div style="font-size:0.8rem; color:var(--accent-light); word-break:break-all;">
            <?= htmlspecialchars($offre['email_client']) ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Benefits -->
        <div style="border-top:1px solid var(--border); padding-top:1.2rem; margin-bottom:1.5rem;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-check-circle" style="color:var(--accent-green)"></i> Offre vérifiée
          </div>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-shield-alt" style="color:var(--accent-purple-light)"></i> Communications sécurisées
          </div>
          <div style="display:flex; align-items:center; gap:8px; color:var(--text-secondary); font-size:0.875rem;">
            <i class="fas fa-award" style="color:var(--accent-orange)"></i> Paiement garanti
          </div>
        </div>

        <!-- CTA Buttons -->
        <a href="mailto:<?= htmlspecialchars($offre['email_client'] ?? '') ?>" class="btn-primary" style="width:100%; justify-content:center; margin-bottom:0.75rem; text-decoration:none; display:inline-flex;">
          <i class="fas fa-envelope"></i> Contacter le client
        </a>
        
        <button onclick="alert('Fonctionnalité de candidature bientôt disponible')" class="btn-outline" style="width:100%; justify-content:center;">
          <i class="fas fa-hand-paper"></i> Candidater
        </button>
      </div>

      <!-- Share -->
      <div style="background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.5rem;">Partager cette offre</div>
        <div style="display:flex; gap:0.5rem; justify-content:center;">
          <button style="width:40px; height:40px; border-radius:8px; border:none; background:var(--bg-light); cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fab fa-facebook" style="color:var(--accent-light);"></i>
          </button>
          <button style="width:40px; height:40px; border-radius:8px; border:none; background:var(--bg-light); cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fab fa-twitter" style="color:var(--accent-light);"></i>
          </button>
          <button style="width:40px; height:40px; border-radius:8px; border:none; background:var(--bg-light); cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fab fa-linkedin" style="color:var(--accent-light);"></i>
          </button>
        </div>
      </div>

    </div>
  </div>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
