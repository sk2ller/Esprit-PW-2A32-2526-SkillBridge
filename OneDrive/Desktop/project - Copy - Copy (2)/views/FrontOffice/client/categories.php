<?php
$pageTitle = 'Catégories - SkillBridge';
include __DIR__ . '/../partials/navbar.php';

require_once __DIR__ . '/../../../models/Categorie.php';
require_once __DIR__ . '/../../../models/Service.php';

$categorieModel = new Categorie();
$serviceModel = new Service();

$allCategories = $categorieModel->getAll();
?>

<div style="margin-top: var(--nav-height); padding-top: 2rem;"></div>

<!-- PAGE HEADER -->
<div style="max-width:1400px; margin:0 auto; padding:2rem;">
  <h1 style="font-family:'Space Grotesk',sans-serif; font-size:2rem; font-weight:800; margin-bottom:0.5rem;">
    Explorez nos <span style="color:var(--accent-purple-light);">Catégories</span>
  </h1>
  <p style="color:var(--text-secondary); font-size:1rem; margin-bottom:2rem;">
    Découvrez les meilleurs freelancers dans chaque domaine
  </p>
</div>

<!-- CATEGORIES GRID -->
<div class="page-container">
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    
    <?php foreach ($allCategories as $category): 
      $categoryServices = $serviceModel->getByCategory($category['id_categorie']);
    ?>
    
    <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; transition: all 0.3s ease; cursor: pointer;" 
         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(124,58,237,0.15)'" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
      
      <!-- Category Header -->
      <div style="background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(59,130,246,0.1)); padding: 2rem 1.5rem; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">
          <i class="<?= htmlspecialchars($category['icone']) ?>" style="color: var(--accent-purple-light);"></i>
        </div>
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">
          <?= htmlspecialchars($category['nom_categorie']) ?>
        </h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.75rem;">
          <?= count($categoryServices) ?> service<?= count($categoryServices) > 1 ? 's' : '' ?>
        </p>
      </div>

      <!-- Services Preview -->
      <div style="padding: 1.5rem;">
        <?php if (!empty($categoryServices)): ?>
          <div style="margin-bottom: 1rem;">
            <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.75rem;">
              Services populaires
            </p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <?php foreach (array_slice($categoryServices, 0, 3) as $service): ?>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); color: var(--text-secondary); font-size: 0.875rem;">
                <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 6px; font-size: 0.7rem;"></i>
                <?= htmlspecialchars(substr($service['titre'], 0, 35)) ?>...
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <p style="color: var(--text-muted); font-size: 0.875rem; text-align: center; padding: 1rem 0;">
            Aucun service disponible
          </p>
        <?php endif; ?>

        <!-- View Category Button -->
        <a href="index.php?page=all_services&categorie=<?= $category['id_categorie'] ?>" 
           style="display: block; width: 100%; padding: 0.75rem; background: linear-gradient(135deg, var(--accent-purple-light), #7c3aed); color: white; border: none; border-radius: var(--radius); text-align: center; font-weight: 600; text-decoration: none; margin-top: 1rem; transition: all 0.3s ease; cursor: pointer;"
           onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 8px 16px rgba(124,58,237,0.3)'"
           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
          Voir tous les services
          <i class="fas fa-arrow-right" style="margin-left: 6px;"></i>
        </a>
      </div>
    </div>

    <?php endforeach; ?>

  </div>

  <!-- Services by Category Section -->
  <hr style="border: none; height: 1px; background: var(--border); margin: 3rem 0;">

  <?php foreach ($allCategories as $category): 
    $categoryServices = $serviceModel->getByCategory($category['id_categorie']);
    if (!empty($categoryServices)):
  ?>

  <!-- Category Services Section -->
  <div style="margin-bottom: 3rem;">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
      <i class="<?= htmlspecialchars($category['icone']) ?>" style="font-size: 2rem; color: var(--accent-purple-light);"></i>
      <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0;">
          <?= htmlspecialchars($category['nom_categorie']) ?>
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
          <?= count($categoryServices) ?> service<?= count($categoryServices) > 1 ? 's disponible' . 's' : ' disponible' ?>
        </p>
      </div>
    </div>

    <div class="services-grid">
      <?php foreach ($categoryServices as $service): ?>
      <div class="service-card">
        <div class="service-card-image" style="background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($service['titre']) % 4] ?>, var(--bg-secondary));">
          <i class="<?= htmlspecialchars($service['icone'] ?? 'fas fa-star') ?>" style="color: rgba(255,255,255,0.2); font-size:4rem; position:relative; z-index:1;"></i>
        </div>
        <div class="service-card-body">
          <span class="service-category-tag"><?= htmlspecialchars($service['nom_categorie']) ?></span>
          <h3 class="service-title"><?= htmlspecialchars($service['titre']) ?></h3>
          <p style="color:var(--text-muted); font-size:0.82rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
            <?= htmlspecialchars($service['description']) ?>
          </p>
        </div>
        <div class="service-card-footer">
          <div>
            <div class="service-price"><?= number_format($service['prix'], 2) ?> DT</div>
            <div class="service-delay"><i class="fas fa-clock"></i> <?= $service['delai_livraison'] ?> jours</div>
          </div>
          <a href="index.php?page=service_detail&id=<?= $service['id_service'] ?>" class="btn-sm btn-sm-purple">
            Voir <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php endif; endforeach; ?>

</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
