// Toast Manager - Handle notification toasts
class ToastManager {
  constructor() {
    this.toasts = [];
    this.container = null;
    this.init();
  }

  init() {
    this.container = document.getElementById('toast-container');
    if (!this.container) {
      console.warn('Toast container not found');
    }
  }

  show(message, type = 'info', options = {}) {
    const toast = this.createToast(message, type, options);
    this.addToast(toast);
    return toast.id;
  }

  createToast(message, type, options) {
    const id = this.generateId();
    const toast = {
      id,
      message,
      type,
      title: options.title || this.getDefaultTitle(type),
      duration: options.duration || this.getDefaultDuration(type),
      persistent: options.persistent || false,
      actions: options.actions || []
    };

    return toast;
  }

  addToast(toast) {
    this.toasts.push(toast);
    this.renderToast(toast);

    // Auto-remove toast if not persistent
    if (!toast.persistent && toast.duration > 0) {
      setTimeout(() => {
        this.removeToast(toast.id);
      }, toast.duration);
    }
  }

  renderToast(toast) {
    if (!this.container) return;

    const toastElement = document.createElement('div');
    toastElement.className = `toast toast-${toast.type}`;
    toastElement.setAttribute('data-toast-id', toast.id);

    toastElement.innerHTML = `
      <div class="toast-icon">
        <i class="${this.getIconClass(toast.type)}"></i>
      </div>
      <div class="toast-content">
        ${toast.title ? `<div class="toast-title">${toast.title}</div>` : ''}
        <div class="toast-message">${toast.message}</div>
        ${this.renderActions(toast.actions)}
      </div>
      <button class="toast-close" aria-label="Close notification">
        <i class="fas fa-times"></i>
      </button>
    `;

    // Add event listeners
    const closeBtn = toastElement.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
      this.removeToast(toast.id);
    });

    // Add action listeners
    toast.actions.forEach((action, index) => {
      const actionBtn = toastElement.querySelector(`[data-action-index="${index}"]`);
      if (actionBtn && action.handler) {
        actionBtn.addEventListener('click', () => {
          action.handler();
          if (action.closeOnClick !== false) {
            this.removeToast(toast.id);
          }
        });
      }
    });

    // Add to container
    this.container.appendChild(toastElement);

    // Trigger show animation
    requestAnimationFrame(() => {
      toastElement.classList.add('show');
    });
  }

  renderActions(actions) {
    if (!actions || actions.length === 0) return '';

    return `
      <div class="toast-actions">
        ${actions.map((action, index) => `
          <button class="btn btn-sm ${action.class || 'btn-secondary'}" data-action-index="${index}">
            ${action.icon ? `<i class="${action.icon}"></i>` : ''}
            ${action.text}
          </button>
        `).join('')}
      </div>
    `;
  }

  removeToast(id) {
    const toastElement = this.container?.querySelector(`[data-toast-id="${id}"]`);
    if (toastElement) {
      toastElement.classList.remove('show');

      setTimeout(() => {
        if (toastElement.parentNode) {
          toastElement.parentNode.removeChild(toastElement);
        }
      }, 300); // Match CSS transition duration
    }

    // Remove from array
    this.toasts = this.toasts.filter(toast => toast.id !== id);
  }

  removeAll() {
    this.toasts.forEach(toast => {
      this.removeToast(toast.id);
    });
  }

  getDefaultTitle(type) {
    const titles = {
      success: 'Success',
      error: 'Error',
      warning: 'Warning',
      info: 'Information'
    };
    return titles[type] || 'Notification';
  }

  getDefaultDuration(type) {
    const durations = {
      success: 4000,
      error: 6000,
      warning: 5000,
      info: 4000
    };
    return durations[type] || 4000;
  }

  getIconClass(type) {
    const icons = {
      success: 'fas fa-check',
      error: 'fas fa-exclamation-triangle',
      warning: 'fas fa-exclamation',
      info: 'fas fa-info'
    };
    return icons[type] || 'fas fa-bell';
  }

  generateId() {
    return 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
  }

  // Convenience methods
  success(message, options = {}) {
    return this.show(message, 'success', options);
  }

  error(message, options = {}) {
    return this.show(message, 'error', options);
  }

  warning(message, options = {}) {
    return this.show(message, 'warning', options);
  }

  info(message, options = {}) {
    return this.show(message, 'info', options);
  }
}
