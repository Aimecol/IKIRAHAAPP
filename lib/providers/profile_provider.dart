import 'dart:io';
import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import '../services/profile_service.dart';

class ProfileProvider with ChangeNotifier {
  User? _user;
  bool _isLoading = false;
  String? _error;

  // Getters
  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasError => _error != null;

  // Private methods for state management
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String? error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
  }

  void _setUser(User? user) {
    _user = user;
    notifyListeners();
  }

  /// Initialize profile data
  Future<void> initializeProfile() async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.getUserProfile();
      
      if (result.success && result.user != null) {
        _setUser(result.user);
      } else {
        _setError(result.message);
      }
    } catch (e) {
      _setError('Failed to initialize profile: ${e.toString()}');
    } finally {
      _setLoading(false);
    }
  }

  /// Refresh profile data
  Future<bool> refreshProfile() async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.getUserProfile();
      
      if (result.success && result.user != null) {
        _setUser(result.user);
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to refresh profile: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Update profile information
  Future<bool> updateProfile({
    required String name,
    String? phone,
    String? address,
    String? dateOfBirth,
    String? gender,
    String? bio,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.updateProfile(
        name: name,
        phone: phone,
        address: address,
        dateOfBirth: dateOfBirth,
        gender: gender,
        bio: bio,
      );
      
      if (result.success && result.user != null) {
        _setUser(result.user);
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to update profile: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Upload profile picture
  Future<bool> uploadProfilePicture(File imageFile) async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.uploadProfilePicture(imageFile);
      
      if (result.success && result.user != null) {
        _setUser(result.user);
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to upload profile picture: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Delete profile picture
  Future<bool> deleteProfilePicture() async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.deleteProfilePicture();
      
      if (result.success && result.user != null) {
        _setUser(result.user);
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to delete profile picture: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Change password
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final result = await ProfileService.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        confirmPassword: confirmPassword,
      );
      
      if (result.success) {
        // Password changed successfully, no need to update user data
        return true;
      } else {
        _setError(result.message);
        return false;
      }
    } catch (e) {
      _setError('Failed to change password: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  /// Update user data from external source (e.g., AuthProvider)
  void updateUserData(User user) {
    _setUser(user);
  }

  /// Clear profile data (e.g., on logout)
  void clearProfile() {
    _user = null;
    _error = null;
    _isLoading = false;
    notifyListeners();
  }

  /// Get user's display name
  String get displayName {
    if (_user == null) return 'Guest';
    return _user!.name.isNotEmpty ? _user!.name : 'User';
  }

  /// Get user's initials for avatar
  String get userInitials {
    if (_user == null || _user!.name.isEmpty) return 'U';
    
    final nameParts = _user!.name.trim().split(' ');
    if (nameParts.length >= 2) {
      return '${nameParts[0][0]}${nameParts[1][0]}'.toUpperCase();
    } else {
      return nameParts[0][0].toUpperCase();
    }
  }

  /// Check if profile is complete
  bool get isProfileComplete {
    if (_user == null) return false;
    
    return _user!.name.isNotEmpty &&
           _user!.email.isNotEmpty &&
           _user!.phone != null &&
           _user!.phone!.isNotEmpty;
  }

  /// Get profile completion percentage
  double get profileCompletionPercentage {
    if (_user == null) return 0.0;
    
    int completedFields = 0;
    int totalFields = 7; // name, email, phone, address, dateOfBirth, gender, bio
    
    if (_user!.name.isNotEmpty) completedFields++;
    if (_user!.email.isNotEmpty) completedFields++;
    if (_user!.phone != null && _user!.phone!.isNotEmpty) completedFields++;
    if (_user!.address != null && _user!.address!.isNotEmpty) completedFields++;
    if (_user!.dateOfBirth != null && _user!.dateOfBirth!.isNotEmpty) completedFields++;
    if (_user!.gender != null && _user!.gender!.isNotEmpty) completedFields++;
    if (_user!.bio != null && _user!.bio!.isNotEmpty) completedFields++;
    
    return completedFields / totalFields;
  }
}
