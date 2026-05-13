<?php
// Sidebar frontoffice — client (role=2) et freelancer (role=3)
$role    = (int)($_SESSION['user_role'] ?? 0);
$current = $_GET['action'] ?? 'home';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .sb-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1099; }
    .sb-overlay.active { display:block; }

    .front-sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 240px;
        height: 100vh;
        background: var(--charcoal);
        z-index: 1100;
        display: flex;
        flex-direction: column;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        box-shadow: 4px 0 24px rgba(0,0,0,0.25);
    }
    .front-sidebar.open { transform: translateX(0); }

    .sb-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sb-logo { height: 38px; width: auto; }
    .sb-close {
        background: none; border: none; color: rgba(255,255,255,0.5);
        font-size: 1.3rem; cursor: pointer; line-height: 1; padding: 0;
        transition: color 0.2s;
    }
    .sb-close:hover { color: #fff; }

    .sb-user {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .sb-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--amber); color: var(--charcoal);
        font-weight: 700; font-size: 0.85rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .sb-user-info { overflow: hidden; }
    .sb-user-name { color: #fff; font-weight: 600; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sb-user-role { color: rgba(255,255,255,0.4); font-size: 0.75rem; }

    .sb-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }
    .sb-section { padding: 0.5rem 1.5rem 0.25rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.3); font-weight: 600; }
    .sb-link {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.7rem 1.5rem;
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }
    .sb-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .sb-link.active { color: #fff; background: rgba(224,112,32,0.12); border-left-color: var(--amber); }
    .sb-link i { width: 16px; text-align: center; font-size: 0.9rem; }

    .sb-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.07);
    }
    .sb-logout {
        display: flex; align-items: center; gap: 0.75rem;
        color: rgba(255,255,255,0.45); text-decoration: none;
        font-size: 0.85rem; font-weight: 500;
        padding: 0.5rem 0;
        transition: color 0.2s;
    }
    .sb-logout:hover { color: #f08080; }

    /* Toggle button */
    .sb-toggle {
        background: none; border: none;
        color: rgba(255,255,255,0.75);
        font-size: 1.2rem; cursor: pointer;
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
        display: flex; align-items: center;
    }
    .sb-toggle:hover { background: rgba(255,255,255,0.08); color: #fff; }

    /* Push content when sidebar open on desktop */
    @media (min-width: 992px) {
        .front-sidebar { transform: translateX(0); }
        .sb-overlay { display: none !important; }
        .sb-close { display: none; }
        .sb-toggle { display: none; }
        body.has-sidebar .navbar-top .container { padding-left: 256px; }
        body.has-sidebar main,
        body.has-sidebar .projects-layout,
        body.has-sidebar .page-layout { margin-left: 240px; }
        body.has-sidebar .footer { margin-left: 240px; }
    }
</style>

<!-- Overlay mobile -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="front-sidebar" id="frontSidebar">
    <div class="sb-header">
        <img src="<?= BASE_URL ?>/Views/assets/img/logo1.png" alt="SkillBridge" class="sb-logo">
        <button class="sb-close" onclick="closeSidebar()">&#10005;</button>
    </div>

    <div class="sb-user">
        <div class="sb-avatar">
            <?= strtoupper(mb_substr($_SESSION['user_prenom']??'',0,1).mb_substr($_SESSION['user_nom']??'',0,1)) ?>
        </div>
        <div class="sb-user-info">
            <div class="sb-user-name"><?= htmlspecialchars(($_SESSION['user_prenom']??'').' '.($_SESSION['user_nom']??'')) ?></div>
            <div class="sb-user-role"><?= $role===3 ? 'Freelancer' : 'Client' ?></div>
        </div>
    </div>

    <nav class="sb-nav">
        <?php if ($role === 3): ?>
        <div class="sb-section">Menu</div>
        <a href="?action=projects"    class="sb-link <?= $current==='projects'    ?'active':'' ?>"><i class="fas fa-briefcase"></i> Projets</a>
        <a href="?action=profile"     class="sb-link <?= $current==='profile'     ?'active':'' ?>"><i class="fas fa-user"></i> Mon Profil</a>

        <?php elseif ($role === 2): ?>
        <div class="sb-section">Menu</div>
        <a href="?action=projects"           class="sb-link <?= ($current==='projects' && ($_GET['tab']??'')==='') ?'active':'' ?>"><i class="fas fa-search"></i> Parcourir les projets</a>
        <a href="?action=profile"            class="sb-link <?= $current==='profile'            ?'active':'' ?>"><i class="fas fa-user"></i> Mon Profil</a>
        <?php endif; ?>
    </nav>

    <div class="sb-footer">
        <a href="?action=logout" class="sb-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

<script>
function openSidebar()  { document.getElementById('frontSidebar').classList.add('open'); document.getElementById('sbOverlay').classList.add('active'); }
function closeSidebar() { document.getElementById('frontSidebar').classList.remove('open'); document.getElementById('sbOverlay').classList.remove('active'); }
</script>
