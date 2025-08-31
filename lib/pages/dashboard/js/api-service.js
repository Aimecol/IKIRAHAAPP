/**
 * IKIRAHA Dashboard API Service
 * Handles all communication with the backend API
 */

class ApiService {
    constructor() {
        // Use dynamic URL detection if DashboardConfig is available
        this.baseUrl = window.DashboardConfig ?
            window.DashboardConfig.API_BASE_URL :
            'http://localhost/ikirahaapp/ikiraha-api/public';
        this.accessToken = localStorage.getItem('access_token');
        this.refreshToken = localStorage.getItem('refresh_token');
    }

    // Set authentication tokens
    setTokens(accessToken, refreshToken = null) {
        this.accessToken = accessToken;
        localStorage.setItem('access_token', accessToken);
        
        if (refreshToken) {
            this.refreshToken = refreshToken;
            localStorage.setItem('refresh_token', refreshToken);
        }
    }

    // Clear authentication tokens
    clearTokens() {
        this.accessToken = null;
        this.refreshToken = null;
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user_data');
    }

    // Get authorization headers
    getAuthHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };
        
        if (this.accessToken) {
            headers['Authorization'] = `Bearer ${this.accessToken}`;
        }
        
        return headers;
    }

    // Make HTTP request with error handling and token refresh
    async makeRequest(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            method: options.method || 'GET',
            headers: this.getAuthHeaders(),
            ...options
        };

        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        try {
            let response = await fetch(url, config);
            
            // Handle token expiration
            if (response.status === 401 && this.refreshToken) {
                const refreshed = await this.refreshAccessToken();
                if (refreshed) {
                    // Retry request with new token
                    config.headers = this.getAuthHeaders();
                    response = await fetch(url, config);
                }
            }

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Refresh access token
    async refreshAccessToken() {
        if (!this.refreshToken) {
            return false;
        }

        try {
            const response = await fetch(`${this.baseUrl}/auth/refresh`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: this.refreshToken
                })
            });

            if (response.ok) {
                const data = await response.json();
                this.setTokens(data.data.access_token, data.data.refresh_token);
                return true;
            }
        } catch (error) {
            console.error('Token refresh failed:', error);
        }

        // If refresh fails, clear tokens
        this.clearTokens();
        return false;
    }

    // Authentication methods
    async login(email, password) {
        const response = await this.makeRequest('/auth/login', {
            method: 'POST',
            body: { email, password }
        });

        if (response.success) {
            this.setTokens(response.data.access_token, response.data.refresh_token);
            localStorage.setItem('user_data', JSON.stringify(response.data.user));
        }

        return response;
    }

    async logout() {
        try {
            await this.makeRequest('/auth/logout', {
                method: 'POST'
            });
        } catch (error) {
            console.error('Logout API call failed:', error);
        } finally {
            this.clearTokens();
        }
    }

    async getProfile() {
        return await this.makeRequest('/auth/profile');
    }

    async updateProfile(profileData) {
        return await this.makeRequest('/auth/profile', {
            method: 'PUT',
            body: profileData
        });
    }

    // Product methods
    async getProducts(filters = {}) {
        const params = new URLSearchParams(filters).toString();
        const endpoint = params ? `/products?${params}` : '/products';
        return await this.makeRequest(endpoint);
    }

    async getProduct(id) {
        return await this.makeRequest(`/products/${id}`);
    }

    async createProduct(productData) {
        return await this.makeRequest('/products', {
            method: 'POST',
            body: productData
        });
    }

    async updateProduct(id, productData) {
        return await this.makeRequest(`/products/${id}`, {
            method: 'PUT',
            body: productData
        });
    }

    async deleteProduct(id) {
        return await this.makeRequest(`/products/${id}`, {
            method: 'DELETE'
        });
    }

    async getFeaturedProducts() {
        return await this.makeRequest('/products/featured');
    }

    async searchProducts(query) {
        return await this.makeRequest(`/products/search?q=${encodeURIComponent(query)}`);
    }

    // Category methods
    async getCategories() {
        return await this.makeRequest('/categories');
    }

    // Order methods
    async getOrders(filters = {}) {
        const params = new URLSearchParams(filters).toString();
        const endpoint = params ? `/orders?${params}` : '/orders';
        return await this.makeRequest(endpoint);
    }

    async getAllOrders(filters = {}) {
        const params = new URLSearchParams(filters).toString();
        const endpoint = params ? `/orders/all?${params}` : '/orders/all';
        return await this.makeRequest(endpoint);
    }

    async getOrder(id) {
        return await this.makeRequest(`/orders/${id}`);
    }

    async createOrder(orderData) {
        return await this.makeRequest('/orders', {
            method: 'POST',
            body: orderData
        });
    }

    async updateOrderStatus(id, status) {
        return await this.makeRequest(`/orders/${id}/status`, {
            method: 'PUT',
            body: { status }
        });
    }

    async getRestaurantOrders(restaurantId, filters = {}) {
        const params = new URLSearchParams(filters).toString();
        const endpoint = params ? `/restaurants/${restaurantId}/orders?${params}` : `/restaurants/${restaurantId}/orders`;
        return await this.makeRequest(endpoint);
    }

    // Dashboard analytics methods
    async getDashboardStats() {
        // This would be a custom endpoint for dashboard statistics
        // For now, we'll aggregate data from existing endpoints
        try {
            const [orders, products] = await Promise.all([
                this.getAllOrders(),
                this.getProducts()
            ]);

            return {
                success: true,
                data: {
                    totalOrders: orders.data?.orders?.length || 0,
                    totalProducts: products.data?.products?.length || 0,
                    // Add more aggregated stats as needed
                }
            };
        } catch (error) {
            console.error('Failed to get dashboard stats:', error);
            return { success: false, message: error.message };
        }
    }

    // User management methods (admin only)
    async getUsers(filters = {}) {
        // Since the backend doesn't have a dedicated users endpoint,
        // we'll simulate this by getting user data from other endpoints
        try {
            // For now, return the current user and some mock data
            // In a real implementation, you'd add a /users endpoint to the backend
            const profileResponse = await this.getProfile();
            if (profileResponse.success) {
                return {
                    success: true,
                    data: {
                        users: [
                            profileResponse.data.user,
                            // Add other users from the database setup
                            { id: 1, name: 'Super Admin', email: 'admin@ikiraha.com', role: 'super_admin', is_active: true, phone: '+250788123456' },
                            { id: 2, name: 'Restaurant Owner', email: 'merchant@ikiraha.com', role: 'merchant', is_active: true, phone: '+250788123457' },
                            { id: 3, name: 'Financial Manager', email: 'accountant@ikiraha.com', role: 'accountant', is_active: true, phone: '+250788123458' },
                            { id: 4, name: 'Test Client', email: 'client@ikiraha.com', role: 'client', is_active: true, phone: '+250788123459' }
                        ]
                    }
                };
            }
        } catch (error) {
            console.error('Failed to get users:', error);
        }

        return { success: false, message: 'Failed to load users' };
    }

    async getUser(id) {
        // Simulate getting a specific user
        const usersResponse = await this.getUsers();
        if (usersResponse.success) {
            const user = usersResponse.data.users.find(u => u.id == id);
            if (user) {
                return { success: true, data: { user } };
            }
        }
        return { success: false, message: 'User not found' };
    }

    async createUser(userData) {
        // Use the register endpoint for creating new users
        return await this.makeRequest('/auth/register', {
            method: 'POST',
            body: userData
        });
    }

    async updateUser(id, userData) {
        // Use the profile update endpoint
        return await this.updateProfile(userData);
    }

    async deleteUser(id) {
        // This would need to be implemented in the backend
        // For now, return a success response
        return { success: true, message: 'User deletion would be implemented in backend' };
    }

    // Health check
    async healthCheck() {
        return await this.makeRequest('/health');
    }

    // Utility methods
    isAuthenticated() {
        return !!this.accessToken;
    }

    getCurrentUser() {
        const userData = localStorage.getItem('user_data');
        return userData ? JSON.parse(userData) : null;
    }

    getCurrentUserRole() {
        const user = this.getCurrentUser();
        return user ? user.role : null;
    }
}

// Export for use in other files
window.ApiService = ApiService;
