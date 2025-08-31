// Main Dashboard Application
import { NavigationComponent } from './components/navigation.js';
import { ToastManager } from './components/toast.js';
import { ModalManager } from './components/modal.js';
import { ThemeManager } from './components/theme.js';
import { DataService } from './components/data-service.js';
import { Router } from './router.js';
import { AuthManager } from './auth.js';

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
    return `
      <div class="users-page">
        <div class="page-header">
          <h1>User Management</h1>
          <button class="btn btn-primary" onclick="app.createUser()">
            <i class="fas fa-plus"></i>
            Add User
          </button>
        </div>
        <div class="card">
          <div class="card-body">
            <p>User management interface will be implemented here.</p>
          </div>
        </div>
      </div>
    `;
  }

  async getProductsContent() {
    return `
      <div class="products-page">
        <div class="page-header">
          <h1>Products</h1>
          <button class="btn btn-primary" onclick="app.createProduct()">
            <i class="fas fa-plus"></i>
            Add Product
          </button>
        </div>
        <div class="card">
          <div class="card-body">
            <p>Product management interface will be implemented here.</p>
          </div>
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
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.app = new DashboardApp();
});

export default DashboardApp;
