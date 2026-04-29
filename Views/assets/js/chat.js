document.addEventListener('DOMContentLoaded', function () {
    const messagesContainer = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');

    if (!messagesContainer || !chatForm) {
        return;
    }

    const conversationId = messagesContainer.dataset.conversationId;
    const currentRole = messagesContainer.dataset.role;
    const currentSender = messagesContainer.dataset.senderName;
    const socket = typeof io !== 'undefined' ? io('http://localhost:3000') : null;

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function escapeHtml(value) {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderMessage(messageData) {
        const row = document.createElement('div');
        row.className = 'chat-bubble-row' + (messageData.sender_role === currentRole ? ' is-mine' : '');

        row.innerHTML = `
            <div class="chat-bubble">
                <div class="chat-bubble-name">${escapeHtml(messageData.sender_name)}</div>
                <div>${escapeHtml(messageData.message).replaceAll('\n', '<br>')}</div>
                <small>${escapeHtml(messageData.created_at)}</small>
            </div>
        `;

        messagesContainer.appendChild(row);
        scrollToBottom();
    }

    if (socket) {
        socket.emit('join-room', { room: 'conversation-' + conversationId });
    }

    if (socket) {
        socket.on('chat-message', function (payload) {
            if (String(payload.id_conversation) !== String(conversationId)) {
                return;
            }

            if (payload.sender_name === currentSender && payload.sender_role === currentRole) {
                return;
            }

            renderMessage(payload);
        });
    }

    chatForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(chatForm);
        const message = (formData.get('message') || '').toString().trim();

        if (!message) {
            return;
        }

        try {
            const response = await fetch('?action=chat_send', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (!result.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: result.message || 'Impossible d envoyer le message.'
                    });
                }
                return;
            }

            renderMessage(result.messageData);
            if (socket) {
                socket.emit('chat-message', {
                    room: 'conversation-' + conversationId,
                    ...result.messageData
                });
            }

            chatForm.reset();
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur reseau',
                    text: 'Le message n a pas pu etre envoye. Verifiez la connexion et le serveur de chat.'
                });
            }
        }
    });

    scrollToBottom();
});
