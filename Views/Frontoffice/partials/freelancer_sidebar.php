<?php $sidebarAction = $_GET['action'] ?? ''; ?>
<aside class="freelancer-sidebar">
    <div class="freelancer-sidebar-card">
        <div class="freelancer-sidebar-kicker">Dashboard Freelancer</div>
        <div class="freelancer-sidebar-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Freelancer') ?></div>
        <div class="freelancer-sidebar-copy">Gerez vos services, suivez vos publications et gardez une navigation claire comme un vrai dashboard.</div>
    </div>

    <nav class="freelancer-nav">
        <a class="freelancer-link <?= $sidebarAction === 'my_services' ? 'active' : '' ?>" href="?action=my_services">
            <i class="fas fa-grid-2"></i>
            <span>Mes services</span>
        </a>
        <a class="freelancer-link <?= $sidebarAction === 'service_create' ? 'active' : '' ?>" href="?action=service_create">
            <i class="fas fa-plus"></i>
            <span>Ajouter service</span>
        </a>
        <a class="freelancer-link <?= in_array($sidebarAction, ['job_offers', 'job_offer_detail'], true) ? 'active' : '' ?>" href="?action=job_offers">
            <i class="fas fa-bullhorn"></i>
            <span>Offres job</span>
        </a>
        <a class="freelancer-link <?= $sidebarAction === 'my_applications' ? 'active' : '' ?>" href="?action=my_applications">
            <i class="fas fa-paper-plane"></i>
            <span>Mes candidatures</span>
        </a>
        <a class="freelancer-link <?= $sidebarAction === 'chat' ? 'active' : '' ?>" href="?action=chat">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a class="freelancer-link" href="?action=services">
            <i class="fas fa-store"></i>
            <span>Voir marketplace</span>
        </a>
        <a class="freelancer-link" href="?action=profile">
            <i class="fas fa-user-gear"></i>
            <span>Mon profil</span>
        </a>
    </nav>
</aside>
