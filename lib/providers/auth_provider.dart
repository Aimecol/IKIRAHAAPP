import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _isAuthenticated = false;
  bool _isLoading = false;
  String? _error;

  // Getters
  User? get user => _user;
  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // User role getters
  bool get isClient => _user?.role.isClient ?? false;
  bool get isMerchant => _user?.role.isMerchant ?? false;
  bool get isAccountant => _user?.role.isAccountant ?? false;
  bool get isSuperAdmin => _user?.role.isSuperAdmin ?? false;

  // Permission getters
  bool get canPlaceOrders => _user?.role.canPlaceOrders ?? false;
  bool get canManageRestaurants => _user?.role.canManageRestaurants ?? false;
  bool get canViewFinancials => _user?.role.canViewFinancials ?? false;
  bool get canManageUsers => _user?.role.canManageUsers ?? false;

  AuthProvider() {
    // Don't auto-initialize authentication - start fresh with login screen
    _isLoading = false;
  }

  // Initialize authentication state (called manually when needed)
  Future<void> initializeAuth() async {
    _setLoading(true);

    try {
      final isAuth = await AuthService.isAuthenticated();
      if (isAuth) {
        final user = await AuthService.getUser();
        if (user != null) {
          _user = user;
          _isAuthenticated = true;

          // Verify token is still valid by getting fresh profile
          await refreshProfile();
        }
      }
    } catch (e) {
      _setError('Failed to initialize authentication: ${e.toString()}');
    } finally {
      _setLoading(false);
    }
  }

  // Login
  Future<bool> login(String email, String password) async {
    _setLoading(true);
    _clearError();

    try {
      final result = await AuthService.login(email: email, password: password);
      
      if (result.success && result.user != null) {
        _user = result.user;
        _isAuthenticated = true;
        notifyListeners();
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Login failed: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Register
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String role,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final result = await AuthService.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
        role: role,
      );
      
      if (result.success && result.user != null) {
        _user = result.user;
        _isAuthenticated = true;
        notifyListeners();
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Registration failed: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Logout
  Future<void> logout() async {
    _setLoading(true);
    
    try {
      await AuthService.logout();
    } catch (e) {
      // Continue with logout even if API call fails
      debugPrint('Logout API call failed: ${e.toString()}');
    }
    
    _user = null;
    _isAuthenticated = false;
    _clearError();
    _setLoading(false);
  }

  // Refresh user profile
  Future<bool> refreshProfile() async {
    if (!_isAuthenticated) return false;

    try {
      final result = await AuthService.getProfile();
      
      if (result.success && result.user != null) {
        _user = result.user;
        notifyListeners();
        return true;
      } else {
        // If profile refresh fails, user might be logged out
        if (result.message.toLowerCase().contains('session expired') ||
            result.message.toLowerCase().contains('not authenticated')) {
          await logout();
        }
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to refresh profile: ${e.toString()}');
      return false;
    }
  }

  // Update user profile
  Future<bool> updateProfile(Map<String, dynamic> updates) async {
    if (!_isAuthenticated) return false;

    _setLoading(true);
    _clearError();

    try {
      // Make API call to update profile
      final response = await AuthService.authenticatedRequest(
        'PUT',
        '/auth/profile',
        body: updates,
      );

      final data = response.body;
      if (response.statusCode == 200) {
        // Refresh profile to get updated data
        await refreshProfile();
        return true;
      } else {
        _setError('Failed to update profile');
        return false;
      }
    } catch (e) {
      _setError('Profile update failed: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Change password
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    if (!_isAuthenticated) return false;

    _setLoading(true);
    _clearError();

    try {
      final response = await AuthService.authenticatedRequest(
        'POST',
        '/auth/change-password',
        body: {
          'current_password': currentPassword,
          'new_password': newPassword,
        },
      );

      if (response.statusCode == 200) {
        return true;
      } else {
        _setError('Failed to change password');
        return false;
      }
    } catch (e) {
      _setError('Password change failed: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Check if user has specific permission
  bool hasPermission(String permission) {
    if (_user == null) return false;

    switch (permission.toLowerCase()) {
      case 'place_orders':
        return canPlaceOrders;
      case 'manage_restaurants':
        return canManageRestaurants;
      case 'view_financials':
        return canViewFinancials;
      case 'manage_users':
        return canManageUsers;
      default:
        return false;
    }
  }

  // Check if user has any of the specified roles
  bool hasAnyRole(List<UserRole> roles) {
    if (_user == null) return false;
    return roles.contains(_user!.role);
  }

  // Check if user has specific role
  bool hasRole(UserRole role) {
    if (_user == null) return false;
    return _user!.role == role;
  }

  // Get user display name
  String get displayName {
    if (_user == null) return 'Guest';
    return _user!.name.isNotEmpty ? _user!.name : _user!.email;
  }

  // Get user initials for avatar
  String get userInitials {
    if (_user == null) return 'G';
    final name = _user!.name.trim();
    if (name.isEmpty) return _user!.email.substring(0, 1).toUpperCase();
    
    final parts = name.split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    } else {
      return parts[0].substring(0, 1).toUpperCase();
    }
  }

  // Private helper methods
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
    notifyListeners();
  }

  // Clear all data (useful for testing or complete reset)
  void clear() {
    _user = null;
    _isAuthenticated = false;
    _isLoading = false;
    _error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    super.dispose();
  }
}
