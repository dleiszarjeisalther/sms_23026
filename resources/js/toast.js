// Create a container for toasts if it doesn't exist
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
});

/**
 * Shows a toast notification.
 * @param {string} message - The message to display.
 * @param {string} type - 'success', 'error', 'info', 'warning' (default: 'info')
 * @param {number} duration - Time in ms before hiding (default: 3000)
 */
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return; // Safeguard if called too early

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Add icon based on type
    let icon = '';
    switch(type) {
        case 'success': icon = '✓'; break;
        case 'error': icon = '✕'; break;
        case 'warning': icon = '⚠'; break;
        default: icon = 'ℹ'; break;
    }

    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Remove after duration
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300); // Wait for CSS transition to finish
    }, duration);
}
