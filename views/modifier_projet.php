<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Modifier un projet</title>
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
            <a href="index.php?action=list">Tableau de bord</a>
            <a href="index.php?action=add">Ajouter un projet</a>
            <a class="is-active" href="index.php?action=edit&amp;id=<?php echo (int)$projet['id_projet']; ?>">Modifier le projet</a>
        </div>

        <div class="sidebar__footer">
            <div class="sidebar__footer-name">Edition projet</div>
            <div class="sidebar__footer-text">Mise a jour rapide en HTML4</div>
        </div>
    </div>

    <div class="main-panel">
        <div class="topbar">
            <div>
                <div class="topbar__eyebrow">Mise a jour</div>
                <h1 class="topbar__title">Modifier un projet</h1>
                <div class="topbar__subtitle">Ajustez le contenu, le budget et le statut du projet.</div>
            </div>
            <div class="topbar__actions">
                <a class="btn btn--ghost" href="index.php?action=list">Retour a la liste</a>
            </div>
        </div>

        <div class="form-layout">
            <div class="form-sidecard">
                <div class="form-sidecard__title">Projet courant</div>
                <div class="form-sidecard__text"><?php echo htmlspecialchars($projet['titre']); ?></div>
                <div class="form-sidecard__note">Le formulaire garde vos modifications si une validation echoue.</div>
            </div>

            <div class="form-card">
                <?php if (count($errors) > 0): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="index.php?action=edit&amp;id=<?php echo (int)$projet['id_projet']; ?>">
                    <div class="form-row">
                        <label for="titre">Titre</label>
                        <input type="text" name="titre" id="titre" value="<?php echo htmlspecialchars($projet['titre']); ?>">
                    </div>

                    <div class="form-row">
                        <label for="description">Description</label>
                        <textarea name="description" id="description"><?php echo htmlspecialchars($projet['description']); ?></textarea>
                    </div>

                    <div class="form-row form-row--two">
                        <div>
                            <label for="budget">Budget</label>
                            <input type="text" name="budget" id="budget" value="<?php echo htmlspecialchars((string)$projet['budget']); ?>">
                        </div>
                        <div>
                            <label for="date_creation">Date de creation</label>
                            <input type="text" name="date_creation" id="date_creation" placeholder="AAAA-MM-JJ" value="<?php echo htmlspecialchars($projet['date_creation']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="statut">Statut</label>
                        <select name="statut" id="statut">
                            <option value="en_cours"<?php echo ($projet['statut'] === 'en_cours') ? ' selected="selected"' : ''; ?>>En cours</option>
                            <option value="termine"<?php echo ($projet['statut'] === 'termine') ? ' selected="selected"' : ''; ?>>Termine</option>
                            <option value="en_attente"<?php echo ($projet['statut'] === 'en_attente') ? ' selected="selected"' : ''; ?>>En attente</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <input class="btn btn--primary" type="submit" value="Mettre a jour">
                        <a class="btn btn--ghost" href="index.php?action=list">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
