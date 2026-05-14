/**
 * SkillBridge — AI Assistant Chat Widget
 * Vanilla ES6+ — Reusable across all dashboards
 * Usage: AiAssistant.init({ role: 'client', context: {...} })
 */

'use strict';

const AiAssistant = (() => {
  // =========================================================
  // STATE
  // =========================================================
  let config = {
    role: 'client',
    context: {},
    endpoint: 'ai_chat.php'
  };

  let isOpen = false;
  let isLoading = false;
  let history = []; // Conversation history, max 10 messages
  let hasGreeted = false;

  // Assistant names and greetings per role
  const assistants = {
    client: {
      name: 'Nova',
      icon: '✨',
      greeting: 'Bonjour ! Je suis Nova, votre assistante shopping. Comment puis-je vous aider à trouver le produit parfait ?'
    },
    vendeur: {
      name: 'Forge',
      icon: '🔥',
      greeting: 'Salut ! Je suis Forge, votre mentor freelance. Comment puis-je vous aider à améliorer vos produits et vos ventes ?'
    },
    admin: {
      name: 'Sigma',
      icon: '⚡',
      greeting: 'Bonjour. Je suis Sigma, votre assistant de gestion. Comment puis-je vous aider à piloter la plateforme aujourd\'hui ?'
    }
  };

  // DOM references (set during init)
  let bubble, panel, messagesEl, inputEl, sendBtn, typingEl;

  // =========================================================
  // INITIALIZATION
  // =========================================================

  /**
   * Initialize the AI Assistant widget
   * @param {Object} opts - { role: string, context: object, endpoint?: string }
   */
  function init(opts = {}) {
    config.role = opts.role || 'client';
    config.context = opts.context || {};
    if (opts.endpoint) config.endpoint = opts.endpoint;

    // Build and inject the widget DOM
    buildWidget();
    bindEvents();
  }

  // =========================================================
  // DOM CONSTRUCTION
  // =========================================================

  function buildWidget() {
    const assistant = assistants[config.role] || assistants.client;
    const roleClass = config.role;

    // Create bubble button
    bubble = document.createElement('button');
    bubble.className = `ai-bubble ai-bubble--${roleClass}`;
    bubble.setAttribute('aria-label', `Ouvrir l'assistant ${assistant.name}`);
    bubble.innerHTML = `
      ${assistant.icon}
      <span class="ai-bubble-dot"></span>
    `;

    // Create panel
    panel = document.createElement('div');
    panel.className = 'ai-panel';
    panel.innerHTML = `
      <div class="ai-panel-header">
        <div class="ai-panel-avatar ai-panel-avatar--${roleClass}">
          ${assistant.icon}
        </div>
        <div class="ai-panel-info">
          <div class="ai-panel-name">${assistant.name}</div>
          <div class="ai-panel-status">En ligne</div>
        </div>
        <button class="ai-panel-close" aria-label="Fermer">
          <i class="fas fa-minus"></i>
        </button>
      </div>
      <div class="ai-messages" id="aiMessages"></div>
      <div class="ai-typing" id="aiTyping">
        <div class="ai-typing-dots">
          <span class="ai-typing-dot"></span>
          <span class="ai-typing-dot"></span>
          <span class="ai-typing-dot"></span>
        </div>
      </div>
      <div class="ai-input-area">
        <input type="text" class="ai-input" id="aiInput"
               placeholder="Écrivez un message..." autocomplete="off" maxlength="2000">
        <button class="ai-send ai-send--${roleClass}" id="aiSend" aria-label="Envoyer">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    `;

    // Append to body
    document.body.appendChild(panel);
    document.body.appendChild(bubble);

    // Store references
    messagesEl = panel.querySelector('#aiMessages');
    inputEl = panel.querySelector('#aiInput');
    sendBtn = panel.querySelector('#aiSend');
    typingEl = panel.querySelector('#aiTyping');
  }

  // =========================================================
  // EVENT BINDING
  // =========================================================

  function bindEvents() {
    // Toggle panel on bubble click
    bubble.addEventListener('click', togglePanel);

    // Close panel
    panel.querySelector('.ai-panel-close').addEventListener('click', togglePanel);

    // Send on button click
    sendBtn.addEventListener('click', handleSend);

    // Send on Enter key
    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    });
  }

  // =========================================================
  // PANEL TOGGLE
  // =========================================================

  function togglePanel() {
    isOpen = !isOpen;

    if (isOpen) {
      panel.classList.add('ai-panel--open');
      bubble.classList.add('ai-bubble--hidden');
      inputEl.focus();

      // Show greeting on first open
      if (!hasGreeted) {
        const assistant = assistants[config.role] || assistants.client;
        addMessage('assistant', assistant.greeting);
        hasGreeted = true;
      }
    } else {
      panel.classList.remove('ai-panel--open');
      bubble.classList.remove('ai-bubble--hidden');
    }
  }

  // =========================================================
  // MESSAGE HANDLING
  // =========================================================

  function handleSend() {
    const message = inputEl.value.trim();
    if (!message || isLoading) return;

    // Display user message
    addMessage('user', message);
    inputEl.value = '';

    // Send to backend
    sendToApi(message);
  }

  /**
   * Adds a message bubble to the chat
   * @param {string} role - 'user', 'assistant', or 'error'
   * @param {string} content - The message text
   */
  function addMessage(role, content) {
    const msg = document.createElement('div');

    if (role === 'error') {
      msg.className = 'ai-msg ai-msg--error';
      msg.textContent = content;
    } else if (role === 'user') {
      msg.className = `ai-msg ai-msg--user ai-msg--${config.role}`;
      msg.textContent = content;
    } else {
      msg.className = 'ai-msg ai-msg--assistant';
      // Simple markdown-like formatting: **bold**, *italic*, `code`
      msg.innerHTML = formatText(content);
    }

    messagesEl.appendChild(msg);
    scrollToBottom();

    // Track history for API (only user and assistant, not errors)
    if (role !== 'error') {
      history.push({ role: role, content: content });
      // Keep only last 10 messages
      if (history.length > 10) {
        history = history.slice(-10);
      }
    }
  }

  /**
   * Simple text formatting (bold, italic, code)
   */
  function formatText(text) {
    return text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.+?)\*/g, '<em>$1</em>')
      .replace(/`(.+?)`/g, '<code style="background:rgba(255,255,255,0.1);padding:1px 5px;border-radius:4px;font-size:0.82em;">$1</code>')
      .replace(/\n/g, '<br>');
  }

  /**
   * Scrolls chat to the bottom
   */
  function scrollToBottom() {
    requestAnimationFrame(() => {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    });
  }

  // =========================================================
  // API COMMUNICATION
  // =========================================================

  /**
   * Sends a message to the backend AI endpoint
   * @param {string} message - User's message
   */
  async function sendToApi(message) {
    isLoading = true;
    sendBtn.disabled = true;
    showTyping(true);

    try {
      const response = await fetch(config.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: message,
          role: config.role,
          context: JSON.stringify(config.context),
          history: history.slice(0, -1) // Exclude the message we just added
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        addMessage('assistant', data.response);
      } else {
        addMessage('error', data.error || 'Une erreur est survenue. Réessayez.');
      }
    } catch (err) {
      console.error('AI Assistant error:', err);
      addMessage('error', '⚠️ Impossible de contacter l\'assistant. Vérifiez votre connexion.');
    } finally {
      isLoading = false;
      sendBtn.disabled = false;
      showTyping(false);
    }
  }

  /**
   * Shows/hides the typing indicator
   */
  function showTyping(show) {
    if (show) {
      typingEl.classList.add('ai-typing--visible');
    } else {
      typingEl.classList.remove('ai-typing--visible');
    }
    scrollToBottom();
  }

  // =========================================================
  // PUBLIC API — allows updating context dynamically
  // =========================================================

  function updateContext(newContext) {
    config.context = { ...config.context, ...newContext };
  }

  // =========================================================
  // EXPOSE PUBLIC METHODS
  // =========================================================
  return {
    init,
    updateContext
  };
})();
