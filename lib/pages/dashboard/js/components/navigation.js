// Navigation Component - Reusable navigation for desktop and mobile
export class NavigationComponent {
  constructor() {
    this.currentRole = localStorage.getItem('userRole') || 'merchant';
    this.navigationItems = this.getNavigationItems();
    this.isCollapsed = false;
    this.isMobile = window.innerWidth <= 768;
    this.currentRoute = window.location.hash.slice(1) || 'dashboard';
    this.init();
  }

  init() {
    this.render();
    this.attachEventListeners();
    this.handleResize();
    window.addEventListener('resize', () => this.handleResize());
    window.addEventListener('hashchange', () => this.updateActiveRoute());
  }

  getNavigationItems() {
    return {
      super_admin: [
        {
          section: 'Main',
          items: [
            { icon: 'fas fa-tachometer-alt', text: 'Dashboard', href: '/dashboard', badge: null },
            { icon: 'fas fa-users', text: 'Users', href: '/users', badge: null },
            { icon: 'fas fa-store', text: 'Merchants', href: '/merchants', badge: '12' }
          ]
        },
        {
          section: 'Management',
          items: [
            { icon: 'fas fa-box', text: 'Products', href: '/products', badge: null },
            { icon: 'fas fa-shopping-cart', text: 'Orders', href: '/orders', badge: '5' },
            { icon: 'fas fa-chart-bar', text: 'Analytics', href: '/analytics', badge: null }
          ]
        },
        {
          section: 'Financial',
          items: [
            { icon: 'fas fa-credit-card', text: 'Transactions', href: '/transactions', badge: null },
            { icon: 'fas fa-file-alt', text: 'Reports', href: '/reports', badge: null }
          ]
        },
        {
          section: 'System',
          items: [
            { icon: 'fas fa-bell', text: 'Notifications', href: '/notifications', badge: '3' },
            { icon: 'fas fa-cog', text: 'Settings', href: '/settings', badge: null },
            { icon: 'fas fa-user', text: 'Profile', href: '/profile', badge: null }
          ]
        }
      ],
      merchant: [
        {
          section: 'Main',
          items: [
            { icon: 'fas fa-tachometer-alt', text: 'Dashboard', href: '/dashboard', badge: null },
            { icon: 'fas fa-box', text: 'Products', href: '/products', badge: null },
            { icon: 'fas fa-shopping-cart', text: 'Orders', href: '/orders', badge: '5' }
          ]
        },
        {
          section: 'Business',
          items: [
            { icon: 'fas fa-chart-line', text: 'Analytics', href: '/analytics', badge: null },
            { icon: 'fas fa-star', text: 'Reviews', href: '/reviews', badge: '2' }
          ]
        },
        {
          section: 'Account',
          items: [
            { icon: 'fas fa-bell', text: 'Notifications', href: '/notifications', badge: '3' },
            { icon: 'fas fa-user', text: 'Profile', href: '/profile', badge: null },
            { icon: 'fas fa-cog', text: 'Settings', href: '/settings', badge: null }
          ]
        }
      ],
      accountant: [
        {
          section: 'Main',
          items: [
            { icon: 'fas fa-tachometer-alt', text: 'Dashboard', href: '/dashboard', badge: null },
            { icon: 'fas fa-credit-card', text: 'Transactions', href: '/transactions', badge: null }
          ]
        },
        {
          section: 'Reports',
          items: [
            { icon: 'fas fa-file-alt', text: 'Financial Reports', href: '/reports', badge: null },
            { icon: 'fas fa-chart-bar', text: 'Analytics', href: '/analytics', badge: null },
            { icon: 'fas fa-download', text: 'Exports', href: '/exports', badge: null }
          ]
        },
        {
          section: 'Account',
          items: [
            { icon: 'fas fa-bell', text: 'Notifications', href: '/notifications', badge: '1' },
            { icon: 'fas fa-user', text: 'Profile', href: '/profile', badge: null },
            { icon: 'fas fa-cog', text: 'Settings', href: '/settings', badge: null }
          ]
        }
      ]
    };
  }

  async render() {
    await this.renderDesktopNavigation();
    await this.renderMobileNavigation();
    this.setupEventListeners();
    this.updateActiveRoute();
  }

  updateActiveRoute() {
    const currentRoute = window.location.hash.slice(1) || 'dashboard';
    this.currentRoute = currentRoute;

    // Update active states in both desktop and mobile navigation
    document.querySelectorAll('.nav-item').forEach(item => {
      const href = item.getAttribute('href') || item.querySelector('a')?.getAttribute('href');
      if (href) {
        const route = href.replace('#', '').replace('/', '');
        if (route === currentRoute) {
          item.classList.add('active');
        } else {
          item.classList.remove('active');
        }
      }
    });
  }

  async renderDesktopNavigation() {
    const navigationContainer = document.getElementById('navigation-container');
    if (!navigationContainer) return;

    const sidebarHTML = this.generateSidebarHTML();
    navigationContainer.innerHTML = sidebarHTML;
  }

