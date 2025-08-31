// Data Service - Handle API calls and mock data
export class DataService {
  constructor() {
    this.baseUrl = './data';
    this.cache = new Map();
    this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
  }

  async fetchData(endpoint, options = {}) {
    const cacheKey = `${endpoint}-${JSON.stringify(options)}`;

    // Check cache first
    if (this.cache.has(cacheKey) && !options.skipCache) {
      const cached = this.cache.get(cacheKey);
      if (Date.now() - cached.timestamp < this.cacheTimeout) {
        return cached.data;
      }
    }

    try {
      const url = `${this.baseUrl}/${endpoint}.json`;
      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      // Cache the result
      this.cache.set(cacheKey, {
        data,
        timestamp: Date.now()
      });

      return data;
    } catch (error) {
      console.error(`Failed to fetch ${endpoint}:`, error);

      // Return mock data as fallback
      return this.getMockData(endpoint);
    }
  }

  getMockData(endpoint) {
    const mockData = {
      dashboard: {
        kpis: {
          merchant: [
            { id: "orders", title: "Orders", value: "89", change: "+15%", changeType: "positive", icon: "fas fa-shopping-cart" },
            { id: "revenue", title: "Revenue", value: "$3,456", change: "+22%", changeType: "positive", icon: "fas fa-dollar-sign" },
            { id: "products", title: "Products", value: "45", change: "+3", changeType: "positive", icon: "fas fa-box" },
            { id: "rating", title: "Rating", value: "4.8", change: "+0.2", changeType: "positive", icon: "fas fa-star" }
          ]
        },
        recentActivity: [
          { id: 1, title: "New order received", description: "Order #1234", timestamp: new Date().toISOString(), icon: "fas fa-plus" },
          { id: 2, title: "Product updated", description: "Burger Special", timestamp: new Date(Date.now() - 900000).toISOString(), icon: "fas fa-edit" }
        ]
      },
      products: [],
      orders: [],
      users: [],
      transactions: []
    };

    return mockData[endpoint] || {};
  }

  // Dashboard specific methods
  async getDashboardData(role = 'merchant') {
    const data = await this.fetchData('dashboard');
    return {
      kpis: data.kpis[role] || [],
      recentActivity: data.recentActivity || [],
      quickActions: data.quickActions[role] || []
    };
  }

  async getKPIs(role = 'merchant') {
    const data = await this.fetchData('dashboard');
    return data.kpis[role] || [];
  }

  async getRecentActivity() {
    const data = await this.fetchData('dashboard');
    return data.recentActivity || [];
  }

  async getQuickActions(role = 'merchant') {
    const data = await this.fetchData('dashboard');
    return data.quickActions[role] || [];
  }

  // Products methods
  async getProducts(filters = {}) {
    const data = await this.fetchData('products');
    let products = data.products || [];

    // Apply filters
    if (filters.category) {
      products = products.filter(p => p.category === filters.category);
    }
    if (filters.status) {
      products = products.filter(p => p.status === filters.status);
    }
    if (filters.search) {
      const search = filters.search.toLowerCase();
      products = products.filter(p =>
        p.name.toLowerCase().includes(search) ||
        p.description.toLowerCase().includes(search)
      );
    }

    return products;
  }

  async getProduct(id) {
    const data = await this.fetchData('products');
    const products = data.products || [];
    return products.find(p => p.id === id);
  }

  // Orders methods
  async getOrders(filters = {}) {
    const data = await this.fetchData('orders');
    let orders = data.orders || [];

    // Apply filters
    if (filters.status) {
      orders = orders.filter(o => o.status === filters.status);
    }
    if (filters.dateFrom) {
      orders = orders.filter(o => new Date(o.createdAt) >= new Date(filters.dateFrom));
    }
    if (filters.dateTo) {
      orders = orders.filter(o => new Date(o.createdAt) <= new Date(filters.dateTo));
    }

    return orders;
  }

  async getOrder(id) {
    const data = await this.fetchData('orders');
    const orders = data.orders || [];
    return orders.find(o => o.id === id);
  }

  // Users methods
  async getUsers(filters = {}) {
    const data = await this.fetchData('users');
    let users = data.users || [];

    // Apply filters
    if (filters.role) {
      users = users.filter(u => u.role === filters.role);
    }
    if (filters.status) {
      users = users.filter(u => u.status === filters.status);
    }
    if (filters.search) {
      const search = filters.search.toLowerCase();
      users = users.filter(u =>
        u.name.toLowerCase().includes(search) ||
        u.email.toLowerCase().includes(search)
      );
    }

    return users;
  }

  async getUser(id) {
    const data = await this.fetchData('users');
    const users = data.users || [];
    return users.find(u => u.id === id);
  }

  // Transactions methods
  async getTransactions(filters = {}) {
    const data = await this.fetchData('transactions');
    let transactions = data.transactions || [];

    // Apply filters
    if (filters.type) {
      transactions = transactions.filter(t => t.type === filters.type);
    }
    if (filters.dateFrom) {
      transactions = transactions.filter(t => new Date(t.date) >= new Date(filters.dateFrom));
    }
    if (filters.dateTo) {
      transactions = transactions.filter(t => new Date(t.date) <= new Date(filters.dateTo));
    }

    return transactions;
  }

  // Analytics methods
  async getAnalyticsData(period = '30d') {
    const data = await this.fetchData('analytics');
    return data[period] || {};
  }

  // Cache management
  clearCache() {
    this.cache.clear();
  }

  invalidateCache(pattern) {
    for (const key of this.cache.keys()) {
      if (key.includes(pattern)) {
        this.cache.delete(key);
      }
    }
  }

  // Utility methods
  formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency
    }).format(amount);
  }

  formatDate(date, options = {}) {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      ...options
    }).format(new Date(date));
  }

  formatRelativeTime(date) {
    const now = new Date();
    const target = new Date(date);
    const diffMs = now - target;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    return this.formatDate(date);
  }
}
