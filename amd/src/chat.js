define(['core/ajax', 'core/notification'], function(Ajax, Notification) {
    const histories = {};

    const escapeHtml = function(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    };

    const renderMarkdown = function(text) {
        let html = escapeHtml(text);
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
        html = html.replace(/^\s*[-*]\s+(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
        html = html.replace(/\n/g, '<br>');
        return html;
    };

    const appendMessage = function(root, role, content) {
        const messages = root.querySelector('[data-region="messages"]');
        const item = document.createElement('div');
        item.className = 'block-aicourseassistant-message block-aicourseassistant-message-' + role;
        item.innerHTML = renderMarkdown(content);
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
    };

    const setBusy = function(root, busy) {
        const form = root.querySelector('[data-region="form"]');
        const loading = root.querySelector('[data-region="loading"]');
        form.querySelectorAll('textarea, button').forEach(function(control) {
            control.disabled = busy;
        });
        loading.classList.toggle('hidden', !busy);
    };

    const send = function(root, blockinstanceid) {
        const input = root.querySelector('[data-region="input"]');
        const message = input.value.trim();
        if (!message) {
            return;
        }

        histories[blockinstanceid] = histories[blockinstanceid] || [];
        appendMessage(root, 'user', message);
        input.value = '';
        setBusy(root, true);

        Ajax.call([{
            methodname: 'block_aicourseassistant_send_message',
            args: {
                blockinstanceid: blockinstanceid,
                message: message,
                history: JSON.stringify(histories[blockinstanceid])
            }
        }])[0].then(function(response) {
            if (!response.success) {
                throw new Error(response.error || 'Request failed');
            }
            histories[blockinstanceid].push({role: 'user', content: message});
            histories[blockinstanceid].push({role: 'assistant', content: response.answer});
            histories[blockinstanceid] = histories[blockinstanceid].slice(-12);
            appendMessage(root, 'assistant', response.answer);
            setBusy(root, false);
            input.focus();
            return response;
        }, function(error) {
            Notification.exception(error);
            setBusy(root, false);
            input.focus();
        });
    };

    const init = function(blockinstanceid) {
        const root = document.querySelector('.block-aicourseassistant[data-blockinstanceid="' + blockinstanceid + '"]');
        if (!root) {
            return;
        }
        histories[blockinstanceid] = [];

        root.querySelector('[data-region="form"]').addEventListener('submit', function(event) {
            event.preventDefault();
            send(root, blockinstanceid);
        });

        root.querySelector('[data-action="clear"]').addEventListener('click', function() {
            histories[blockinstanceid] = [];
            root.querySelector('[data-region="messages"]').innerHTML = '';
            root.querySelector('[data-region="input"]').focus();
        });
    };

    return {
        init: init
    };
});
