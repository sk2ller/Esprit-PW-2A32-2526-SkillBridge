<?php
$pageTitle = htmlspecialchars($produit->getNom()) . ' - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="page-top">
<div class="detail-container">

  <div style="margin-bottom:1.5rem;">
    <a href="index.php?page=all_produits" style="color:var(--accent-purple-light); text-decoration:none; font-size:0.875rem;">
      <i class="fas fa-arrow-left"></i> Retour aux produits
    </a>
  </div>

  <div class="detail-grid">
    <div class="detail-image" style="background: var(--bg-secondary); overflow: hidden; position: relative;">
      <?php if ($produit->getImage()): ?>
        <img src="<?= htmlspecialchars($produit->getImage()) ?>" alt="<?= htmlspecialchars($produit->getNom()) ?>" style="width: 100%; height: 100%; object-fit: cover;">
      <?php else: ?>
        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, <?= ['#1a0533','#0a2240','#002a1f','#1a1000'][crc32($produit->getNom()) % 4] ?>, var(--bg-secondary)); display: flex; align-items: center; justify-content: center;">
          <i class="fas fa-box" style="color: rgba(255,255,255,0.3); font-size:6rem; position:relative; z-index:1;"></i>
        </div>
      <?php endif; ?>
    </div>

    <div class="detail-info">
      <span class="product-category-tag" style="font-size:0.85rem; padding:5px 14px;">
        <?= htmlspecialchars($produit->getNomCategorie()) ?>
      </span>

      <h1><?= htmlspecialchars($produit->getNom()) ?></h1>

      <div class="detail-price"><?= number_format($produit->getPrix(), 2) ?> DT</div>

      <div class="detail-meta">
        <div class="detail-meta-item">
          <i class="fas fa-cubes"></i>
          <span>Stock : <strong><?= $produit->getQuantite() ?></strong> unités disponibles</span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-tags"></i>
          <span>Catégorie : <strong><?= htmlspecialchars($produit->getNomCategorie()) ?></strong></span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-circle-check"></i>
          <span>Statut : 
            <?php
            $bmap = ['disponible'=>'badge-disponible','rupture'=>'badge-rupture','en_attente'=>'badge-pending'];
            $lmap = ['disponible'=>'✓ Disponible','rupture'=>'✗ Rupture','en_attente'=>'⏳ En attente'];
            ?>
            <span class="badge <?= $bmap[$produit->getStatut()] ?>"><?= $lmap[$produit->getStatut()] ?></span>
          </span>
        </div>
        <div class="detail-meta-item">
          <i class="fas fa-calendar"></i>
          <span>Publié le : <?= date('d/m/Y', strtotime($produit->getCreatedAt())) ?></span>
        </div>
      </div>

      <?php if ($produit->getStatut() === 'disponible'): ?>
      <form method="POST" action="index.php?page=panier_add" style="margin-top: 1rem; display: flex; gap: 1rem;">
        <input type="hidden" name="id_produit" value="<?= $produit->getId() ?>">
        <input type="hidden" name="redirect" value="index.php?page=produit_detail&id=<?= $produit->getId() ?>">
        <div style="display:flex; align-items:center; background:var(--bg-primary); border:1px solid var(--border); border-radius:8px; overflow:hidden;">
          <input type="number" name="quantite" value="1" min="1" max="<?= $produit->getQuantite() ?>" style="width: 60px; padding: 12px; background: transparent; border: none; color: var(--text-primary); text-align: center; outline: none; font-weight: 600;">
        </div>
        <button type="submit" class="btn-primary" style="flex:1; justify-content:center; cursor: pointer;">
          <i class="fas fa-cart-plus"></i> Ajouter au panier
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-description">
    <h3 style="font-family:'Space Grotesk',sans-serif; margin-bottom:1rem;">Description</h3>
    <p><?= nl2br(htmlspecialchars($produit->getDescription())) ?></p>
  </div>

  <!-- ====== CHAT WITH FREELANCER ====== -->
  <?php 
  $clientEmail = $_SESSION['user']['email'] ?? '';
  $chatVendorEmail = $vendorEmail ?? '';
  $chatProduitId = $produit->getId();
  ?>
  <?php if (!empty($clientEmail) && !empty($chatVendorEmail) && $clientEmail !== $chatVendorEmail): ?>
  <div id="chatSection" style="margin-top:2rem; background:var(--bg-card, #1a1a2e); border:1px solid var(--border, #2a2a3e); border-radius:16px; overflow:hidden;">
    <!-- Chat Header -->
    <div style="padding:1rem 1.5rem; background:linear-gradient(135deg, rgba(139,92,246,0.15), rgba(59,130,246,0.1)); border-bottom:1px solid var(--border, #2a2a3e); display:flex; align-items:center; justify-content:space-between; cursor:pointer;" onclick="toggleChat()">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#3b82f6);display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-comments" style="color:white; font-size:0.9rem;"></i>
        </div>
        <div>
          <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary, #f3f4f6);">💬 Contacter le Freelancer</div>
          <div style="font-size:0.75rem; color:var(--text-muted, #9ca3af);">Discussion à propos de "<?= htmlspecialchars($produit->getNom()) ?>"</div>
        </div>
      </div>
      <i id="chatToggleIcon" class="fas fa-chevron-down" style="color:var(--text-muted); transition:transform 0.3s;"></i>
    </div>

    <!-- Chat Body (collapsible) -->
    <div id="chatBody" style="display:none;">
      <div id="chatMessages" style="height:300px; overflow-y:auto; padding:1rem 1.5rem; display:flex; flex-direction:column; gap:8px;">
        <div style="text-align:center; color:var(--text-muted, #9ca3af); font-size:0.8rem; padding:2rem 0;">
          <i class="fas fa-lock" style="margin-bottom:6px; font-size:1.2rem; opacity:0.5;"></i><br>
          Chargement des messages...
        </div>
      </div>
      <!-- Chat Input -->
      <div style="padding:1rem 1.5rem; border-top:1px solid var(--border, #2a2a3e); display:flex; gap:10px;">
        <input type="text" id="chatInput" placeholder="Écrire un message..." 
          style="flex:1; padding:10px 16px; border-radius:10px; border:1px solid var(--border, #2a2a3e); background:var(--bg-primary, #0f0f1a); color:var(--text-primary, #f3f4f6); font-size:0.9rem; outline:none;"
          onkeydown="if(event.key==='Enter')sendChatMsg()">
        <button onclick="sendChatMsg()" style="padding:10px 20px; border-radius:10px; border:none; background:linear-gradient(135deg,#8b5cf6,#6366f1); color:white; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s;">
          <i class="fas fa-paper-plane"></i> Envoyer
        </button>
      </div>
    </div>
  </div>

  <script>
  const CHAT_USER = '<?= htmlspecialchars($clientEmail) ?>';
  const CHAT_VENDOR = '<?= htmlspecialchars($chatVendorEmail) ?>';
  const CHAT_PRODUIT = <?= (int)$chatProduitId ?>;
  let chatOpen = false;
  let lastMsgId = 0;
  let chatPollTimer = null;

  function toggleChat() {
    chatOpen = !chatOpen;
    document.getElementById('chatBody').style.display = chatOpen ? 'block' : 'none';
    document.getElementById('chatToggleIcon').style.transform = chatOpen ? 'rotate(180deg)' : '';
    if (chatOpen) {
      fetchMessages();
      chatPollTimer = setInterval(fetchMessages, 3000);
    } else {
      clearInterval(chatPollTimer);
    }
  }

  function fetchMessages() {
    fetch(`chat_api.php?action=fetch&user1=${encodeURIComponent(CHAT_USER)}&user2=${encodeURIComponent(CHAT_VENDOR)}&id_produit=${CHAT_PRODUIT}&after=${lastMsgId}`)
      .then(r => r.json())
      .then(data => {
        if (data.success && data.messages.length > 0) {
          const container = document.getElementById('chatMessages');
          // First load — clear placeholder
          if (lastMsgId === 0) container.innerHTML = '';
          
          data.messages.forEach(msg => {
            const isMine = msg.sender_email === CHAT_USER;
            const div = document.createElement('div');
            div.style.cssText = `max-width:75%; padding:10px 14px; border-radius:12px; font-size:0.88rem; line-height:1.4; word-wrap:break-word; ${
              isMine 
                ? 'align-self:flex-end; background:linear-gradient(135deg,#8b5cf6,#6366f1); color:white; border-bottom-right-radius:4px;' 
                : 'align-self:flex-start; background:var(--bg-secondary, #1e1e32); color:var(--text-primary, #f3f4f6); border:1px solid var(--border, #2a2a3e); border-bottom-left-radius:4px;'
            }`;
            const time = new Date(msg.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
            div.innerHTML = `${msg.message}<div style="font-size:0.65rem; opacity:0.6; margin-top:4px; text-align:${isMine ? 'right' : 'left'};">${time}</div>`;
            container.appendChild(div);
            lastMsgId = Math.max(lastMsgId, parseInt(msg.id));
          });
          container.scrollTop = container.scrollHeight;
        } else if (data.success && data.messages.length === 0 && lastMsgId === 0) {
          document.getElementById('chatMessages').innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:0.8rem; padding:3rem 0;"><i class="fas fa-comment-dots" style="font-size:1.5rem; opacity:0.3; margin-bottom:8px; display:block;"></i>Aucun message pour l\'instant.<br>Envoyez le premier message !</div>';
        }
      })
      .catch(e => console.error('Chat fetch error:', e));
  }

  function sendChatMsg() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('sender_email', CHAT_USER);
    formData.append('receiver_email', CHAT_VENDOR);
    formData.append('id_produit', CHAT_PRODUIT);
    formData.append('message', msg);

    fetch('chat_api.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          input.value = '';
          fetchMessages();
        }
      })
      .catch(e => console.error('Chat send error:', e));
  }
  </script>
  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
