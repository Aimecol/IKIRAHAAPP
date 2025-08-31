// Main Dashboard Application

class DashboardApp {
  constructor() {
    this.currentUser = null;
    this.currentRole = 'merchant'; // Default role
    this.components = {};
    this.isInitialized = false;

    this.init();
  }

  async init() {
    try {
      // Show loading screen
      this.showLoading();

      // Initialize core components
      await this.initializeComponents();

      // Initialize authentication
      await this.initializeAuth();

      // Initialize router
      await this.initializeRouter();

      // Setup event listeners
      this.setupEventListeners();

      // Hide loading screen and show app
      this.hideLoading();

      this.isInitialized = true;
      console.log('Dashboard app initialized successfully');

    } catch (error) {
      console.error('Failed to initialize dashboard app:', error);
      this.showError('Failed to load dashboard. Please refresh the page.');
    }
  }

  async initializeComponents() {
    // Initialize theme manager first
    this.components.theme = new ThemeManager();

    // Initialize data service
    this.components.dataService = new DataService();

    // Initialize toast manager
    this.components.toast = new ToastManager();

    // Initialize modal manager
    this.components.modal = new ModalManager();

    // Initialize navigation
    this.components.navigation = new NavigationComponent();
    await this.components.navigation.render();
  }

  async initializeAuth() {
    this.components.auth = new AuthManager();

    // Check if user is authenticated
    const isAuthenticated = await this.components.auth.checkAuth();

    if (!isAuthenticated) {
      // Redirect to login page
      window.location.href = 'login.html';
      return;
    }

    // Get current user and role
    this.currentUser = this.components.auth.getCurrentUser();
    this.currentRole = this.components.auth.getCurrentRole();

    // Update navigation based on role
    this.components.navigation.updateForRole(this.currentRole);

    // Update profile information in UI
    this.updateProfileUI();
  }

  async initializeRouter() {
    this.components.router = new Router();

    // Define routes based on role
    this.setupRoutes();

    // Start router
    await this.components.router.start();
  }

  setupRoutes() {
    const routes = {
      '/': 'dashboard',
      '/dashboard': 'dashboard',
      '/users': 'users',
      '/merchants': 'merchants',
      '/products': 'products',
      '/orders': 'orders',
      '/analytics': 'analytics',
      '/transactions': 'transactions',
      '/reports': 'reports',
      '/settings': 'settings',
      '/profile': 'profile',
      '/notifications': 'notifications'
    };

    // Filter routes based on user role
    const allowedRoutes = this.filterRoutesByRole(routes);

    Object.entries(allowedRoutes).forEach(([path, page]) => {
      this.components.router.addRoute(path, () => this.loadPage(page));
    });

    // Add 404 route
    this.components.router.addRoute('*', () => this.loadPage('404'));
  }

  filterRoutesByRole(routes) {
    const rolePermissions = {
      'super_admin': ['dashboard', 'users', 'merchants', 'products', 'orders', 'analytics', 'transactions', 'reports', 'settings', 'profile', 'notifications'],
      'merchant': ['dashboard', 'products', 'orders', 'analytics', 'profile', 'settings', 'notifications'],
      'accountant': ['dashboard', 'transactions', 'reports', 'analytics', 'profile', 'settings', 'notifications']
    };

    const allowedPages = rolePermissions[this.currentRole] || [];
    const filteredRoutes = {};

    Object.entries(routes).forEach(([path, page]) => {
      if (allowedPages.includes(page)) {
        filteredRoutes[path] = page;
      }
    });

    return filteredRoutes;
  }

  async loadPage(pageName) {
    try {
      // Update breadcrumbs
      this.updateBreadcrumbs(pageName);

      // Load page content
      const pageContent = await this.getPageContent(pageName);

      // Update page content
      const contentContainer = document.getElementById('page-content');
      if (contentContainer) {
        contentContainer.innerHTML = pageContent;

        // Initialize page-specific functionality
        await this.initializePage(pageName);
      }

    } catch (error) {
      console.error(`Failed to load page ${pageName}:`, error);
      this.components.toast.show('Error loading page', 'error');
    }
  }

  async getPageContent(pageName) {
    // In a real app, this would fetch from templates or API
    // For now, we'll return basic content
    const pageTemplates = {
      'dashboard': await this.getDashboardContent(),
      'users': await this.getUsersContent(),
      'merchants': await this.getMerchantsContent(),
      'products': await this.getProductsContent(),
      'orders': await this.getOrdersContent(),
      'analytics': await this.getAnalyticsContent(),
      'transactions': await this.getTransactionsContent(),
      'reports': await this.getReportsContent(),
      'settings': await this.getSettingsContent(),
      'profile': await this.getProfileContent(),
      'notifications': await this.getNotificationsContent(),
      '404': this.get404Content()
    };

    return pageTemplates[pageName] || pageTemplates['404'];
  }

