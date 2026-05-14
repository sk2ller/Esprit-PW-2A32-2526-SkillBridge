<?php
$pageTitle = 'Messages - SkillBridge';
include __DIR__ . '/../partials/navbar.php';
$myEmail = $_SESSION['user']['email'] ?? '';
?>

<div class="page-top">
<div style="max-width:1200px; margin:0 auto; padding:2rem;">

  <div style="margin-bottom:2rem;">
    <h1 style="font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:700; color:var(--text-primary);">
      <i class="fas fa-comments" style="color:var(--amber);"></i> Mes Messages
    </h1>
    <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Conversations avec vos clients à propos de vos produits</p>
  </div>

  <div style="display:grid; grid-template-columns: 360px 1fr; gap:1.5rem; min-height:550px;">
    
    <!-- Conversations List -->
    <div id="convoList" style="background:var(--paper); border:1px solid var(--border); border-radius:22px; overflow:hidden; display:flex; flex-direction:column; box-shadow:var(--shadow);">
      <div style="padding:1rem 1.2rem; border-bottom:1px solid var(--border); font-weight:700; font-size:0.9rem; color:var(--text-primary); font-family:'Playfair Display',serif;">
        <i class="fas fa-inbox"></i> Conversations
      </div>
      <div id="convoItems" style="flex:1; overflow-y:auto; padding:0.5rem;">
        <div style="text-align:center; padding:3rem 1rem; color:var(--text-muted); font-size:0.85rem;">
          <i class="fas fa-spinner fa-spin"></i> Chargement...
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div id="chatArea" style="background:var(--paper); border:1px solid var(--border); border-radius:22px; overflow:hidden; display:flex; flex-direction:column; box-shadow:var(--shadow);">
      <!-- Chat Header -->
      <div id="chatHeader" style="padding:1rem 1.5rem; border-bottom:1px solid var(--border); background:linear-gradient(135deg, rgba(224,112,32,0.06), rgba(240,138,59,0.03));">
        <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary);" id="chatHeaderTitle">
          <i class="fas fa-comment-dots"></i> Sélectionnez une conversation
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted);" id="chatHeaderSub">Cliquez sur un client à gauche pour ouvrir le chat</div>
      </div>

      <!-- Messages -->
      <div id="vendorChatMessages" style="flex:1; overflow-y:auto; padding:1rem 1.5rem; display:flex; flex-direction:column; gap:8px; min-height:350px;">
        <div style="text-align:center; padding:4rem 1rem; color:var(--text-muted); font-size:0.85rem; opacity:0.6;">
          <i class="fas fa-comments" style="font-size:3rem; margin-bottom:1rem; display:block; opacity:0.3;"></i>
          Aucune conversation sélectionnée
        </div>
      </div>

      <!-- Input -->
      <div id="chatInputArea" style="padding:1rem 1.5rem; border-top:1px solid var(--border); display:none; gap:10px;">
        <input type="text" id="vendorChatInput" placeholder="Répondre..." 
          style="flex:1; padding:10px 16px; border-radius:14px; border:1px solid var(--border); background:var(--paper-soft); color:var(--text-primary); font-size:0.9rem; outline:none; font-family:inherit;"
          onkeydown="if(event.key==='Enter')vendorSendMsg()">
        <button onclick="vendorSendMsg()" style="padding:10px 20px; border-radius:14px; border:none; background:linear-gradient(135deg,var(--amber),var(--amber-light)); color:white; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; box-shadow:0 8px 18px rgba(224,112,32,.2); font-family:inherit;">
          <i class="fas fa-paper-plane"></i> Envoyer
        </button>
      </div>
    </div>

  </div>

</div>
</div>

<script>
const MY_EMAIL = '<?= htmlspecialchars($myEmail) ?>';
let activeConvo = null;
let vendorLastMsgId = 0;
let vendorPollTimer = null;

