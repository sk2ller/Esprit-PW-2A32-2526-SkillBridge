<?php
$currentAction = $_GET['action'] ?? 'home';
$userRole = (int)($_SESSION['user_role'] ?? 0);
$isLoggedIn = isset($_SESSION['user_id']) && $userRole > 0;
$userPrenom = trim((string)($_SESSION['user_prenom'] ?? ''));
$userNom = trim((string)($_SESSION['user_nom'] ?? ''));
$userDisplayName = trim($userPrenom . ' ' . $userNom);

if ($userDisplayName === '') {
    $userDisplayName = $userPrenom !== '' ? $userPrenom : 'My Account';
}
?>
<nav class="front-navbar">
    <div class="wrap">
        <a class="front-brand" href="?action=home">
            <img src="/Views/assets/img/logo1.png" alt="SkillBridge">
        </a>

        <ul class="front-nav-links">
            <li><a class="front-nav-link <?= $currentAction === 'home' ? 'active' : '' ?>" href="?action=home"><i class="fas fa-house"></i> Home</a></li>
            <?php if ($isLoggedIn && in_array($userRole, [2, 3], true)): ?>
                <li><a class="front-nav-link <?= in_array($currentAction, ['services', 'service_detail'], true) ? 'active' : '' ?>" href="?action=services"><i class="fas fa-briefcase"></i> Services</a></li>
            <?php endif; ?>
            <?php if ($userRole === 2): ?>
                <li><a class="front-nav-link <?= $currentAction === 'freelancers' ? 'active' : '' ?>" href="?action=freelancers"><i class="fas fa-user-group"></i> Freelancers</a></li>
            <?php endif; ?>
            <?php if ($userRole === 3): ?>
                <li><a class="front-nav-link <?= in_array($currentAction, ['job_offers', 'job_offer_detail', 'my_applications'], true) ? 'active' : '' ?>" href="?action=job_offers"><i class="fas fa-bullhorn"></i> Offres Job</a></li>
            <?php endif; ?>
            <?php if ($userRole === 2): ?>
                <li><a class="front-nav-link <?= in_array($currentAction, ['my_job_offers', 'job_offer_create', 'job_offer_edit'], true) ? 'active' : '' ?>" href="?action=my_job_offers"><i class="fas fa-bullhorn"></i> Mes Offres Job</a></li>
            <?php endif; ?>
            <?php if ($userRole === 3): ?>
                <li><a class="front-nav-link <?= in_array($currentAction, ['my_services', 'service_create', 'service_edit'], true) ? 'active' : '' ?>" href="?action=my_services"><i class="fas fa-layer-group"></i> Mes Services</a></li>
            <?php endif; ?>
            <?php if (in_array($userRole, [2, 3], true)): ?>
                <li><a class="front-nav-link <?= $currentAction === 'chat' ? 'active' : '' ?>" href="?action=chat"><i class="fas fa-comments"></i> Messages</a></li>
            <?php endif; ?>
            <?php if ($userRole === 1): ?>
                <li><a class="front-nav-link <?= in_array($currentAction, ['statistics', 'userlist'], true) ? 'active' : '' ?>" href="?action=statistics"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <?php endif; ?>
        </ul>

        <div class="front-nav-actions">
            <?php if ($isLoggedIn): ?>
                <details class="account-switcher">
                    <summary class="account-switcher-trigger">
                        <span class="account-switcher-title">Account</span>
                        <span class="account-switcher-current"><?= htmlspecialchars($userDisplayName) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>

                    <div class="account-switcher-menu">
                        <div class="account-switcher-label">Account</div>
                        <a class="account-item <?= $currentAction === 'profile' ? 'active' : '' ?>" href="?action=profile">
                            <span class="account-item-main">
                                <i class="fas fa-id-badge"></i>
                                <span>Profile</span>
                            </span>
                            <i class="fas fa-chevron-right account-item-arrow"></i>
                        </a>
                        <?php if ($userRole === 3): ?>
                            <a class="account-item <?= $currentAction === 'myrating' ? 'active' : '' ?>" href="?action=myrating">
                                <span class="account-item-main">
                                    <i class="fas fa-star"></i>
                                    <span>My Rating</span>
                                </span>
                                <i class="fas fa-chevron-right account-item-arrow"></i>
                            </a>
                        <?php endif; ?>
                        <a class="account-item" href="?action=logout">
                            <span class="account-item-main">
                                <i class="fas fa-right-from-bracket"></i>
                                <span>Logout</span>
                            </span>
                            <i class="fas fa-chevron-right account-item-arrow"></i>
                        </a>
                    </div>
                </details>
            <?php else: ?>
                <a class="sb-btn-soft" href="?action=login"><i class="fas fa-right-to-bracket"></i> Login</a>
                <a class="sb-btn" href="?action=register"><i class="fas fa-user-plus"></i> Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