  async getDashboardContent() {
    try {
      // Fetch dashboard data based on current role
      const dashboardData = await this.components.dataService.getDashboardData(this.currentRole);

      // Generate KPI cards
      const kpiCards = dashboardData.kpis.map(kpi => `
        <div class="card kpi-card">
          <div class="card-body">
            <div class="kpi-icon">
              <i class="${kpi.icon}"></i>
            </div>
            <div class="kpi-content">
              <h3>${kpi.value}</h3>
              <p>${kpi.title}</p>
              <span class="kpi-change ${kpi.changeType}">${kpi.change}</span>
            </div>
          </div>
        </div>
      `).join('');

      // Generate activity timeline
      const activityItems = dashboardData.recentActivity.map(activity => `
        <div class="activity-item">
          <div class="activity-icon">
            <i class="${activity.icon}"></i>
          </div>
          <div class="activity-content">
            <p><strong>${activity.title}</strong></p>
            <p class="activity-description">${activity.description}</p>
            <p class="activity-time">${this.components.dataService.formatRelativeTime(activity.timestamp)}</p>
          </div>
        </div>
      `).join('');

      // Generate quick actions
      const quickActions = dashboardData.quickActions.map(action => `
        <button class="btn btn-${action.color} btn-block" onclick="app.handleQuickAction('${action.action}')">
          <i class="${action.icon}"></i>
          ${action.title}
        </button>
      `).join('');

      return `
        <div class="dashboard-page">
          <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, ${this.currentUser?.name || 'User'}!</p>
          </div>

          <div class="kpi-grid">
            ${kpiCards}
          </div>

          <div class="dashboard-content">
            <div class="dashboard-main">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="card-body">
                  <div class="activity-timeline">
                    ${activityItems}
                  </div>
                </div>
              </div>
            </div>

            <div class="dashboard-sidebar">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                  <div class="quick-actions">
                    ${quickActions}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    } catch (error) {
      console.error('Error loading dashboard content:', error);
      return this.getErrorContent('Failed to load dashboard data');
    }
  }

  async getUsersContent() {
    const users = [
      { id: 1, name: 'John Smith', email: 'john@example.com', role: 'Merchant', status: 'Active', lastLogin: '2 hours ago' },
      { id: 2, name: 'Sarah Johnson', email: 'sarah@example.com', role: 'Accountant', status: 'Active', lastLogin: '1 day ago' },
      { id: 3, name: 'Mike Wilson', email: 'mike@example.com', role: 'Merchant', status: 'Inactive', lastLogin: '1 week ago' },
      { id: 4, name: 'Lisa Brown', email: 'lisa@example.com', role: 'Merchant', status: 'Active', lastLogin: '3 hours ago' }
    ];

    const userRows = users.map(user => `
      <tr>
        <td>
          <div style="display: flex; align-items: center; gap: var(--space-3);">
            <div class="avatar avatar-sm avatar-fallback">${user.name.split(' ').map(n => n[0]).join('')}</div>
            <div>
              <div style="font-weight: var(--font-weight-medium);">${user.name}</div>
              <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">${user.email}</div>
            </div>
          </div>
        </td>
        <td><span class="badge badge-secondary">${user.role}</span></td>
        <td><span class="badge ${user.status === 'Active' ? 'badge-success' : 'badge-warning'}">${user.status}</span></td>
        <td style="color: var(--text-tertiary); font-size: var(--font-size-sm);">${user.lastLogin}</td>
        <td>
          <div style="display: flex; gap: var(--space-2);">
            <button class="btn btn-sm btn-secondary" onclick="app.editUser(${user.id})">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="app.deleteUser(${user.id})">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');

    return `
      <div class="users-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>User Management</h1>
            <p>Manage system users and their permissions</p>
          </div>
          <button class="btn btn-primary" onclick="app.createUser()">
            <i class="fas fa-plus"></i>
            Add User
          </button>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">All Users</h3>
            <div style="display: flex; gap: var(--space-3); margin-top: var(--space-4);">
              <input type="search" placeholder="Search users..." class="form-input" style="max-width: 300px;">
              <select class="form-select" style="max-width: 150px;">
                <option value="">All Roles</option>
                <option value="merchant">Merchant</option>
                <option value="accountant">Accountant</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div class="card-body" style="padding: 0;">
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  ${userRows}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getMerchantsContent() {
    const merchants = [
      { id: 1, name: 'Avuma Restaurant', owner: 'John Doe', type: 'Restaurant', status: 'Active', revenue: '$12,450', orders: 89, rating: 4.8 },
      { id: 2, name: 'Blessing Hotel', owner: 'Sarah Wilson', type: 'Hotel', status: 'Active', revenue: '$8,320', orders: 45, rating: 4.6 },
      { id: 3, name: 'Ice Restaurant', owner: 'Mike Johnson', type: 'Restaurant', status: 'Pending', revenue: '$0', orders: 0, rating: 0 },
      { id: 4, name: 'Peace Restaurant', owner: 'Lisa Brown', type: 'Restaurant', status: 'Active', revenue: '$15,670', orders: 123, rating: 4.9 }
    ];

    const merchantCards = merchants.map(merchant => `
      <div class="card" style="transition: all var(--transition-normal);">
        <div class="card-body">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
            <div style="display: flex; align-items: center; gap: var(--space-3);">
              <div class="avatar avatar-lg avatar-fallback">${merchant.name.split(' ').map(n => n[0]).join('')}</div>
              <div>
                <h4 style="margin: 0; font-size: var(--font-size-lg);">${merchant.name}</h4>
                <p style="margin: 0; color: var(--text-secondary); font-size: var(--font-size-sm);">Owner: ${merchant.owner}</p>
                <span class="badge badge-secondary" style="margin-top: var(--space-1);">${merchant.type}</span>
              </div>
            </div>
            <span class="badge ${merchant.status === 'Active' ? 'badge-success' : merchant.status === 'Pending' ? 'badge-warning' : 'badge-danger'}">${merchant.status}</span>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-4);">
            <div style="text-align: center;">
              <div style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-success);">${merchant.revenue}</div>
              <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">Revenue</div>
            </div>
            <div style="text-align: center;">
              <div style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-primary);">${merchant.orders}</div>
              <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">Orders</div>
            </div>
            <div style="text-align: center;">
              <div style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-warning);">${merchant.rating || 'N/A'}</div>
              <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">Rating</div>
            </div>
          </div>

          <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
            <button class="btn btn-sm btn-secondary" onclick="app.viewMerchant(${merchant.id})">
              <i class="fas fa-eye"></i>
              View
            </button>
            <button class="btn btn-sm btn-primary" onclick="app.editMerchant(${merchant.id})">
              <i class="fas fa-edit"></i>
              Edit
            </button>
          </div>
        </div>
      </div>
    `).join('');

    return `
      <div class="merchants-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>Merchant Management</h1>
            <p>Manage restaurants, hotels, and food businesses</p>
          </div>
          <button class="btn btn-primary" onclick="app.createMerchant()">
            <i class="fas fa-plus"></i>
            Add Merchant
          </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-6);">
          ${merchantCards}
        </div>
      </div>
    `;
  }

  async getProductsContent() {
    const products = [
      { id: 1, name: 'Grilled Burger', category: 'Main Course', price: '$12.99', status: 'Available', stock: 25, image: 'images/burger.svg' },
      { id: 2, name: 'Caesar Salad', category: 'Salads', price: '$8.99', status: 'Available', stock: 15, image: 'images/salad.svg' },
      { id: 3, name: 'Margherita Pizza', category: 'Pizza', price: '$14.99', status: 'Available', stock: 8, image: 'images/pizza.svg' },
      { id: 4, name: 'Vanilla Ice Cream', category: 'Desserts', price: '$4.99', status: 'Out of Stock', stock: 0, image: 'images/ice-cream.svg' },
      { id: 5, name: 'Garden Salad', category: 'Salads', price: '$7.99', status: 'Available', stock: 20, image: 'images/salad2.svg' },
      { id: 6, name: 'Greek Salad', category: 'Salads', price: '$9.99', status: 'Available', stock: 12, image: 'images/salad3.svg' }
    ];

    const productCards = products.map(product => `
      <div class="card" style="transition: all var(--transition-normal);">
        <div class="card-body">
          <div style="display: flex; gap: var(--space-4);">
            <div style="width: 80px; height: 80px; border-radius: var(--radius-lg); background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <img src="${product.image}" alt="${product.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-md);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <div style="display: none; color: var(--text-tertiary); font-size: var(--font-size-lg);"><i class="fas fa-box"></i></div>
            </div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-2);">
                <h4 style="margin: 0; font-size: var(--font-size-lg);">${product.name}</h4>
                <span class="badge ${product.status === 'Available' ? 'badge-success' : 'badge-warning'}">${product.status}</span>
              </div>
              <p style="margin: 0 0 var(--space-2) 0; color: var(--text-secondary); font-size: var(--font-size-sm);">${product.category}</p>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: var(--space-4);">
                  <div>
                    <span style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-primary);">${product.price}</span>
                  </div>
                  <div>
                    <span style="font-size: var(--font-size-sm); color: var(--text-tertiary);">Stock: ${product.stock}</span>
                  </div>
                </div>
                <div style="display: flex; gap: var(--space-2);">
                  <button class="btn btn-sm btn-secondary" onclick="app.editProduct(${product.id})">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="app.deleteProduct(${product.id})">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `).join('');

    return `
      <div class="products-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>Products</h1>
            <p>Manage your menu items and inventory</p>
          </div>
          <button class="btn btn-primary" onclick="app.createProduct()">
            <i class="fas fa-plus"></i>
            Add Product
          </button>
        </div>

        <div style="margin-bottom: var(--space-6);">
          <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
            <input type="search" placeholder="Search products..." class="form-input" style="max-width: 300px;">
            <select class="form-select" style="max-width: 150px;">
              <option value="">All Categories</option>
              <option value="main">Main Course</option>
              <option value="salads">Salads</option>
              <option value="pizza">Pizza</option>
              <option value="desserts">Desserts</option>
            </select>
            <select class="form-select" style="max-width: 150px;">
              <option value="">All Status</option>
              <option value="available">Available</option>
              <option value="out-of-stock">Out of Stock</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: var(--space-6);">
          ${productCards}
        </div>
      </div>
    `;
  }

  async getOrdersContent() {
    const orders = [
      { id: 1234, customer: 'John Smith', items: 3, total: '$24.97', status: 'Pending', time: '10 mins ago', type: 'Delivery' },
      { id: 1235, customer: 'Sarah Johnson', items: 2, total: '$18.50', status: 'Preparing', time: '15 mins ago', type: 'Pickup' },
      { id: 1236, customer: 'Mike Wilson', items: 1, total: '$12.99', status: 'Ready', time: '25 mins ago', type: 'Delivery' },
      { id: 1237, customer: 'Lisa Brown', items: 4, total: '$32.45', status: 'Completed', time: '1 hour ago', type: 'Dine-in' },
      { id: 1238, customer: 'David Lee', items: 2, total: '$19.98', status: 'Cancelled', time: '2 hours ago', type: 'Delivery' }
    ];

    const orderRows = orders.map(order => `
      <tr style="cursor: pointer;" onclick="app.viewOrder(${order.id})">
        <td>
          <div style="font-weight: var(--font-weight-medium);">#${order.id}</div>
          <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">${order.time}</div>
        </td>
        <td>
          <div style="font-weight: var(--font-weight-medium);">${order.customer}</div>
          <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">${order.items} items</div>
        </td>
        <td>
          <span class="badge badge-secondary">${order.type}</span>
        </td>
        <td style="font-weight: var(--font-weight-semibold); color: var(--color-success);">${order.total}</td>
        <td>
          <span class="badge ${
            order.status === 'Pending' ? 'badge-warning' :
            order.status === 'Preparing' ? 'badge-info' :
            order.status === 'Ready' ? 'badge-primary' :
            order.status === 'Completed' ? 'badge-success' :
            'badge-danger'
          }">${order.status}</span>
        </td>
        <td>
          <div style="display: flex; gap: var(--space-2);">
            <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); app.editOrder(${order.id})">
              <i class="fas fa-edit"></i>
            </button>
            ${order.status === 'Pending' ? `
              <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); app.acceptOrder(${order.id})">
                <i class="fas fa-check"></i>
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `).join('');

    return `
      <div class="orders-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>Orders</h1>
            <p>Manage customer orders and deliveries</p>
          </div>
          <div style="display: flex; gap: var(--space-3);">
            <button class="btn btn-secondary" onclick="app.exportOrders()">
              <i class="fas fa-download"></i>
              Export
            </button>
            <button class="btn btn-primary" onclick="app.refreshOrders()">
              <i class="fas fa-refresh"></i>
              Refresh
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <div style="display: flex; gap: var(--space-3); margin-top: var(--space-4);">
              <select class="form-select" style="max-width: 150px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="preparing">Preparing</option>
                <option value="ready">Ready</option>
                <option value="completed">Completed</option>
              </select>
              <select class="form-select" style="max-width: 150px;">
                <option value="">All Types</option>
                <option value="delivery">Delivery</option>
                <option value="pickup">Pickup</option>
                <option value="dine-in">Dine-in</option>
              </select>
            </div>
          </div>
          <div class="card-body" style="padding: 0;">
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  ${orderRows}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getAnalyticsContent() {
    const chartData = {
      revenue: [1200, 1900, 3000, 5000, 2000, 3000, 4500],
      orders: [45, 67, 89, 123, 78, 95, 134],
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    };

    return `
      <div class="analytics-page">
        <div class="page-header">
          <h1>Analytics</h1>
          <p>Business insights and performance metrics</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Revenue Trend</h3>
            </div>
            <div class="card-body">
              <div style="height: 200px; background: linear-gradient(135deg, var(--color-primary-light), var(--bg-tertiary)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 60%; background: linear-gradient(to top, var(--color-primary), transparent); opacity: 0.3;"></div>
                <div style="text-align: center; z-index: 1;">
                  <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-primary);">📈</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary); margin-top: var(--space-2);">7-day revenue trend</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Order Volume</h3>
            </div>
            <div class="card-body">
              <div style="height: 200px; background: linear-gradient(135deg, var(--color-success), var(--bg-tertiary)); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 70%; background: linear-gradient(to top, var(--color-success), transparent); opacity: 0.3;"></div>
                <div style="text-align: center; z-index: 1;">
                  <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-success);">📊</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary); margin-top: var(--space-2);">Weekly order volume</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-6);">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Performance Metrics</h3>
            </div>
            <div class="card-body">
              <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-6);">
                <div style="text-align: center; padding: var(--space-4); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-primary);">4.8</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Avg Rating</div>
                </div>
                <div style="text-align: center; padding: var(--space-4); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-success);">23min</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Avg Prep Time</div>
                </div>
                <div style="text-align: center; padding: var(--space-4); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-warning);">89%</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Success Rate</div>
                </div>
                <div style="text-align: center; padding: var(--space-4); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-info);">156</div>
                  <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Total Customers</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Top Products</h3>
            </div>
            <div class="card-body">
              <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <span style="font-weight: var(--font-weight-medium);">Grilled Burger</span>
                  <span style="color: var(--color-primary); font-weight: var(--font-weight-bold);">45 orders</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <span style="font-weight: var(--font-weight-medium);">Caesar Salad</span>
                  <span style="color: var(--color-primary); font-weight: var(--font-weight-bold);">32 orders</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3); background: var(--bg-secondary); border-radius: var(--radius-lg);">
                  <span style="font-weight: var(--font-weight-medium);">Margherita Pizza</span>
                  <span style="color: var(--color-primary); font-weight: var(--font-weight-bold);">28 orders</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getTransactionsContent() {
    const transactions = [
      { id: 'TXN001', date: '2025-08-31', merchant: 'Avuma Restaurant', amount: '$234.50', type: 'Payment', status: 'Completed', method: 'Card' },
      { id: 'TXN002', date: '2025-08-31', merchant: 'Blessing Hotel', amount: '$156.75', type: 'Payment', status: 'Completed', method: 'Mobile Money' },
      { id: 'TXN003', date: '2025-08-30', merchant: 'Ice Restaurant', amount: '$89.25', type: 'Refund', status: 'Pending', method: 'Card' },
      { id: 'TXN004', date: '2025-08-30', merchant: 'Peace Restaurant', amount: '$345.80', type: 'Payment', status: 'Completed', method: 'Cash' },
      { id: 'TXN005', date: '2025-08-29', merchant: 'Avuma Restaurant', amount: '$67.40', type: 'Payment', status: 'Failed', method: 'Card' }
    ];

    const transactionRows = transactions.map(txn => `
      <tr style="cursor: pointer;" onclick="app.viewTransaction('${txn.id}')">
        <td>
          <div style="font-weight: var(--font-weight-medium);">${txn.id}</div>
          <div style="font-size: var(--font-size-xs); color: var(--text-tertiary);">${txn.date}</div>
        </td>
        <td>${txn.merchant}</td>
        <td>
          <span class="badge ${txn.type === 'Payment' ? 'badge-primary' : txn.type === 'Refund' ? 'badge-warning' : 'badge-secondary'}">${txn.type}</span>
        </td>
        <td style="font-weight: var(--font-weight-semibold); color: ${txn.type === 'Refund' ? 'var(--color-danger)' : 'var(--color-success)'};">${txn.amount}</td>
        <td>
          <span class="badge ${
            txn.status === 'Completed' ? 'badge-success' :
            txn.status === 'Pending' ? 'badge-warning' :
            'badge-danger'
          }">${txn.status}</span>
        </td>
        <td>${txn.method}</td>
        <td>
          <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); app.viewTransactionDetails('${txn.id}')">
            <i class="fas fa-eye"></i>
          </button>
        </td>
      </tr>
    `).join('');

    return `
      <div class="transactions-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>Transactions</h1>
            <p>Financial transaction history and management</p>
          </div>
          <div style="display: flex; gap: var(--space-3);">
            <button class="btn btn-secondary" onclick="app.exportTransactions()">
              <i class="fas fa-download"></i>
              Export CSV
            </button>
            <button class="btn btn-primary" onclick="app.reconcileAccounts()">
              <i class="fas fa-balance-scale"></i>
              Reconcile
            </button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
          <div class="card">
            <div class="card-body" style="text-align: center;">
              <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-success); margin-bottom: var(--space-2);">$78,901</div>
              <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Total Revenue</div>
              <div style="font-size: var(--font-size-xs); color: var(--color-success); margin-top: var(--space-1);">+12% this month</div>
            </div>
          </div>
          <div class="card">
            <div class="card-body" style="text-align: center;">
              <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-primary); margin-bottom: var(--space-2);">2,345</div>
              <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Transactions</div>
              <div style="font-size: var(--font-size-xs); color: var(--color-success); margin-top: var(--space-1);">+18% this month</div>
            </div>
          </div>
          <div class="card">
            <div class="card-body" style="text-align: center;">
              <div style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-warning); margin-bottom: var(--space-2);">$5,432</div>
              <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Pending</div>
              <div style="font-size: var(--font-size-xs); color: var(--color-danger); margin-top: var(--space-1);">-8% this month</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Transactions</h3>
            <div style="display: flex; gap: var(--space-3); margin-top: var(--space-4);">
              <input type="date" class="form-input" style="max-width: 150px;">
              <input type="date" class="form-input" style="max-width: 150px;">
              <select class="form-select" style="max-width: 150px;">
                <option value="">All Types</option>
                <option value="payment">Payment</option>
                <option value="refund">Refund</option>
              </select>
              <select class="form-select" style="max-width: 150px;">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
          </div>
          <div class="card-body" style="padding: 0;">
            <div class="table-container">
              <table class="table">
                <thead>
                  <tr>
                    <th>Transaction ID</th>
                    <th>Merchant</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  ${transactionRows}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getReportsContent() {
    return `
      <div class="reports-page">
        <div class="page-header">
          <h1>Reports</h1>
          <p>Financial and business reports</p>
        </div>
        <div class="card">
          <div class="card-body">
            <p>Reports interface will be implemented here.</p>
          </div>
        </div>
      </div>
    `;
  }

  async getSettingsContent() {
    return `
      <div class="settings-page">
        <div class="page-header">
          <h1>Settings</h1>
          <p>Application and account settings</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-6);">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Account Settings</h3>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label">Business Name</label>
                <input type="text" class="form-input" value="${this.currentUser?.name || 'Your Business'}" placeholder="Enter business name">
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input" value="${this.currentUser?.email || 'user@example.com'}" placeholder="Enter email">
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-input" placeholder="Enter phone number">
              </div>
              <div class="form-group">
                <label class="form-label">Address</label>
                <textarea class="form-textarea" placeholder="Enter business address"></textarea>
              </div>
              <button class="btn btn-primary">
                <i class="fas fa-check"></i>
                Save Changes
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Notification Preferences</h3>
            </div>
            <div class="card-body">
              <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <div style="font-weight: var(--font-weight-medium);">Email Notifications</div>
                    <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Receive order updates via email</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                  </label>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <div style="font-weight: var(--font-weight-medium);">SMS Notifications</div>
                    <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Receive urgent alerts via SMS</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                  </label>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <div style="font-weight: var(--font-weight-medium);">Push Notifications</div>
                    <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Browser push notifications</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Security</h3>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-input" placeholder="Enter current password">
              </div>
              <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" class="form-input" placeholder="Enter new password">
              </div>
              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-input" placeholder="Confirm new password">
              </div>
              <button class="btn btn-primary">
                <i class="fas fa-lock"></i>
                Update Password
              </button>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Theme Preferences</h3>
            </div>
            <div class="card-body">
              <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                <div>
                  <label class="form-label">Theme</label>
                  <select class="form-select">
                    <option value="auto">Auto (System)</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                  </select>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <div style="font-weight: var(--font-weight-medium);">Reduced Motion</div>
                    <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">Minimize animations</div>
                  </div>
                  <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getProfileContent() {
    const user = this.currentUser || { name: 'User', email: 'user@example.com', role: 'merchant' };

    return `
      <div class="profile-page">
        <div class="page-header">
          <h1>Profile</h1>
          <p>Manage your account information</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--space-6);">
          <div class="card">
            <div class="card-body" style="text-align: center;">
              <div class="avatar avatar-lg avatar-fallback" style="width: 120px; height: 120px; font-size: var(--font-size-3xl); margin: 0 auto var(--space-4);">
                ${user.name.split(' ').map(n => n[0]).join('').toUpperCase()}
              </div>
              <h3 style="margin-bottom: var(--space-2);">${user.name}</h3>
              <p style="color: var(--text-secondary); margin-bottom: var(--space-4);">${user.email}</p>
              <span class="badge badge-primary" style="margin-bottom: var(--space-6);">${user.role.replace('_', ' ').toUpperCase()}</span>

              <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                <button class="btn btn-secondary btn-block">
                  <i class="fas fa-camera"></i>
                  Change Photo
                </button>
                <button class="btn btn-danger btn-block">
                  <i class="fas fa-sign-out-alt"></i>
                  Sign Out
                </button>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Personal Information</h3>
            </div>
            <div class="card-body">
              <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4);">
                <div class="form-group">
                  <label class="form-label">First Name</label>
                  <input type="text" class="form-input" value="${user.name.split(' ')[0] || ''}" placeholder="First name">
                </div>
                <div class="form-group">
                  <label class="form-label">Last Name</label>
                  <input type="text" class="form-input" value="${user.name.split(' ')[1] || ''}" placeholder="Last name">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input" value="${user.email}" placeholder="Email address">
              </div>

              <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4);">
                <div class="form-group">
                  <label class="form-label">Phone Number</label>
                  <input type="tel" class="form-input" placeholder="Phone number">
                </div>
                <div class="form-group">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" class="form-input">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea class="form-textarea" placeholder="Tell us about yourself..."></textarea>
              </div>

              <div style="display: flex; gap: var(--space-3); justify-content: flex-end;">
                <button class="btn btn-secondary">Cancel</button>
                <button class="btn btn-primary">
                  <i class="fas fa-check"></i>
                  Save Changes
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  async getNotificationsContent() {
    const notifications = [
      { id: 1, title: 'New Order Received', message: 'Order #1234 from John Smith', time: '5 minutes ago', type: 'order', read: false },
      { id: 2, title: 'Payment Processed', message: 'Payment of $234.50 has been confirmed', time: '1 hour ago', type: 'payment', read: false },
      { id: 3, title: 'Product Low Stock', message: 'Vanilla Ice Cream is running low (2 items left)', time: '2 hours ago', type: 'warning', read: true },
      { id: 4, title: 'New Review', message: 'You received a 5-star review from Sarah Johnson', time: '1 day ago', type: 'review', read: true },
      { id: 5, title: 'System Update', message: 'Dashboard has been updated with new features', time: '2 days ago', type: 'system', read: true }
    ];

    const notificationItems = notifications.map(notification => `
      <div class="card" style="margin-bottom: var(--space-4); ${!notification.read ? 'border-left: 4px solid var(--color-primary);' : ''}">
        <div class="card-body" style="display: flex; align-items: flex-start; gap: var(--space-4);">
          <div style="width: 40px; height: 40px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; flex-shrink: 0; background-color: ${
            notification.type === 'order' ? 'var(--color-primary-light)' :
            notification.type === 'payment' ? 'rgba(52, 199, 89, 0.1)' :
            notification.type === 'warning' ? 'rgba(255, 149, 0, 0.1)' :
            notification.type === 'review' ? 'rgba(255, 149, 0, 0.1)' :
            'var(--bg-tertiary)'
          }; color: ${
            notification.type === 'order' ? 'var(--color-primary)' :
            notification.type === 'payment' ? 'var(--color-success)' :
            notification.type === 'warning' ? 'var(--color-warning)' :
            notification.type === 'review' ? 'var(--color-warning)' :
            'var(--text-secondary)'
          };">
            <i class="${
              notification.type === 'order' ? 'fas fa-shopping-cart' :
              notification.type === 'payment' ? 'fas fa-credit-card' :
              notification.type === 'warning' ? 'fas fa-exclamation-triangle' :
              notification.type === 'review' ? 'fas fa-star' :
              'fas fa-bell'
            }"></i>
          </div>
          <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-2);">
              <h4 style="margin: 0; font-size: var(--font-size-base); font-weight: ${!notification.read ? 'var(--font-weight-semibold)' : 'var(--font-weight-medium)'};">${notification.title}</h4>
              ${!notification.read ? '<div style="width: 8px; height: 8px; border-radius: var(--radius-full); background-color: var(--color-primary);"></div>' : ''}
            </div>
            <p style="margin: 0 0 var(--space-2) 0; color: var(--text-secondary); font-size: var(--font-size-sm);">${notification.message}</p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: var(--font-size-xs); color: var(--text-tertiary);">${notification.time}</span>
              <div style="display: flex; gap: var(--space-2);">
                ${!notification.read ? `
                  <button class="btn btn-sm btn-secondary" onclick="app.markAsRead(${notification.id})">
                    <i class="fas fa-check"></i>
                    Mark Read
                  </button>
                ` : ''}
                <button class="btn btn-sm btn-danger" onclick="app.deleteNotification(${notification.id})">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `).join('');

    return `
      <div class="notifications-page">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>Notifications</h1>
            <p>Stay updated with your business activities</p>
          </div>
          <div style="display: flex; gap: var(--space-3);">
            <button class="btn btn-secondary" onclick="app.markAllAsRead()">
              <i class="fas fa-check"></i>
              Mark All Read
            </button>
            <button class="btn btn-danger" onclick="app.clearAllNotifications()">
              <i class="fas fa-times"></i>
              Clear All
            </button>
          </div>
        </div>

        <div style="display: flex; gap: var(--space-4); margin-bottom: var(--space-6);">
          <button class="btn btn-primary">All</button>
          <button class="btn btn-secondary">Unread</button>
          <button class="btn btn-secondary">Orders</button>
          <button class="btn btn-secondary">Payments</button>
          <button class="btn btn-secondary">System</button>
        </div>

        <div>
          ${notificationItems}
        </div>
      </div>
    `;
  }

  get404Content() {
    return `
      <div class="error-page">
        <div class="error-content">
          <h1>404</h1>
          <h2>Page Not Found</h2>
          <p>The page you're looking for doesn't exist.</p>
          <button class="btn btn-primary" onclick="app.goHome()">
            <i class="fas fa-home"></i>
            Go Home
          </button>
        </div>
      </div>
    `;
  }

  setupEventListeners() {
    // Navigation toggle
    const navToggle = document.getElementById('nav-toggle');
    if (navToggle) {
      navToggle.addEventListener('click', () => {
        this.toggleMobileNav();
      });
    }

    // Profile dropdown
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.querySelector('.profile-dropdown');
    if (profileBtn && profileDropdown) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('open');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', () => {
        profileDropdown.classList.remove('open');
      });
    }

    // Global search
    const globalSearch = document.getElementById('global-search');
    if (globalSearch) {
      globalSearch.addEventListener('input', (e) => {
        this.handleGlobalSearch(e.target.value);
      });
    }

    // Notifications
    const notificationsBtn = document.getElementById('notifications-btn');
    if (notificationsBtn) {
      notificationsBtn.addEventListener('click', () => {
        this.showNotifications();
      });
    }
  }

  toggleMobileNav() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
      sidebar.classList.toggle('mobile-open');
    }
  }

  handleGlobalSearch(query) {
    // Implement global search functionality
    console.log('Searching for:', query);
  }

  showNotifications() {
    // Show notifications panel
    this.components.toast.show('Notifications feature coming soon!', 'info');
  }

  updateBreadcrumbs(pageName) {
    const breadcrumbs = document.getElementById('breadcrumbs');
    if (breadcrumbs) {
      const pageNames = {
        'dashboard': 'Dashboard',
        'users': 'Users',
        'merchants': 'Merchants',
        'products': 'Products',
        'orders': 'Orders',
        'analytics': 'Analytics',
        'transactions': 'Transactions',
        'reports': 'Reports',
        'settings': 'Settings',
        'profile': 'Profile',
        'notifications': 'Notifications'
      };

      breadcrumbs.innerHTML = `<span class="breadcrumb-item">${pageNames[pageName] || 'Page'}</span>`;
    }
  }

  async initializePage(pageName) {
    // Initialize page-specific functionality
    switch (pageName) {
      case 'dashboard':
        this.initializeDashboard();
        break;
      case 'analytics':
        this.initializeAnalytics();
        break;
      // Add more page initializations as needed
    }
  }

  initializeDashboard() {
    // Initialize dashboard-specific functionality
    console.log('Dashboard initialized');
  }

  initializeAnalytics() {
    // Initialize analytics charts and functionality
    console.log('Analytics initialized');
  }

  showLoading() {
    const loadingScreen = document.getElementById('loading-screen');
    const app = document.getElementById('app');

    if (loadingScreen) loadingScreen.style.display = 'flex';
    if (app) app.style.display = 'none';
  }

  hideLoading() {
    const loadingScreen = document.getElementById('loading-screen');
    const app = document.getElementById('app');

    if (loadingScreen) {
      loadingScreen.style.opacity = '0';
      setTimeout(() => {
        loadingScreen.style.display = 'none';
      }, 300);
    }

    if (app) app.style.display = 'flex';
  }

  showError(message) {
    if (this.components.toast) {
      this.components.toast.show(message, 'error');
    } else {
      alert(message);
    }
  }

  updateProfileUI() {
    if (!this.currentUser) return;

    // Update profile name
    const profileName = document.querySelector('.profile-name');
    if (profileName) {
      profileName.textContent = this.currentUser.name || 'User';
    }

    // Update profile avatar with user initials
    const profileAvatar = document.querySelector('.profile-avatar');
    if (profileAvatar && this.currentUser.name) {
      const initials = this.currentUser.name
        .split(' ')
        .map(name => name.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);

      profileAvatar.innerHTML = `<span>${initials}</span>`;
    }
  }

  // Quick action methods
  createProduct() {
    this.components.router.navigate('/products');
    this.components.toast.show('Redirected to products page', 'info');
  }

  viewOrders() {
    this.components.router.navigate('/orders');
  }

  viewReports() {
    this.components.router.navigate('/reports');
  }

  goHome() {
    this.components.router.navigate('/dashboard');
  }

  createUser() {
    this.components.toast.show('Create user functionality coming soon!', 'info');
  }

  handleQuickAction(action) {
    const actions = {
      'addProduct': () => this.createProduct(),
      'viewOrders': () => this.viewOrders(),
      'viewAnalytics': () => this.viewAnalytics(),
      'addMerchant': () => this.createMerchant(),
      'viewReports': () => this.viewReports(),
      'manageUsers': () => this.manageUsers(),
      'exportTransactions': () => this.exportTransactions(),
      'reconcileAccounts': () => this.reconcileAccounts()
    };

    if (actions[action]) {
      actions[action]();
    } else {
      this.components.toast.info(`${action} functionality coming soon!`);
    }
  }

  viewAnalytics() {
    this.components.router.navigate('/analytics');
  }

  createMerchant() {
    this.components.router.navigate('/merchants');
    this.components.toast.info('Redirected to merchants page');
  }

  manageUsers() {
    this.components.router.navigate('/users');
  }

  exportTransactions() {
    this.components.toast.info('Exporting transactions... (Demo)');
    // Mock CSV download
    setTimeout(() => {
      this.components.toast.success('Transactions exported successfully!');
    }, 2000);
  }

  reconcileAccounts() {
    this.components.toast.info('Account reconciliation feature coming soon!');
  }

  exportOrders() {
    this.components.toast.info('Exporting orders... (Demo)');
    // Mock CSV download
    setTimeout(() => {
      this.components.toast.success('Orders exported successfully!');
    }, 2000);
  }

  getErrorContent(message) {
    return `
      <div class="error-page">
        <div class="error-content">
          <h2>Error</h2>
          <p>${message}</p>
          <button class="btn btn-primary" onclick="window.location.reload()">
            <i class="fas fa-refresh"></i>
            Reload Page
          </button>
        </div>
      </div>
    `;
  }

  // Additional action methods for enhanced pages
  editUser(id) {
    this.components.toast.info(`Edit user ${id} functionality coming soon!`);
  }

  deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
      this.components.toast.success(`User ${id} deleted successfully!`);
    }
  }

  viewMerchant(id) {
    this.components.toast.info(`View merchant ${id} details coming soon!`);
  }

  editMerchant(id) {
    this.components.toast.info(`Edit merchant ${id} functionality coming soon!`);
  }

  editProduct(id) {
    this.components.toast.info(`Edit product ${id} functionality coming soon!`);
  }

  deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
      this.components.toast.success(`Product ${id} deleted successfully!`);
    }
  }

  viewOrder(id) {
    this.components.toast.info(`View order ${id} details coming soon!`);
  }

  editOrder(id) {
    this.components.toast.info(`Edit order ${id} functionality coming soon!`);
  }

  acceptOrder(id) {
    this.components.toast.success(`Order ${id} accepted and is now being prepared!`);
  }

  refreshOrders() {
    this.components.toast.info('Refreshing orders...');
    setTimeout(() => {
      this.components.toast.success('Orders refreshed successfully!');
    }, 1000);
  }

  viewTransaction(id) {
    this.components.toast.info(`View transaction ${id} details coming soon!`);
  }

  viewTransactionDetails(id) {
    this.components.toast.info(`Transaction ${id} details coming soon!`);
  }

  markAsRead(id) {
    this.components.toast.success(`Notification ${id} marked as read!`);
  }

  deleteNotification(id) {
    this.components.toast.success(`Notification ${id} deleted!`);
  }

  markAllAsRead() {
    this.components.toast.success('All notifications marked as read!');
  }

  clearAllNotifications() {
    if (confirm('Are you sure you want to clear all notifications?')) {
      this.components.toast.success('All notifications cleared!');
    }
  }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.app = new DashboardApp();
});

export default DashboardApp;
