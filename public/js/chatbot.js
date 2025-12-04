document.addEventListener('DOMContentLoaded', function () {
    
    // Referencias a los elementos del DOM
    const bubble = document.getElementById('chatbot-bubble');
    const window = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close'); // (Necesitas añadir un botón de cerrar en la UI)
    const sendBtn = document.getElementById('chatbot-send');
    const messageInput = document.getElementById('chatbot-input-text');
    const messagesContainer = document.getElementById('chatbot-messages');

    // --- TAREA 1: Mostrar u ocultar la ventana ---
    if (bubble) {
        bubble.addEventListener('click', () => {
            window.classList.toggle('show');
        });
    }

    // (Debes añadir un botón de cerrar en tu `chatbot_ui.php` y darle el id 'chatbot-close')
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            window.classList.remove('show');
        });
    }

    // --- TAREA 2: Enviar un mensaje (al hacer clic o presionar Enter) ---
    const sendMessage = async () => {
        const messageText = messageInput.value.trim();
        if (messageText === '') return; // No enviar mensajes vacíos

        // 1. Mostrar el mensaje del usuario en la UI
        addMessageToUI(messageText, 'user');
        messageInput.value = ''; // Limpiar el input

        try {
            // 2. Enviar el mensaje al backend (a la API de IA)
            const response = await fetch('modules/chatbot/chatbot_api_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: messageText })
            });

            if (!response.ok) {
                throw new Error('Error en la respuesta de la API');
            }

            const data = await response.json();
            
            // 3. Mostrar la respuesta del bot en la UI
            addMessageToUI(data.reply, 'bot');

        } catch (error) {
            console.error('Error al contactar al chatbot:', error);
            addMessageToUI('Lo siento, no puedo conectarme en este momento.', 'bot');
        }
    };

    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }

    if (messageInput) {
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // --- TAREA 3: Función de ayuda para añadir mensajes a la ventana ---
    function addMessageToUI(text, type) {
        const messageElement = document.createElement('div');
        messageElement.className = `chat-message ${type}`; // 'user' o 'bot'
        messageElement.textContent = text;
        
        messagesContainer.appendChild(messageElement);
        
        // Hacer scroll automático al final
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

});