// Load conversations
function loadConversations() {
  fetch(`chat_api.php?action=conversations`)
    .then(r => r.json())
    .then(data => {
      const container = document.getElementById('convoItems');
      if (!data.success || !data.conversations || data.conversations.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:3rem 1rem; color:var(--text-muted); font-size:0.85rem;"><i class="fas fa-inbox" style="font-size:2rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>Aucune conversation</div>';
        return;
      }
      container.innerHTML = '';
      data.conversations.forEach(c => {
        const isActive = activeConvo && activeConvo.client === c.client_email && activeConvo.produit == c.id_produit;
        const unread = parseInt(c.unread_count) || 0;
        const div = document.createElement('div');
        div.className = 'convo-item';
        div.style.cssText = `padding:12px 14px; border-radius:14px; cursor:pointer; transition:all 0.2s; margin-bottom:4px; ${
          isActive ? 'background:rgba(224,112,32,0.08); border:1px solid rgba(224,112,32,0.2);' : 'background:transparent; border:1px solid transparent;'
        }`;
        div.onmouseover = function() { if (!isActive) this.style.background = 'rgba(255,255,255,0.04)'; };
        div.onmouseout = function() { if (!isActive) this.style.background = 'transparent'; };
        div.onclick = () => openConvo(c.client_email, c.id_produit, c.nom_produit || 'Produit');
        div.innerHTML = `
          <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--amber),var(--amber-light));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-user" style="color:white; font-size:0.8rem;"></i>
            </div>
            <div style="flex:1; min-width:0;">
              <div style="font-weight:600; font-size:0.85rem; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                ${c.client_email}
                ${unread > 0 ? `<span style="background:var(--amber); color:white; font-size:0.6rem; padding:2px 6px; border-radius:10px; margin-left:4px;">${unread}</span>` : ''}
              </div>
              <div style="font-size:0.72rem; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                📦 ${c.nom_produit || 'Produit supprimé'}
              </div>
              <div style="font-size:0.7rem; color:var(--text-muted); opacity:0.7; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px;">
                ${c.last_message ? c.last_message.substring(0, 40) + (c.last_message.length > 40 ? '...' : '') : ''}
              </div>
            </div>
          </div>
        `;
        container.appendChild(div);
      });
    });
}

function openConvo(clientEmail, produitId, produitName) {
  activeConvo = { client: clientEmail, produit: produitId };
  vendorLastMsgId = 0;

  document.getElementById('chatHeaderTitle').innerHTML = `<i class="fas fa-user-circle"></i> ${clientEmail}`;
  document.getElementById('chatHeaderSub').textContent = `📦 ${produitName}`;
  document.getElementById('chatInputArea').style.display = 'flex';
  document.getElementById('vendorChatMessages').innerHTML = '<div style="text-align:center; padding:2rem; color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i></div>';

  // Mark read
  const fd = new FormData();
  fd.append('action', 'mark_read');
  fd.append('my_email', MY_EMAIL);
  fd.append('other_email', clientEmail);
  fd.append('id_produit', produitId);
  fetch('chat_api.php', { method: 'POST', body: fd });

  if (vendorPollTimer) clearInterval(vendorPollTimer);
  vendorFetchMessages();
  vendorPollTimer = setInterval(vendorFetchMessages, 3000);
  loadConversations(); // refresh unread badges
}

function vendorFetchMessages() {
  if (!activeConvo) return;
  fetch(`chat_api.php?action=fetch&user2=${encodeURIComponent(activeConvo.client)}&id_produit=${activeConvo.produit}&after=${vendorLastMsgId}`)
    .then(r => r.json())
    .then(data => {
      if (data.success && data.messages.length > 0) {
        const container = document.getElementById('vendorChatMessages');
        if (vendorLastMsgId === 0) container.innerHTML = '';

        data.messages.forEach(msg => {
          const isMine = msg.sender_email === MY_EMAIL;
          const div = document.createElement('div');
          div.style.cssText = `max-width:70%; padding:10px 14px; border-radius:14px; font-size:0.88rem; line-height:1.4; word-wrap:break-word; ${
            isMine 
              ? 'align-self:flex-end; background:linear-gradient(135deg,var(--amber),var(--amber-light)); color:white; border-bottom-right-radius:4px;'
              : 'align-self:flex-start; background:var(--paper-soft); color:var(--text-primary); border:1px solid var(--border); border-bottom-left-radius:4px;'
          }`;
          const time = new Date(msg.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
          div.innerHTML = `${msg.message}<div style="font-size:0.65rem; opacity:0.6; margin-top:4px; text-align:${isMine ? 'right' : 'left'};">${time}</div>`;
          container.appendChild(div);
          vendorLastMsgId = Math.max(vendorLastMsgId, parseInt(msg.id));
        });
        container.scrollTop = container.scrollHeight;
      } else if (data.success && data.messages.length === 0 && vendorLastMsgId === 0) {
        document.getElementById('vendorChatMessages').innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-muted); font-size:0.85rem;"><i class="fas fa-comment-dots" style="font-size:2rem; opacity:0.3; margin-bottom:0.5rem; display:block;"></i>Aucun message dans cette conversation</div>';
      }
    });
}

function vendorSendMsg() {
  if (!activeConvo) return;
  const input = document.getElementById('vendorChatInput');
  const msg = input.value.trim();
  if (!msg) return;

  const fd = new FormData();
  fd.append('action', 'send');
  fd.append('receiver_email', activeConvo.client);
  fd.append('id_produit', activeConvo.produit);
  fd.append('message', msg);

  fetch('chat_api.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        input.value = '';
        vendorFetchMessages();
      }
    });
}

// Initial load
loadConversations();
setInterval(loadConversations, 10000);
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
