<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Tableau de bord projets</title>
    <link rel="stylesheet" type="text/css" href="views/assets/css/projet.css">
</head>
<body>
<div class="app-shell">
    <div class="sidebar">
        <div class="sidebar__brand">
            <span class="sidebar__brand-mark">P</span>
            <span class="sidebar__brand-text">ProjetHub</span>
        </div>

        <div class="sidebar__section-title">Navigation</div>
        <div class="sidebar__nav">
            <a class="is-active" href="index.php?action=list">Tableau de bord</a>
            <a href="index.php?action=add">Ajouter un projet</a>
        </div>

        <div class="sidebar__footer">
            <div class="sidebar__footer-name">Gestion simple</div>
            <div class="sidebar__footer-text">MVC HTML4 avec PHP et SQLite</div>
        </div>
    </div>

    <div class="main-panel">
        <div class="topbar">
            <div>
                <div class="topbar__eyebrow">Frontend / Backend</div>
                <h1 class="topbar__title">Mes Projets</h1>
                <div class="topbar__subtitle">Gerez, recherchez et suivez vos projets en un seul endroit.</div>
            </div>
            <div class="topbar__actions">
                <form class="search-form" method="get" action="index.php">
                    <input type="hidden" name="action" value="list">
                    <input type="text" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" placeholder="Rechercher un projet">
                    <input type="submit" class="btn btn--soft" value="Rechercher">
                </form>
                <a class="btn btn--primary" href="index.php?action=add">+ Nouveau projet</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card__icon">#</div>
                <div class="stat-card__value"><?php echo (int)$stats['total_projets']; ?></div>
                <div class="stat-card__label">Projets au total</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">+</div>
                <div class="stat-card__value"><?php echo (int)$stats['projets_en_cours']; ?></div>
                <div class="stat-card__label">En cours</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">=</div>
                <div class="stat-card__value"><?php echo (int)$stats['projets_termines']; ?></div>
                <div class="stat-card__label">Terminés</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">$</div>
                <div class="stat-card__value"><?php echo number_format((float)$stats['budget_total'], 0, ',', ' '); ?></div>
                <div class="stat-card__label">Budget total</div>
            </div>
        </div>

        <div class="panel-header">
            <div>
                <h2 class="panel-title">Liste des projets</h2>
                <div class="panel-subtitle">Vue inspirée du dashboard fourni, adaptée a votre CRUD MVC.</div>
            </div>
            <div class="panel-counter"><?php echo count($projets); ?> resultat(s)</div>
        </div>

        <div class="project-grid">
            <?php if (count($projets) === 0): ?>
            <div class="empty-state">
                <div class="empty-state__title">Aucun projet trouve.</div>
                <div class="empty-state__text">Ajoutez votre premier projet pour remplir le tableau de bord.</div>
                <a class="btn btn--primary" href="index.php?action=add">Creer un projet</a>
            </div>
            <?php else: ?>
                <?php
                $statusLabels = array(
                    'en_cours' => 'En cours',
                    'termine' => 'Termine',
                    'en_attente' => 'En attente',
                );
                $statusClasses = array(
                    'en_cours' => 'badge badge--progress',
                    'termine' => 'badge badge--done',
                    'en_attente' => 'badge badge--waiting',
                );
                ?>
                <?php foreach ($projets as $projet): ?>
                <?php
                $statut = isset($projet['statut']) ? $projet['statut'] : 'en_cours';
                $badgeClass = isset($statusClasses[$statut]) ? $statusClasses[$statut] : 'badge badge--progress';
                $badgeLabel = isset($statusLabels[$statut]) ? $statusLabels[$statut] : 'En cours';
                $dateCreation = $projet['date_creation'];
                $dateObject = DateTime::createFromFormat('Y-m-d', $dateCreation);
                if ($dateObject) {
                    $dateCreation = $dateObject->format('d/m/Y');
                }
                $titreInitial = strtoupper(substr(trim($projet['titre']), 0, 1));
                if ($titreInitial === '') {
                    $titreInitial = 'P';
                }
                ?>
                <div class="project-card">
                    <div class="project-card__head">
                        <div class="project-card__icon"><?php echo htmlspecialchars($titreInitial); ?></div>
                        <span class="<?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                    </div>
                    <h3 class="project-card__title"><?php echo htmlspecialchars($projet['titre']); ?></h3>
                    <div class="project-card__description"><?php echo nl2br(htmlspecialchars($projet['description'])); ?></div>
                    <div class="project-card__meta">
                        <div>
                            <span class="project-card__label">Budget</span>
                            <span class="project-card__value"><?php echo number_format((float)$projet['budget'], 0, ',', ' '); ?> DH</span>
                        </div>
                        <div>
                            <span class="project-card__label">Creation</span>
                            <span class="project-card__value"><?php echo htmlspecialchars($dateCreation); ?></span>
                        </div>
                    </div>
                    <div class="project-card__actions">
                        <a class="btn btn--ghost" href="index.php?action=edit&amp;id=<?php echo (int)$projet['id_projet']; ?>">Voir / Modifier</a>
                        <a class="btn btn--danger" href="index.php?action=delete&amp;id=<?php echo (int)$projet['id_projet']; ?>" onclick="return confirm('Supprimer ce projet ?');">Supprimer</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
