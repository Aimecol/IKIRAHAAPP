/**
 * Modal Utilities for IKIRAHA Dashboard
 * Provides modal functionality for forms and dialogs
 */

class ModalUtils {
    static show(content, options = {}) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-backdrop';
            modal.innerHTML = `
                <div class="modal-dialog ${options.size || ''}">
                    <div class="modal-content">
                        ${content}
                    </div>
                </div>
            `;

            // Add event listeners
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(modal);
                    resolve(null);
                }
            });

            // Close button functionality
            modal.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.close(modal);
                    resolve(null);
                });
            });

            // Add to DOM
            document.body.appendChild(modal);
            
            // Focus first input
            setTimeout(() => {
                const firstInput = modal.querySelector('input, select, textarea');
                if (firstInput) firstInput.focus();
            }, 100);

            // Store resolve function for form submission
            modal._resolve = resolve;
        });
    }

    static close(modal) {
        if (modal && modal.parentNode) {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 200);
        }
    }

    static async showProductForm(existingProduct = null) {
        const isEdit = !!existingProduct;
        
        const content = `
            <div class="modal-header">
                <h3>${isEdit ? 'Edit Product' : 'Add New Product'}</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="product-form">
                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-input" 
                               value="${existingProduct?.name || ''}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="3" 
                                  placeholder="Enter product description">${existingProduct?.description || ''}</textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price (Rwf) *</label>
                            <input type="number" name="price" class="form-input" 
                                   value="${existingProduct?.price || ''}" min="0" step="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="is_available" 
                                   ${existingProduct?.is_available !== false ? 'checked' : ''}>
                            <span class="checkmark"></span>
                            Available for ordering
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="is_featured" 
                                   ${existingProduct?.is_featured ? 'checked' : ''}>
                            <span class="checkmark"></span>
                            Featured product
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close">Cancel</button>
                <button class="btn btn-primary" onclick="ModalUtils.submitProductForm()">
                    <i class="fas fa-save"></i>
                    ${isEdit ? 'Update' : 'Add'} Product
                </button>
            </div>
        `;

        const modal = await this.show(content, { size: 'modal-lg' });
        
        // Load categories after modal is shown
        this.loadCategoriesForForm(existingProduct?.category_id);
        
        return modal;
    }

    static async loadCategoriesForForm(selectedCategoryId = null) {
        try {
            const api = new ApiService();
            const response = await api.getCategories();
            
            if (response.success) {
                const select = document.querySelector('#product-form select[name="category_id"]');
                if (select) {
                    const options = response.data.categories.map(cat => 
                        `<option value="${cat.id}" ${cat.id == selectedCategoryId ? 'selected' : ''}>${cat.name}</option>`
                    ).join('');
                    select.innerHTML = '<option value="">Select Category</option>' + options;
                }
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }

    static submitProductForm() {
        const form = document.getElementById('product-form');
        const formData = new FormData(form);
        
        // Validate required fields
        const name = formData.get('name')?.trim();
        const price = formData.get('price');
        const categoryId = formData.get('category_id');
        
        if (!name) {
            alert('Product name is required');
            return;
        }
        
        if (!price || price <= 0) {
            alert('Valid price is required');
            return;
        }
        
        if (!categoryId) {
            alert('Category selection is required');
            return;
        }

        const productData = {
            name: name,
            description: formData.get('description')?.trim() || '',
            price: parseFloat(price),
            category_id: parseInt(categoryId),
            is_available: formData.get('is_available') === 'on',
            is_featured: formData.get('is_featured') === 'on'
        };

        // Find and close modal, return data
        const modal = form.closest('.modal-backdrop');
        if (modal && modal._resolve) {
            this.close(modal);
            modal._resolve(productData);
        }
    }

    static async showOrderDetails(order) {
        const content = `
            <div class="modal-header">
                <h3>Order #${order.id} Details</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="order-details">
                    <div class="detail-section">
                        <h4>Customer Information</h4>
                        <p><strong>Name:</strong> ${order.customer_name || 'N/A'}</p>
                        <p><strong>Email:</strong> ${order.customer_email || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${order.delivery_phone || 'N/A'}</p>
                        <p><strong>Address:</strong> ${order.delivery_address || 'N/A'}</p>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Order Information</h4>
                        <p><strong>Status:</strong> <span class="badge ${window.DashboardConfig?.getOrderStatusBadgeClass(order.status) || 'badge-secondary'}">${order.status}</span></p>
                        <p><strong>Total Amount:</strong> ${window.DashboardConfig?.formatCurrency(order.total_amount) || 'Rwf ' + order.total_amount}</p>
                        <p><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</p>
                        <p><strong>Created:</strong> ${window.DashboardConfig?.formatDate(order.created_at) || order.created_at}</p>
                    </div>
                    
                    ${order.notes ? `
                        <div class="detail-section">
                            <h4>Notes</h4>
                            <p>${order.notes}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close">Close</button>
            </div>
        `;

        return this.show(content, { size: 'modal-lg' });
    }

    static async confirm(message, title = 'Confirm Action') {
        const content = `
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close">Cancel</button>
                <button class="btn btn-danger" onclick="ModalUtils.confirmAction(true)">Confirm</button>
            </div>
        `;

        return this.show(content);
    }

    static confirmAction(confirmed) {
        const modal = document.querySelector('.modal-backdrop');
        if (modal && modal._resolve) {
            this.close(modal);
            modal._resolve(confirmed);
        }
    }
}

// Export for global use
window.ModalUtils = ModalUtils;
