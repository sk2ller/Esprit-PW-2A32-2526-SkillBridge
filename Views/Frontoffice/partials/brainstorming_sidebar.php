<?php $sidebarAction = $_GET['action'] ?? ''; ?>
<aside class="brainstorming-sidebar">
    <div class="brainstorming-sidebar-head">
        <div class="brainstorming-sidebar-icon"><i class="fas fa-lightbulb"></i></div>
        <div>
            <div class="brainstorming-sidebar-title">Idees</div>
            <div class="brainstorming-sidebar-subtitle">Brainstorming</div>
        </div>
    </div>

    <nav class="brainstorming-nav">
        <a class="brainstorming-nav-link <?= in_array($sidebarAction, ['brainstorming_list', 'list_idees'], true) ? 'active' : '' ?>" href="?action=brainstorming_list">
            <i class="fas fa-list"></i>
            <span>Brainstormings</span>
        </a>
        <a class="brainstorming-nav-link <?= $sidebarAction === 'brainstorming_add' ? 'active' : '' ?>" href="?action=brainstorming_add">
            <i class="fas fa-plus"></i>
            <span>Ajouter brainstorming</span>
        </a>
        <a class="brainstorming-nav-link <?= in_array($sidebarAction, ['all_idees', 'edit_idee'], true) ? 'active' : '' ?>" href="?action=all_idees">
            <i class="fas fa-comments"></i>
            <span>Idees</span>
        </a>
        <a class="brainstorming-nav-link <?= $sidebarAction === 'add_idee' ? 'active' : '' ?>" href="?action=add_idee">
            <i class="fas fa-circle-plus"></i>
            <span>Ajouter idee</span>
        </a>
    </nav>
</aside>
