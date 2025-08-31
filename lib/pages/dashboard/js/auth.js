// Authentication Manager - Handle user authentication and roles
class AuthManager {
  constructor() {
    this.currentUser = null;
    this.currentRole = null;
    this.isAuthenticated = false;
    this.mockUsers = this.getMockUsers();
  }

  getMockUsers() {
    return [
      {
        id: 1,
        email: 'admin@ikiraha.com',
        password: 'admin123',
        name: 'Super Admin',
        role: 'super_admin',
        avatar: 'https://via.placeholder.com/64x64/007AFF/FFFFFF?text=SA',
        permissions: ['all']
      },
      {
        id: 2,
        email: 'merchant@ikiraha.com',
        password: 'merchant123',
        name: 'Restaurant Owner',
        role: 'merchant',
        avatar: 'https://via.placeholder.com/64x64/34C759/FFFFFF?text=RO',
        permissions: ['products', 'orders', 'analytics', 'profile']
      },
      {
        id: 3,
        email: 'accountant@ikiraha.com',
        password: 'accountant123',
        name: 'Financial Manager',
        role: 'accountant',
        avatar: 'https://via.placeholder.com/64x64/FF9500/FFFFFF?text=FM',
        permissions: ['transactions', 'reports', 'analytics', 'profile']
      }
    ];
  }

  async checkAuth() {
    // Check if user is stored in localStorage (mock session)
    const storedUser = localStorage.getItem('dashboard-user');
    const storedRole = localStorage.getItem('dashboard-role');

    if (storedUser && storedRole) {
      try {
        this.currentUser = JSON.parse(storedUser);
        this.currentRole = storedRole;
        this.isAuthenticated = true;
        return true;
      } catch (error) {
        console.error('Error parsing stored user:', error);
        this.clearAuth();
        return false;
      }
    }

    return false;
  }

  async login(email, password) {
    // Mock authentication - in real app, this would call an API
    const user = this.mockUsers.find(u => u.email === email && u.password === password);

    if (user) {
      // Remove password from stored user object
      const { password: _, ...userWithoutPassword } = user;

      this.currentUser = userWithoutPassword;
      this.currentRole = user.role;
      this.isAuthenticated = true;

      // Store in localStorage (mock session)
      localStorage.setItem('dashboard-user', JSON.stringify(userWithoutPassword));
      localStorage.setItem('dashboard-role', user.role);

      return {
        success: true,
        user: userWithoutPassword,
        role: user.role
      };
    }

    return {
      success: false,
      error: 'Invalid email or password'
    };
  }

  async logout() {
    this.currentUser = null;
    this.currentRole = null;
    this.isAuthenticated = false;

    // Clear localStorage
    localStorage.removeItem('dashboard-user');
    localStorage.removeItem('dashboard-role');

    return { success: true };
  }

  getCurrentUser() {
    return this.currentUser;
  }

  getCurrentRole() {
    return this.currentRole;
  }

  isLoggedIn() {
    return this.isAuthenticated;
  }

  hasPermission(permission) {
    if (!this.currentUser) return false;

    // Super admin has all permissions
    if (this.currentRole === 'super_admin') return true;

    return this.currentUser.permissions.includes(permission);
  }

  hasRole(role) {
    return this.currentRole === role;
  }

  hasAnyRole(roles) {
    return roles.includes(this.currentRole);
  }

  // Mock role switching for demo purposes
  async switchRole(newRole) {
    const validRoles = ['super_admin', 'merchant', 'accountant'];

    if (!validRoles.includes(newRole)) {
      return { success: false, error: 'Invalid role' };
    }

    // Find mock user with the new role
    const mockUser = this.mockUsers.find(u => u.role === newRole);

    if (mockUser) {
      const { password: _, ...userWithoutPassword } = mockUser;

      this.currentUser = userWithoutPassword;
      this.currentRole = newRole;

      // Update localStorage
      localStorage.setItem('dashboard-user', JSON.stringify(userWithoutPassword));
      localStorage.setItem('dashboard-role', newRole);

      return {
        success: true,
        user: userWithoutPassword,
        role: newRole
      };
    }

    return { success: false, error: 'Role not found' };
  }

  clearAuth() {
    this.currentUser = null;
    this.currentRole = null;
    this.isAuthenticated = false;
    localStorage.removeItem('dashboard-user');
    localStorage.removeItem('dashboard-role');
  }

  // Get available roles for demo
  getAvailableRoles() {
    return [
      { value: 'super_admin', label: 'Super Admin', description: 'Full system access' },
      { value: 'merchant', label: 'Merchant', description: 'Restaurant/business owner' },
      { value: 'accountant', label: 'Accountant', description: 'Financial management' }
    ];
  }

  // Mock password reset
  async requestPasswordReset(email) {
    const user = this.mockUsers.find(u => u.email === email);

    if (user) {
      // In real app, this would send an email
      console.log(`Password reset email sent to ${email}`);
      return { success: true, message: 'Password reset email sent' };
    }

    return { success: false, error: 'Email not found' };
  }

  // Mock user profile update
  async updateProfile(updates) {
    if (!this.currentUser) {
      return { success: false, error: 'Not authenticated' };
    }

    // Update current user
    this.currentUser = { ...this.currentUser, ...updates };

    // Update localStorage
    localStorage.setItem('dashboard-user', JSON.stringify(this.currentUser));

    return { success: true, user: this.currentUser };
  }
}
