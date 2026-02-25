document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss des messages flash après 3.5s
    var messages = document.querySelectorAll('.message-success, .message-error');
    messages.forEach(function (msg) {
        setTimeout(function () {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(function () {
                if (msg.parentNode) {
                    msg.parentNode.removeChild(msg);
                }
            }, 500);
        }, 3500);
    });
});
