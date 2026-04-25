<?php
include __DIR__ . '/navbar.php';
$currentRole = $_SESSION['role'] ?? 'client';
?>

<?php if ($currentRole === 'freelancer'): ?>
<div class="freelancer-dashboard">
  <?php include __DIR__ . '/freelancer_sidebar.php'; ?>
  <div class="freelancer-main">
<?php else: ?>
<div class="page-container" style="padding-top:2rem;">
<?php endif; ?>

    <div class="freelancer-hero">
      <div>
        <h1>Chat en direct</h1>
        <p>Discutez en temps reel entre client et freelancer avec Socket.IO.</p>
      </div>
      <div class="chat-identity-pill">
        <i class="fas fa-user-circle"></i>
        <span><?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($role) ?>)</span>
      </div>
    </div>

    <div class="chat-shell">
      <aside class="chat-conversations">
        <div class="chat-card">
          <div class="chat-card-title">Profil rapide</div>
          <form method="POST" action="index.php?page=chat<?= isset($selectedConversation['id_conversation']) ? '&conversation_id=' . (int) $selectedConversation['id_conversation'] : '' ?><?= isset($selectedConversation['id_service']) ? '&service_id=' . (int) $selectedConversation['id_service'] : '' ?>">
            <input type="text" name="profile_name" class="form-control" value="<?= htmlspecialchars($displayName) ?>" placeholder="Votre nom">
            <button type="submit" class="btn-primary" style="width:100%; margin-top:.75rem;">Enregistrer le nom</button>
          </form>
        </div>

        <div class="chat-card" style="margin-top:1rem;">
          <div class="chat-card-title">Conversations</div>
          <?php if (empty($conversations)): ?>
            <div class="chat-empty-small">Aucune conversation pour le moment.</div>
          <?php else: ?>
            <div class="chat-conversation-list">
              <?php foreach ($conversations as $conversation): ?>
                <a href="index.php?page=chat&conversation_id=<?= (int) $conversation['id_conversation'] ?><?= $currentRole === 'client' ? '&service_id=' . (int) $conversation['id_service'] : '' ?>"
                   class="chat-conversation-item <?= !empty($selectedConversation) && (int) $selectedConversation['id_conversation'] === (int) $conversation['id_conversation'] ? 'active' : '' ?>">
                  <div class="chat-conversation-top">
                    <span><?= htmlspecialchars($conversation['titre']) ?></span>
                    <small>#<?= (int) $conversation['id_conversation'] ?></small>
                  </div>
                  <div class="chat-conversation-meta">
                    <?= htmlspecialchars($currentRole === 'freelancer' ? $conversation['client_name'] : $conversation['freelancer_name']) ?>
                  </div>
                  <p><?= htmlspecialchars(mb_strimwidth($conversation['last_message'] ?? 'Nouveau chat', 0, 60, '...')) ?></p>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </aside>

      <section class="chat-panel">
        <?php if (empty($selectedConversation)): ?>
          <div class="chat-empty">
            <i class="fas fa-comments"></i>
            <h3>Aucune conversation selectionnee</h3>
            <p>Ouvrez un service puis cliquez sur contacter pour commencer.</p>
          </div>
        <?php else: ?>
          <div class="chat-panel-header">
            <div>
              <div class="chat-panel-title"><?= htmlspecialchars($selectedConversation['titre'] ?? 'Conversation') ?></div>
              <div class="chat-panel-subtitle">
                <?= htmlspecialchars($currentRole === 'freelancer' ? $selectedConversation['client_name'] : $selectedConversation['freelancer_name']) ?>
              </div>
            </div>
            <div class="chat-status-live">
              <span class="chat-status-dot"></span>
              Connecte au salon
            </div>
          </div>

          <div id="chatMessages"
               class="chat-messages"
               data-conversation-id="<?= (int) $selectedConversation['id_conversation'] ?>"
               data-role="<?= htmlspecialchars($role) ?>"
               data-sender-name="<?= htmlspecialchars($displayName) ?>">
            <?php foreach ($messages as $message): ?>
              <div class="chat-bubble-row <?= $message['sender_role'] === $role ? 'is-mine' : '' ?>">
                <div class="chat-bubble">
                  <div class="chat-bubble-name"><?= htmlspecialchars($message['sender_name']) ?></div>
                  <div><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                  <small><?= htmlspecialchars($message['created_at']) ?></small>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <form id="chatForm" class="chat-form">
            <input type="hidden" name="conversation_id" value="<?= (int) $selectedConversation['id_conversation'] ?>">
            <textarea name="message" class="form-control" rows="2" placeholder="Ecrire un message..." required></textarea>
            <button type="submit" class="btn-primary">
              <i class="fas fa-paper-plane"></i> Envoyer
            </button>
          </form>
        <?php endif; ?>
      </section>
    </div>

<?php if ($currentRole === 'freelancer'): ?>
  </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script src="http://localhost:3000/socket.io/socket.io.js"></script>
<script src="views/assets/js/chat.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
