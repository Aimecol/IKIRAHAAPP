// Simple Router for SPA navigation
class Router {
  constructor() {
    this.routes = new Map();
    this.currentRoute = null;
    this.isStarted = false;
  }

  addRoute(path, handler) {
    this.routes.set(path, handler);
  }

  removeRoute(path) {
    this.routes.delete(path);
  }

  async start() {
    if (this.isStarted) return;

    this.isStarted = true;

    // Listen for browser navigation
    window.addEventListener('popstate', () => {
      this.handleRoute();
    });

    // Handle initial route
    await this.handleRoute();
  }

  stop() {
    this.isStarted = false;
    window.removeEventListener('popstate', this.handleRoute);
  }

  async navigate(path, replace = false) {
    if (!this.isStarted) {
      console.warn('Router not started');
      return;
    }

    // Update browser history
    if (replace) {
      window.history.replaceState({}, '', path);
    } else {
      window.history.pushState({}, '', path);
    }

    // Handle the route
    await this.handleRoute();
  }

  async handleRoute() {
    const path = window.location.pathname;
    const handler = this.findRouteHandler(path);

    if (handler) {
      this.currentRoute = path;
      try {
        await handler(path);
      } catch (error) {
        console.error('Route handler error:', error);
        this.handleError(error);
      }
    } else {
      // Handle 404
      this.handle404(path);
    }
  }

  findRouteHandler(path) {
    // Exact match first
    if (this.routes.has(path)) {
      return this.routes.get(path);
    }

    // Check for wildcard route
    if (this.routes.has('*')) {
      return this.routes.get('*');
    }

    // Check for parameterized routes (simple implementation)
    for (const [routePath, handler] of this.routes) {
      if (this.matchRoute(routePath, path)) {
        return handler;
      }
    }

    return null;
  }

  matchRoute(routePath, actualPath) {
    // Simple pattern matching for routes like /users/:id
    const routeParts = routePath.split('/');
    const actualParts = actualPath.split('/');

    if (routeParts.length !== actualParts.length) {
      return false;
    }

    for (let i = 0; i < routeParts.length; i++) {
      const routePart = routeParts[i];
      const actualPart = actualParts[i];

      if (routePart.startsWith(':')) {
        // Parameter - matches anything
        continue;
      } else if (routePart !== actualPart) {
        return false;
      }
    }

    return true;
  }

  getRouteParams(routePath, actualPath) {
    const params = {};
    const routeParts = routePath.split('/');
    const actualParts = actualPath.split('/');

    for (let i = 0; i < routeParts.length; i++) {
      const routePart = routeParts[i];
      const actualPart = actualParts[i];

      if (routePart.startsWith(':')) {
        const paramName = routePart.substring(1);
        params[paramName] = actualPart;
      }
    }

    return params;
  }

  handle404(path) {
    console.warn('Route not found:', path);

    // Try to find and execute 404 handler
    const notFoundHandler = this.routes.get('*');
    if (notFoundHandler) {
      notFoundHandler(path);
    } else {
      // Default 404 handling
      this.showDefaultNotFound();
    }
  }

  handleError(error) {
    console.error('Router error:', error);

    // Show error page or toast
    if (window.app && window.app.components.toast) {
      window.app.components.toast.error('Navigation error occurred');
    }
  }

  showDefaultNotFound() {
    const contentContainer = document.getElementById('page-content');
    if (contentContainer) {
      contentContainer.innerHTML = `
        <div class="error-page">
          <div class="error-content">
            <h1>404</h1>
            <h2>Page Not Found</h2>
            <p>The page you're looking for doesn't exist.</p>
            <button class="btn btn-primary" onclick="window.app.components.router.navigate('/dashboard')">
              <i class="fas fa-home"></i>
              Go to Dashboard
            </button>
          </div>
        </div>
      `;
    }
  }

  getCurrentRoute() {
    return this.currentRoute;
  }

  getQueryParams() {
    const params = new URLSearchParams(window.location.search);
    const result = {};

    for (const [key, value] of params) {
      result[key] = value;
    }

    return result;
  }

  setQueryParam(key, value) {
    const url = new URL(window.location);
    url.searchParams.set(key, value);
    window.history.replaceState({}, '', url);
  }

  removeQueryParam(key) {
    const url = new URL(window.location);
    url.searchParams.delete(key);
    window.history.replaceState({}, '', url);
  }

  // Utility method to check if current route matches pattern
  isCurrentRoute(pattern) {
    const currentPath = window.location.pathname;

    if (pattern === currentPath) {
      return true;
    }

    return this.matchRoute(pattern, currentPath);
  }
}
