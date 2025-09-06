/**
 * API Constants for IKIRAHA Flutter App
 * Contains all API endpoint URLs and configuration
 */

class ApiConstants {
  // Base URL for the API
  static const String baseUrl = 'http://localhost/ikirahaapp/ikiraha-api/public';
  
  // Authentication endpoints
  static const String loginEndpoint = '$baseUrl/auth/login';
  static const String registerEndpoint = '$baseUrl/auth/register';
  static const String logoutEndpoint = '$baseUrl/auth/logout';
  static const String refreshTokenEndpoint = '$baseUrl/auth/refresh';
  static const String changePasswordEndpoint = '$baseUrl/auth/change-password';
  static const String forgotPasswordEndpoint = '$baseUrl/auth/forgot-password';
  static const String resetPasswordEndpoint = '$baseUrl/auth/reset-password';
  
  // Profile endpoints
  static const String profileEndpoint = '$baseUrl/auth/profile';
  static const String uploadProfilePictureEndpoint = '$baseUrl/auth/profile/upload-picture';
  static const String deleteProfilePictureEndpoint = '$baseUrl/auth/profile/delete-picture';
  
  // HTTP Headers
  static const Map<String, String> defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
  
  // Request timeout
  static const Duration requestTimeout = Duration(seconds: 30);
  
  // File upload limits
  static const int maxFileSize = 5 * 1024 * 1024; // 5MB
  static const List<String> allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
}
