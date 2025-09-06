import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../config/environment.dart';
import '../utils/api_constants.dart';

class ProfileService {
  static const String _tag = 'ProfileService';

  // Debug logging
  static void _log(String message) {
    print('[$_tag] $message');
  }

  /// Get user profile from API
  static Future<ProfileResult> getUserProfile() async {
    try {
      _log('Fetching user profile...');

      final token = await _getAccessToken();
      if (token == null) {
        return ProfileResult(
          success: false,
          message: 'No access token found',
        );
      }

      final response = await http.get(
        Uri.parse('${Environment.instance.getApiEndpoint('/auth/profile')}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      _log('Profile API response: ${response.statusCode}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final user = User.fromJson(data['data']['user']);
        return ProfileResult(
          success: true,
          message: 'Profile fetched successfully',
          user: user,
        );
      } else {
        return ProfileResult(
          success: false,
          message: data['message'] ?? 'Failed to fetch profile',
        );
      }
    } catch (e) {
      _log('Error fetching profile: $e');
      return ProfileResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  /// Update user profile
  static Future<ProfileResult> updateProfile({
    required String name,
    String? phone,
    String? address,
    String? dateOfBirth,
    String? gender,
    String? bio,
  }) async {
    try {
      _log('Updating user profile...');

      final token = await _getAccessToken();
      if (token == null) {
        return ProfileResult(
          success: false,
          message: 'No access token found',
        );
      }

      final requestBody = {
        'name': name,
        if (phone != null) 'phone': phone,
        if (address != null) 'address': address,
        if (dateOfBirth != null) 'date_of_birth': dateOfBirth,
        if (gender != null) 'gender': gender,
        if (bio != null) 'bio': bio,
      };

      _log('Update request body: $requestBody');

      final response = await http.put(
        Uri.parse('${Environment.instance.getApiEndpoint('/auth/profile')}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(requestBody),
      );

      _log('Update profile API response: ${response.statusCode}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final user = User.fromJson(data['data']['user']);
        return ProfileResult(
          success: true,
          message: data['message'] ?? 'Profile updated successfully',
          user: user,
        );
      } else {
        return ProfileResult(
          success: false,
          message: data['message'] ?? 'Failed to update profile',
        );
      }
    } catch (e) {
      _log('Error updating profile: $e');
      return ProfileResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  /// Upload profile picture
  static Future<ProfileResult> uploadProfilePicture(File imageFile) async {
    try {
      _log('Uploading profile picture...');

      final token = await _getAccessToken();
      if (token == null) {
        return ProfileResult(
          success: false,
          message: 'No access token found',
        );
      }

      var request = http.MultipartRequest(
        'POST',
        Uri.parse('${Environment.instance.getApiEndpoint('/auth/profile/upload-picture')}'),
      );

      request.headers['Authorization'] = 'Bearer $token';
      request.files.add(
        await http.MultipartFile.fromPath('profile_picture', imageFile.path),
      );

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      _log('Upload picture API response: ${response.statusCode}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final user = User.fromJson(data['data']['user']);
        return ProfileResult(
          success: true,
          message: data['message'] ?? 'Profile picture uploaded successfully',
          user: user,
        );
      } else {
        return ProfileResult(
          success: false,
          message: data['message'] ?? 'Failed to upload profile picture',
        );
      }
    } catch (e) {
      _log('Error uploading profile picture: $e');
      return ProfileResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  /// Change password
  static Future<ProfileResult> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      _log('Changing password...');

      if (newPassword != confirmPassword) {
        return ProfileResult(
          success: false,
          message: 'New password and confirmation do not match',
        );
      }

      if (newPassword.length < 6) {
        return ProfileResult(
          success: false,
          message: 'Password must be at least 6 characters long',
        );
      }

      final token = await _getAccessToken();
      if (token == null) {
        return ProfileResult(
          success: false,
          message: 'No access token found',
        );
      }

      final response = await http.put(
        Uri.parse('${Environment.instance.getApiEndpoint('/auth/change-password')}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );

      _log('Change password API response: ${response.statusCode}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return ProfileResult(
          success: true,
          message: data['message'] ?? 'Password changed successfully',
        );
      } else {
        return ProfileResult(
          success: false,
          message: data['message'] ?? 'Failed to change password',
        );
      }
    } catch (e) {
      _log('Error changing password: $e');
      return ProfileResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  /// Delete profile picture
  static Future<ProfileResult> deleteProfilePicture() async {
    try {
      _log('Deleting profile picture...');

      final token = await _getAccessToken();
      if (token == null) {
        return ProfileResult(
          success: false,
          message: 'No access token found',
        );
      }

      final response = await http.delete(
        Uri.parse('${Environment.instance.getApiEndpoint('/auth/profile/delete-picture')}'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      _log('Delete picture API response: ${response.statusCode}');

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final user = User.fromJson(data['data']['user']);
        return ProfileResult(
          success: true,
          message: data['message'] ?? 'Profile picture deleted successfully',
          user: user,
        );
      } else {
        return ProfileResult(
          success: false,
          message: data['message'] ?? 'Failed to delete profile picture',
        );
      }
    } catch (e) {
      _log('Error deleting profile picture: $e');
      return ProfileResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  /// Get access token from storage
  static Future<String?> _getAccessToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString('access_token');
    } catch (e) {
      _log('Error getting access token: $e');
      return null;
    }
  }
}

/// Result class for profile operations
class ProfileResult {
  final bool success;
  final String message;
  final User? user;
  final Map<String, dynamic>? data;

  ProfileResult({
    required this.success,
    required this.message,
    this.user,
    this.data,
  });

  @override
  String toString() {
    return 'ProfileResult(success: $success, message: $message, user: $user)';
  }
}
