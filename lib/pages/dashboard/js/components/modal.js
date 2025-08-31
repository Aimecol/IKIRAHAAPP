// Modal Manager - Handle modal dialogs
export class ModalManager {
  constructor() {
    this.modals = [];
    this.container = null;
    this.init();
  }

  init() {
    this.container = document.getElementById('modal-container');
    if (!this.container) {
      console.warn('Modal container not found');
    }

    // Setup global event listeners
    this.setupGlobalListeners();
  }

  show(options = {}) {
    const modal = this.createModal(options);
    this.addModal(modal);
    return modal.id;
  }

  createModal(options) {
    const id = this.generateId();
    const modal = {
      id,
      title: options.title || 'Modal',
      content: options.content || '',
      size: options.size || 'medium', // small, medium, large, full
      closable: options.closable !== false,
      backdrop: options.backdrop !== false,
      keyboard: options.keyboard !== false,
      actions: options.actions || [],
      onShow: options.onShow || null,
      onHide: options.onHide || null,
      onConfirm: options.onConfirm || null,
      onCancel: options.onCancel || null
    };

    return modal;
  }

  addModal(modal) {
    this.modals.push(modal);
    this.renderModal(modal);

    // Trigger onShow callback
    if (modal.onShow) {
      modal.onShow(modal);
    }
  }

  renderModal(modal) {
    if (!this.container) return;

    const modalElement = document.createElement('div');
    modalElement.className = 'modal-container show';
    modalElement.setAttribute('data-modal-id', modal.id);

    modalElement.innerHTML = `
      <div class="modal-backdrop"></div>
      <div class="modal">
        <div class="modal-content modal-${modal.size}">
          <div class="modal-header">
            <h3 class="modal-title">${modal.title}</h3>
            ${modal.closable ? '<button class="modal-close" aria-label="Close modal"><i class="fas fa-times"></i></button>' : ''}
          </div>
          <div class="modal-body">
            ${modal.content}
          </div>
          ${this.renderActions(modal.actions)}
        </div>
      </div>
    `;

    // Add event listeners
    this.setupModalListeners(modalElement, modal);

    // Add to container
    this.container.appendChild(modalElement);

    // Focus management
    this.manageFocus(modalElement);
  }

  renderActions(actions) {
    if (!actions || actions.length === 0) return '';

    return `
      <div class="modal-footer">
        ${actions.map((action, index) => `
          <button class="btn ${action.class || 'btn-secondary'}" data-action-index="${index}">
            ${action.icon ? `<i class="${action.icon}"></i>` : ''}
            ${action.text}
          </button>
        `).join('')}
      </div>
    `;
  }

  setupModalListeners(modalElement, modal) {
    // Close button
    const closeBtn = modalElement.querySelector('.modal-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        this.hide(modal.id);
      });
    }

    // Backdrop click
    if (modal.backdrop) {
      const backdrop = modalElement.querySelector('.modal-backdrop');
      backdrop.addEventListener('click', () => {
        this.hide(modal.id);
      });
    }

    // Action buttons
    modal.actions.forEach((action, index) => {
      const actionBtn = modalElement.querySelector(`[data-action-index="${index}"]`);
      if (actionBtn) {
        actionBtn.addEventListener('click', () => {
          if (action.handler) {
            const result = action.handler(modal);
            // Close modal unless handler returns false
            if (result !== false && action.closeOnClick !== false) {
              this.hide(modal.id);
            }
          } else if (action.type === 'confirm' && modal.onConfirm) {
            const result = modal.onConfirm(modal);
            if (result !== false) {
              this.hide(modal.id);
            }
          } else if (action.type === 'cancel' && modal.onCancel) {
            modal.onCancel(modal);
            this.hide(modal.id);
          } else {
            this.hide(modal.id);
          }
        });
      }
    });

    // Keyboard events
    if (modal.keyboard) {
      modalElement.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          this.hide(modal.id);
        }
      });
    }
  }

  hide(id) {
    const modal = this.modals.find(m => m.id === id);
    const modalElement = this.container?.querySelector(`[data-modal-id="${id}"]`);

    if (modalElement) {
      modalElement.classList.remove('show');

      setTimeout(() => {
        if (modalElement.parentNode) {
          modalElement.parentNode.removeChild(modalElement);
        }
      }, 300); // Match CSS transition duration
    }

    // Trigger onHide callback
    if (modal && modal.onHide) {
      modal.onHide(modal);
    }

    // Remove from array
    this.modals = this.modals.filter(m => m.id !== id);

    // Restore focus to previous element
    this.restoreFocus();
  }

  hideAll() {
    this.modals.forEach(modal => {
      this.hide(modal.id);
    });
  }

  manageFocus(modalElement) {
    // Store currently focused element
    this.previouslyFocused = document.activeElement;

    // Focus first focusable element in modal
    const focusableElements = modalElement.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    if (focusableElements.length > 0) {
      focusableElements[0].focus();
    }

    // Trap focus within modal
    modalElement.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
          if (document.activeElement === firstFocusable) {
            e.preventDefault();
            lastFocusable.focus();
          }
        } else {
          if (document.activeElement === lastFocusable) {
            e.preventDefault();
            firstFocusable.focus();
          }
        }
      }
    });
  }

  restoreFocus() {
    if (this.previouslyFocused && this.modals.length === 0) {
      this.previouslyFocused.focus();
      this.previouslyFocused = null;
    }
  }

  setupGlobalListeners() {
    // Handle browser back button
    window.addEventListener('popstate', () => {
      if (this.modals.length > 0) {
        this.hideAll();
      }
    });
  }

  generateId() {
    return 'modal-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
  }

  // Convenience methods
  confirm(title, message, options = {}) {
    return new Promise((resolve) => {
      this.show({
        title: title || 'Confirm',
        content: `<p>${message}</p>`,
        size: 'small',
        actions: [
          {
            text: options.cancelText || 'Cancel',
            class: 'btn-secondary',
            type: 'cancel',
            handler: () => resolve(false)
          },
          {
            text: options.confirmText || 'Confirm',
            class: options.confirmClass || 'btn-primary',
            type: 'confirm',
            handler: () => resolve(true)
          }
        ],
        ...options
      });
    });
  }

  alert(title, message, options = {}) {
    return new Promise((resolve) => {
      this.show({
        title: title || 'Alert',
        content: `<p>${message}</p>`,
        size: 'small',
        actions: [
          {
            text: options.buttonText || 'OK',
            class: 'btn-primary',
            handler: () => resolve(true)
          }
        ],
        ...options
      });
    });
  }

  prompt(title, message, defaultValue = '', options = {}) {
    return new Promise((resolve) => {
      const inputId = 'prompt-input-' + Date.now();
      this.show({
        title: title || 'Input',
        content: `
          <p>${message}</p>
          <div class="form-group">
            <input type="text" id="${inputId}" class="form-input" value="${defaultValue}" placeholder="${options.placeholder || ''}">
          </div>
        `,
        size: 'small',
        actions: [
          {
            text: options.cancelText || 'Cancel',
            class: 'btn-secondary',
            handler: () => resolve(null)
          },
          {
            text: options.confirmText || 'OK',
            class: 'btn-primary',
            handler: () => {
              const input = document.getElementById(inputId);
              resolve(input ? input.value : null);
            }
          }
        ],
        onShow: () => {
          // Focus input after modal is shown
          setTimeout(() => {
            const input = document.getElementById(inputId);
            if (input) {
              input.focus();
              input.select();
            }
          }, 100);
        },
        ...options
      });
    });
  }
}
