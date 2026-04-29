<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Views/assets/css/skillbridge-front.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <div class="front-page">
        <?php if ($role === 'freelancer'): ?>
            <div class="dashboard-shell">
                <?php include __DIR__ . '/partials/freelancer_sidebar.php'; ?>
                <section class="section-surface">
        <?php else: ?>
            <section class="section-surface">
        <?php endif; ?>
                    <div class="section-header">
                        <div>
                            <h1 class="section-title">Chat <span>Direct</span></h1>
                            <p class="section-subtitle">Discutez en temps reel entre client et freelancer pour les services et les offres job.</p>
                        </div>
                        <div class="chat-identity-pill">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($role) ?>)</span>
                        </div>
                    </div>

                    <div class="chat-shell">
                        <aside class="chat-conversations">
                            <div class="chat-card">
                                <div class="chat-card-title">Conversations</div>
                                <?php if (empty($conversations)): ?>
                                    <div class="chat-empty-small">Aucune conversation pour le moment.</div>
                                <?php else: ?>
                                    <div class="chat-conversation-list">
                                        <?php foreach ($conversations as $conversation): ?>
                                            <a href="?action=chat&conversation_id=<?= (int)$conversation['id_conversation'] ?>"
                                               class="chat-conversation-item <?= !empty($selectedConversation) && (int)$selectedConversation['id_conversation'] === (int)$conversation['id_conversation'] ? 'active' : '' ?>">
                                                <div class="chat-conversation-top">
                                                    <span><?= htmlspecialchars($conversation['titre']) ?></span>
                                                    <small>#<?= (int)$conversation['id_conversation'] ?></small>
                                                </div>
                                                <div class="chat-conversation-meta">
                                                    <?= htmlspecialchars(($conversation['conversation_type'] ?? 'service') === 'job' ? 'Offre job' : 'Service') ?> |
                                                    <?= htmlspecialchars($role === 'freelancer'
                                                        ? trim($conversation['client_prenom'] . ' ' . $conversation['client_nom'])
                                                        : trim($conversation['freelancer_prenom'] . ' ' . $conversation['freelancer_nom'])) ?>
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
                                    <p>Depuis une page service ou une offre job, vous pouvez cliquer sur contacter pour demarrer un chat.</p>
                                </div>
                            <?php else: ?>
                                <div class="chat-panel-header">
                                    <div>
                                        <div class="chat-panel-title"><?= htmlspecialchars($selectedConversation['titre'] ?? 'Conversation') ?></div>
                                        <div class="chat-panel-subtitle">
                                            <?= htmlspecialchars(($selectedConversation['conversation_type'] ?? 'service') === 'job' ? 'Offre job' : 'Service') ?> |
                                            <?= htmlspecialchars($role === 'freelancer'
                                                ? trim($selectedConversation['client_prenom'] . ' ' . $selectedConversation['client_nom'])
                                                : trim($selectedConversation['freelancer_prenom'] . ' ' . $selectedConversation['freelancer_nom'])) ?>
                                        </div>
                                    </div>
                                    <div class="chat-status-live">
                                        <span class="chat-status-dot"></span>
                                        Connecte au salon
                                    </div>
                                </div>

                                <div id="chatMessages"
                                     class="chat-messages"
                                     data-conversation-id="<?= (int)$selectedConversation['id_conversation'] ?>"
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

                                <form id="chatForm" class="chat-form" method="post" action="?action=chat_send">
                                    <input type="hidden" name="conversation_id" value="<?= (int)$selectedConversation['id_conversation'] ?>">
                                    <textarea name="message" class="sb-textarea" rows="2" placeholder="Ecrire un message..." required></textarea>
                                    <button type="submit" class="sb-btn">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </form>
                            <?php endif; ?>
                        </section>
                    </div>
            </section>
        <?php if ($role === 'freelancer'): ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    <script src="Views/assets/js/chat.js"></script>
</body>
</html>