  generateSidebarHTML() {
    const roleItems = this.navigationItems[this.currentRole] || [];

    return `
      <div class="sidebar ${this.isCollapsed ? 'collapsed' : ''}">
        <div class="sidebar-header">
          <div class="sidebar-logo">I</div>
          <div class="sidebar-title">Ikiraha</div>
        </div>

        <nav class="sidebar-nav">
          ${roleItems.map(section => this.generateSectionHTML(section)).join('')}
        </nav>

        <div class="sidebar-footer">
          <button class="nav-link" id="sidebar-toggle" title="Toggle Sidebar">
            <div class="nav-icon">
              <i class="fas fa-chevron-left"></i>
            </div>
            <span class="nav-text">Collapse</span>
          </button>
        </div>
      </div>
    `;
  }

  generateSectionHTML(section) {
    return `
      <div class="nav-section">
        <div class="nav-section-title">${section.section}</div>
        ${section.items.map(item => this.generateNavItemHTML(item)).join('')}
      </div>
    `;
  }

  generateNavItemHTML(item) {
    const isActive = window.location.pathname === item.href;
    const badgeHTML = item.badge ? `<span class="nav-badge badge badge-primary">${item.badge}</span>` : '';

    return `
      <div class="nav-item">
        <a href="${item.href}" class="nav-link ${isActive ? 'active' : ''}" data-route="${item.href}">
          <div class="nav-icon">
            <i class="${item.icon}"></i>
          </div>
          <span class="nav-text">${item.text}</span>
          ${badgeHTML}
        </a>
      </div>
    `;
  }

  async renderMobileNavigation() {
    const mobileNavContainer = document.getElementById('mobile-nav');
    if (!mobileNavContainer) return;

    const mobileNavHTML = this.generateMobileNavHTML();
    mobileNavContainer.innerHTML = mobileNavHTML;
  }

  generateMobileNavHTML() {
    const roleItems = this.navigationItems[this.currentRole] || [];
    const mainItems = roleItems.find(section => section.section === 'Main')?.items || [];

    // Show only main navigation items on mobile
    const mobileItems = mainItems.slice(0, 5); // Limit to 5 items for mobile

    return `
      <div class="mobile-nav-container">
        ${mobileItems.map(item => this.generateMobileNavItemHTML(item)).join('')}
      </div>
    `;
  }

  generateMobileNavItemHTML(item) {
    const isActive = window.location.pathname === item.href;

    return `
      <a href="${item.href}" class="mobile-nav-item ${isActive ? 'active' : ''}" data-route="${item.href}">
        <div class="mobile-nav-icon">
          <i class="${item.icon}"></i>
        </div>
        <span class="mobile-nav-text">${item.text}</span>
      </a>
    `;
  }

  setupEventListeners() {
    // Desktop navigation links
    document.querySelectorAll('.nav-link[data-route]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const route = e.currentTarget.getAttribute('data-route');
        this.navigateToRoute(route);
      });
    });

    // Mobile navigation links
    document.querySelectorAll('.mobile-nav-item[data-route]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const route = e.currentTarget.getAttribute('data-route');
        this.navigateToRoute(route);
      });
    });

    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleSidebar();
      });
    }
  }

  navigateToRoute(route) {
    // Update active states
    this.updateActiveStates(route);

    // Trigger route change (this will be handled by the router)
    if (window.app && window.app.components.router) {
      window.app.components.router.navigate(route);
    } else {
      // Fallback for direct navigation
      window.history.pushState({}, '', route);
      window.dispatchEvent(new PopStateEvent('popstate'));
    }
  }

  updateActiveStates(activeRoute) {
    // Update desktop navigation
    document.querySelectorAll('.nav-link').forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('data-route') === activeRoute) {
        link.classList.add('active');
      }
    });

    // Update mobile navigation
    document.querySelectorAll('.mobile-nav-item').forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('data-route') === activeRoute) {
        link.classList.add('active');
      }
    });
  }

  toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const toggleIcon = document.querySelector('#sidebar-toggle .nav-icon i');

    if (sidebar) {
      this.isCollapsed = !this.isCollapsed;
      sidebar.classList.toggle('collapsed', this.isCollapsed);

      if (toggleIcon) {
        toggleIcon.className = this.isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
      }

      // Save state to localStorage
      localStorage.setItem('sidebar-collapsed', this.isCollapsed.toString());
    }
  }

  updateForRole(role) {
    this.currentRole = role;
    this.render(); // Re-render navigation for new role
  }

  handleResize() {
    const isMobile = window.innerWidth <= 768;
    this.isMobile = isMobile;
    const sidebar = document.querySelector('.sidebar');
    const mobileNav = document.querySelector('.mobile-nav');
    const mainContent = document.querySelector('.main-content');

    if (isMobile) {
      if (sidebar) sidebar.style.display = 'none';
      if (mobileNav) mobileNav.style.display = 'flex';
      if (mainContent) {
        mainContent.style.marginLeft = '0';
        mainContent.style.paddingBottom = '80px'; // Space for mobile nav
      }
    } else {
      if (sidebar) sidebar.style.display = 'flex';
      if (mobileNav) mobileNav.style.display = 'none';
      if (mainContent) {
        mainContent.style.marginLeft = this.isCollapsed ? '80px' : '280px';
        mainContent.style.paddingBottom = '0';
      }
    }
  }

  // Load saved sidebar state
  loadSidebarState() {
    const savedState = localStorage.getItem('sidebar-collapsed');
    if (savedState !== null) {
      this.isCollapsed = savedState === 'true';
    }
  }
}
