/**
 * IKIRAHA Dashboard Configuration
 * Centralized configuration for the dashboard application
 */

class DashboardConfig {
    static get API_BASE_URL() {
        // Automatically detect the correct API URL based on current location
        const protocol = window.location.protocol;
        const hostname = window.location.hostname;
        const port = window.location.port;
        
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            return `${protocol}//${hostname}${port ? ':' + port : ''}/ikirahaapp/ikiraha-api/public`;
        } else {
            // Production URL - update this for your production environment
            return `${protocol}//${hostname}/ikiraha-api/public`;
        }
    }

    static get ENDPOINTS() {
        return {
            // Authentication
            LOGIN: '/auth/login',
            LOGOUT: '/auth/logout',
            REGISTER: '/auth/register',
            PROFILE: '/auth/profile',
            REFRESH: '/auth/refresh',
            CHANGE_PASSWORD: '/auth/change-password',

            // Products
            PRODUCTS: '/products',
            PRODUCT_BY_ID: '/products/{id}',
            FEATURED_PRODUCTS: '/products/featured',
            SEARCH_PRODUCTS: '/products/search',

            // Categories
            CATEGORIES: '/categories',

            // Orders
            ORDERS: '/orders',
            ALL_ORDERS: '/orders/all',
            ORDER_BY_ID: '/orders/{id}',
            UPDATE_ORDER_STATUS: '/orders/{id}/status',
            RESTAURANT_ORDERS: '/restaurants/{id}/orders',

            // Health
            HEALTH: '/health',
            ROOT: '/'
        };
    }

    static get ROLES() {
        return {
            SUPER_ADMIN: 'super_admin',
            MERCHANT: 'merchant',
            ACCOUNTANT: 'accountant',
            CLIENT: 'client'
        };
    }

    static get PERMISSIONS() {
        return {
            [this.ROLES.SUPER_ADMIN]: ['all'],
            [this.ROLES.MERCHANT]: ['products', 'orders', 'analytics', 'profile'],
            [this.ROLES.ACCOUNTANT]: ['transactions', 'reports', 'analytics', 'profile'],
            [this.ROLES.CLIENT]: ['orders', 'profile']
        };
    }

    static get ORDER_STATUSES() {
        return {
            PENDING: 'pending',
            CONFIRMED: 'confirmed',
            PREPARING: 'preparing',
            READY: 'ready',
            DELIVERED: 'delivered',
            CANCELLED: 'cancelled'
        };
    }

    static get PAYMENT_METHODS() {
        return {
            MTN_RWANDA: 'mtn_rwanda',
            AIRTEL_RWANDA: 'airtel_rwanda',
            CASH: 'cash'
        };
    }

    static get CACHE_DURATION() {
        return {
            SHORT: 2 * 60 * 1000,    // 2 minutes
            MEDIUM: 5 * 60 * 1000,   // 5 minutes
            LONG: 15 * 60 * 1000     // 15 minutes
        };
    }

    static get TOAST_DURATION() {
        return {
            SHORT: 3000,
            MEDIUM: 5000,
            LONG: 8000
        };
    }

    // Check if user has permission for a specific action
    static hasPermission(userRole, permission) {
        const userPermissions = this.PERMISSIONS[userRole] || [];
        return userPermissions.includes('all') || userPermissions.includes(permission);
    }

    // Get navigation items based on user role
    static getNavigationItems(role) {
        const baseItems = [
            { id: 'dashboard', label: 'Dashboard', icon: 'fas fa-tachometer-alt', href: '#dashboard' },
            { id: 'products', label: 'Products', icon: 'fas fa-box', href: '#products' },
            { id: 'orders', label: 'Orders', icon: 'fas fa-shopping-cart', href: '#orders' },
            { id: 'analytics', label: 'Analytics', icon: 'fas fa-chart-bar', href: '#analytics' },
            { id: 'settings', label: 'Settings', icon: 'fas fa-cog', href: '#settings' },
            { id: 'profile', label: 'Profile', icon: 'fas fa-user', href: '#profile' }
        ];

        // Add role-specific items
        if (role === this.ROLES.SUPER_ADMIN) {
            baseItems.splice(1, 0,
                { id: 'users', label: 'Users', icon: 'fas fa-users', href: '#users' },
                { id: 'merchants', label: 'Merchants', icon: 'fas fa-store', href: '#merchants' }
            );
        }

        if (role === this.ROLES.ACCOUNTANT) {
            baseItems.splice(3, 0,
                { id: 'transactions', label: 'Transactions', icon: 'fas fa-credit-card', href: '#transactions' }
            );
        }

        return baseItems;
    }

    // Get status badge class for orders
    static getOrderStatusBadgeClass(status) {
        const statusClasses = {
            [this.ORDER_STATUSES.PENDING]: 'badge-warning',
            [this.ORDER_STATUSES.CONFIRMED]: 'badge-info',
            [this.ORDER_STATUSES.PREPARING]: 'badge-primary',
            [this.ORDER_STATUSES.READY]: 'badge-success',
            [this.ORDER_STATUSES.DELIVERED]: 'badge-success',
            [this.ORDER_STATUSES.CANCELLED]: 'badge-danger'
        };
        return statusClasses[status] || 'badge-secondary';
    }

    // Get role badge class
    static getRoleBadgeClass(role) {
        const roleClasses = {
            [this.ROLES.SUPER_ADMIN]: 'badge-danger',
            [this.ROLES.MERCHANT]: 'badge-primary',
            [this.ROLES.ACCOUNTANT]: 'badge-secondary',
            [this.ROLES.CLIENT]: 'badge-success'
        };
        return roleClasses[role] || 'badge-secondary';
    }

    // Format currency
    static formatCurrency(amount) {
        return `Rwf ${parseFloat(amount || 0).toLocaleString()}`;
    }

    // Format date
    static formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Format relative time
    static formatRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)} minutes ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`;
        return `${Math.floor(diff / 86400000)} days ago`;
    }

    // Validate email
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Validate phone number (Rwanda format)
    static isValidPhone(phone) {
        const phoneRegex = /^\+250[0-9]{9}$/;
        return phoneRegex.test(phone);
    }

    // Get default test accounts
    static getTestAccounts() {
        return [
            { email: 'admin@ikiraha.com', password: 'password', role: 'Super Admin' },
            { email: 'merchant@ikiraha.com', password: 'password', role: 'Merchant' },
            { email: 'accountant@ikiraha.com', password: 'password', role: 'Accountant' },
            { email: 'client@ikiraha.com', password: 'password', role: 'Client' }
        ];
    }
}

// Export for use in other files
window.DashboardConfig = DashboardConfig;
