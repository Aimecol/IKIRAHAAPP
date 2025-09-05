import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../utils/constants.dart';
import '../config/environment.dart';

class AuthService {
  // Use environment configuration for keys
  static String get _tokenKey => Environment.instance.jwtStorageKey;
  static String get _refreshTokenKey => Environment.instance.jwtRefreshKey;
  static String get _userKey => Environment.instance.userDataKey;

  // HTTP client with timeout configuration
  static http.Client get _httpClient {
    return http.Client();
  }

  // Logging helper
  static void _log(String message, {String level = 'INFO'}) {
    if (Environment.instance.enableLogging) {
      print('[$level] AuthService: $message');
    }
  }

  // API logging helper
  static void _logApiCall(String method, String url, {Map<String, dynamic>? body, int? statusCode, String? response}) {
    if (Environment.instance.enableApiLogging) {
      print('=== API Call ===');
      print('Method: $method');
      print('URL: $url');
      if (body != null) print('Body: ${jsonEncode(body)}');
      if (statusCode != null) print('Status: $statusCode');
      if (response != null) print('Response: $response');
      print('===============');
    }
  }

  // Get stored authentication token with error handling
  static Future<String?> getToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString(_tokenKey);
      _log('Token retrieved: ${token != null ? 'Found' : 'Not found'}');
      return token;
    } catch (e) {
      _log('Error retrieving token: $e', level: 'ERROR');
      return null;
    }
  }

  // Get stored refresh token with error handling
  static Future<String?> getRefreshToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final refreshToken = prefs.getString(_refreshTokenKey);
      _log('Refresh token retrieved: ${refreshToken != null ? 'Found' : 'Not found'}');
      return refreshToken;
    } catch (e) {
      _log('Error retrieving refresh token: $e', level: 'ERROR');
      return null;
    }
  }

  // Get stored user data with enhanced null safety
  static Future<User?> getUser() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userData = prefs.getString(_userKey);

      if (userData == null || userData.isEmpty) {
        _log('No user data found in storage');
        return null;
      }

      final Map<String, dynamic> userMap = jsonDecode(userData);
      if (userMap.isEmpty) {
        _log('User data is empty', level: 'WARN');
        return null;
      }

      final user = User.fromJson(userMap);
      _log('User data retrieved successfully: ${user.email}');
      return user;
    } catch (e) {
      _log('Error retrieving user data: $e', level: 'ERROR');
      return null;
    }
  }

  // Store authentication data with validation
  static Future<void> _storeAuthData(String? token, String? refreshToken, User? user) async {
    try {
      if (token == null || token.isEmpty) {
        throw Exception('Token cannot be null or empty');
      }

      if (user == null) {
        throw Exception('User data cannot be null');
      }

      final prefs = await SharedPreferences.getInstance();

      // Store token
      await prefs.setString(_tokenKey, token);
      _log('Token stored successfully');

      // Store refresh token if provided
      if (refreshToken != null && refreshToken.isNotEmpty) {
        await prefs.setString(_refreshTokenKey, refreshToken);
        _log('Refresh token stored successfully');
      }

      // Store user data
      final userJson = jsonEncode(user.toJson());
      await prefs.setString(_userKey, userJson);
      _log('User data stored successfully: ${user.email}');

    } catch (e) {
      _log('Error storing authentication data: $e', level: 'ERROR');
      rethrow;
    }
  }

  // Clear authentication data with error handling
  static Future<void> clearAuthData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_tokenKey);
      await prefs.remove(_refreshTokenKey);
      await prefs.remove(_userKey);
      _log('All authentication data cleared successfully');
    } catch (e) {
      _log('Error clearing authentication data: $e', level: 'ERROR');
      rethrow;
    }
  }

  // Check if user is authenticated
  static Future<bool> isAuthenticated() async {
    try {
      final token = await getToken();
      final isAuth = token != null && token.isNotEmpty;
      _log('Authentication check: ${isAuth ? 'Authenticated' : 'Not authenticated'}');
      return isAuth;
    } catch (e) {
      _log('Error checking authentication: $e', level: 'ERROR');
      return false;
    }
  }

  // Enhanced HTTP request helper with retry logic and proper error handling
  static Future<http.Response> _makeHttpRequest(
    String method,
    String url, {
    Map<String, String>? headers,
    Map<String, dynamic>? body,
    int? timeoutSeconds,
  }) async {
    final env = Environment.instance;
    final timeout = Duration(seconds: timeoutSeconds ?? env.apiTimeout);

    // Prepare headers
    final requestHeaders = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...?headers,
    };

    // Add authentication header if token exists
    final token = await getToken();
    if (token != null && token.isNotEmpty) {
      requestHeaders['Authorization'] = 'Bearer $token';
    }

    // Prepare body
    String? requestBody;
    if (body != null) {
      requestBody = jsonEncode(body);
    }

    _logApiCall(method, url, body: body);

    // Retry logic
    int attempts = 0;
    final maxAttempts = env.retryAttempts + 1;

    while (attempts < maxAttempts) {
      try {
        http.Response response;

        switch (method.toUpperCase()) {
          case 'GET':
            response = await _httpClient.get(
              Uri.parse(url),
              headers: requestHeaders,
            ).timeout(timeout);
            break;
          case 'POST':
            response = await _httpClient.post(
              Uri.parse(url),
              headers: requestHeaders,
              body: requestBody,
            ).timeout(timeout);
            break;
          case 'PUT':
            response = await _httpClient.put(
              Uri.parse(url),
              headers: requestHeaders,
              body: requestBody,
            ).timeout(timeout);
            break;
          case 'DELETE':
            response = await _httpClient.delete(
              Uri.parse(url),
              headers: requestHeaders,
            ).timeout(timeout);
            break;
          default:
            throw Exception('Unsupported HTTP method: $method');
        }

        _logApiCall(method, url, statusCode: response.statusCode, response: response.body);
        return response;

      } catch (e) {
        attempts++;
        _log('HTTP request attempt $attempts failed: $e', level: 'WARN');

        if (attempts >= maxAttempts) {
          _log('All HTTP request attempts failed', level: 'ERROR');

          // Determine error type and throw appropriate exception
          if (e is SocketException) {
            throw Exception('Network error: Please check your internet connection');
          } else if (e is HttpException) {
            throw Exception('HTTP error: ${e.message}');
          } else if (e.toString().contains('TimeoutException')) {
            throw Exception('Request timeout: The server is taking too long to respond');
          } else {
            throw Exception('Network request failed: ${e.toString()}');
          }
        }

        // Wait before retry
        if (attempts < maxAttempts) {
          await Future.delayed(Duration(milliseconds: env.retryDelay));
        }
      }
    }

    throw Exception('Unexpected error in HTTP request');
  }

  // Register new user
  static Future<AuthResult> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String role,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/auth/register'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'phone': phone,
          'role': role,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 201 && data['success'] == true) {
        // Registration successful, now login to get tokens
        return await login(email: email, password: password);
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Registration failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  // Enhanced login user with robust error handling
  static Future<AuthResult> login({
    required String email,
    required String password,
  }) async {
    try {
      _log('Attempting login for email: $email');

      // Validate input
      if (email.isEmpty || password.isEmpty) {
        return AuthResult(
          success: false,
          message: 'Email and password are required',
        );
      }

      // Make API request using enhanced HTTP helper
      final response = await _makeHttpRequest(
        'POST',
        Environment.instance.getApiEndpoint('/auth/login'),
        body: {
          'email': email.trim(),
          'password': password,
        },
      );

      // Parse response safely
      Map<String, dynamic>? data;
      try {
        if (response.body.isEmpty) {
          throw Exception('Empty response from server');
        }
        data = jsonDecode(response.body) as Map<String, dynamic>?;
      } catch (e) {
        _log('Failed to parse response JSON: $e', level: 'ERROR');
        return AuthResult(
          success: false,
          message: 'Invalid response format from server',
        );
      }

      if (data == null) {
        return AuthResult(
          success: false,
          message: 'No data received from server',
        );
      }

      // Handle successful response
      if (response.statusCode == 200) {
        final success = data['success'] as bool? ?? false;

        if (success) {
          try {
            // Extract data safely
            final responseData = data['data'] as Map<String, dynamic>?;
            if (responseData == null) {
              throw Exception('No data section in response');
            }

            final token = responseData['access_token']?.toString();
            final refreshToken = responseData['refresh_token']?.toString();
            final userData = responseData['user'] as Map<String, dynamic>?;

            if (token == null || token.isEmpty) {
              throw Exception('No access token received');
            }

            if (userData == null) {
              throw Exception('No user data received');
            }

            // Parse user data
            final user = User.fromJson(userData);

            // Store authentication data
            await _storeAuthData(token, refreshToken, user);

            _log('Login successful for user: ${user.email}');

            return AuthResult(
              success: true,
              message: data['message']?.toString() ?? 'Login successful',
              user: user,
              token: token,
            );

          } catch (e) {
            _log('Error processing login response: $e', level: 'ERROR');
            return AuthResult(
              success: false,
              message: 'Failed to process login response: ${e.toString()}',
            );
          }
        } else {
          // Server returned success: false
          final message = data['message']?.toString() ?? 'Login failed';
          _log('Login failed: $message', level: 'WARN');
          return AuthResult(
            success: false,
            message: message,
          );
        }
      } else {
        // Handle HTTP error status codes
        final message = data['message']?.toString() ?? 'Login failed';
        _log('Login failed with status ${response.statusCode}: $message', level: 'ERROR');

        return AuthResult(
          success: false,
          message: message,
        );
      }

    } catch (e) {
      _log('Login error: $e', level: 'ERROR');

      // Return user-friendly error message
      String errorMessage;
      if (e.toString().contains('Network error')) {
        errorMessage = 'Please check your internet connection and try again';
      } else if (e.toString().contains('timeout')) {
        errorMessage = 'Request timeout. Please try again';
      } else if (e.toString().contains('SocketException')) {
        errorMessage = 'Unable to connect to server. Please check your connection';
      } else {
        errorMessage = 'Login failed. Please try again';
      }

      return AuthResult(
        success: false,
        message: errorMessage,
      );
    }
  }

  // Logout user
  static Future<AuthResult> logout() async {
    try {
      final token = await getToken();
      if (token != null) {
        await http.post(
          Uri.parse('${ApiConstants.baseUrl}/auth/logout'),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      }
    } catch (e) {
      // Continue with logout even if API call fails
    }

    await clearAuthData();
    return AuthResult(success: true, message: 'Logged out successfully');
  }

  // Refresh authentication token
  static Future<AuthResult> refreshToken() async {
    try {
      final refreshToken = await getRefreshToken();
      if (refreshToken == null) {
        return AuthResult(success: false, message: 'No refresh token available');
      }

      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/auth/refresh'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $refreshToken',
        },
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final newToken = data['data']['access_token'];
        final newRefreshToken = data['data']['refresh_token'];
        final user = await getUser();

        if (user != null) {
          await _storeAuthData(newToken, newRefreshToken, user);
          return AuthResult(
            success: true,
            message: 'Token refreshed successfully',
            token: newToken,
            user: user,
          );
        }
      }

      // If refresh fails, clear auth data
      await clearAuthData();
      return AuthResult(success: false, message: 'Session expired');
    } catch (e) {
      await clearAuthData();
      return AuthResult(success: false, message: 'Session expired');
    }
  }

  // Get user profile
  static Future<AuthResult> getProfile() async {
    try {
      final token = await getToken();
      if (token == null) {
        return AuthResult(success: false, message: 'Not authenticated');
      }

      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/auth/profile'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final user = User.fromJson(data['data']);
        
        // Update stored user data
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_userKey, jsonEncode(user.toJson()));

        return AuthResult(
          success: true,
          message: 'Profile retrieved successfully',
          user: user,
        );
      } else if (response.statusCode == 401) {
        // Token expired, try to refresh
        final refreshResult = await refreshToken();
        if (refreshResult.success) {
          // Retry getting profile with new token
          return await getProfile();
        } else {
          return refreshResult;
        }
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Failed to get profile',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Network error: ${e.toString()}',
      );
    }
  }

  // Make authenticated HTTP request
  static Future<http.Response> authenticatedRequest(
    String method,
    String endpoint, {
    Map<String, dynamic>? body,
    Map<String, String>? additionalHeaders,
  }) async {
    final token = await getToken();
    final headers = {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
      ...?additionalHeaders,
    };

    final uri = Uri.parse('${ApiConstants.baseUrl}$endpoint');

    switch (method.toUpperCase()) {
      case 'GET':
        return await http.get(uri, headers: headers);
      case 'POST':
        return await http.post(uri, headers: headers, body: body != null ? jsonEncode(body) : null);
      case 'PUT':
        return await http.put(uri, headers: headers, body: body != null ? jsonEncode(body) : null);
      case 'DELETE':
        return await http.delete(uri, headers: headers);
      default:
        throw ArgumentError('Unsupported HTTP method: $method');
    }
  }

  // Send forgot password email
  static Future<AuthResult> forgotPassword({
    required String email,
  }) async {
    try {
      _log('Sending forgot password request for: $email');

      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/auth/forgot-password'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email.trim().toLowerCase(),
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return AuthResult(
          success: true,
          message: data['message'] ?? 'Password reset email sent successfully',
        );
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Failed to send reset email',
        );
      }

    } catch (e) {
      _log('Forgot password error: $e', level: 'ERROR');

      String errorMessage;
      if (e.toString().contains('Network error')) {
        errorMessage = 'Please check your internet connection and try again';
      } else if (e.toString().contains('timeout')) {
        errorMessage = 'Request timeout. Please try again';
      } else {
        errorMessage = 'Failed to send reset email. Please try again';
      }

      return AuthResult(
        success: false,
        message: errorMessage,
      );
    }
  }

  // Reset password with token
  static Future<AuthResult> resetPassword({
    required String token,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      _log('Resetting password with token');

      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/auth/reset-password'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'token': token,
          'password': password,
          'password_confirmation': passwordConfirmation,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return AuthResult(
          success: true,
          message: data['message'] ?? 'Password reset successfully',
        );
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Failed to reset password',
        );
      }

    } catch (e) {
      _log('Reset password error: $e', level: 'ERROR');

      String errorMessage;
      if (e.toString().contains('Network error')) {
        errorMessage = 'Please check your internet connection and try again';
      } else if (e.toString().contains('timeout')) {
        errorMessage = 'Request timeout. Please try again';
      } else {
        errorMessage = 'Failed to reset password. Please try again';
      }

      return AuthResult(
        success: false,
        message: errorMessage,
      );
    }
  }

  // Validate reset token
  static Future<AuthResult> validateResetToken(String token) async {
    try {
      _log('Validating reset token');

      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/auth/validate-reset-token/$token'),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return AuthResult(
          success: true,
          message: data['message'] ?? 'Token is valid',
          data: data['data'],
        );
      } else {
        return AuthResult(
          success: false,
          message: data['message'] ?? 'Invalid or expired token',
        );
      }

    } catch (e) {
      _log('Token validation error: $e', level: 'ERROR');

      String errorMessage;
      if (e.toString().contains('Network error')) {
        errorMessage = 'Please check your internet connection and try again';
      } else if (e.toString().contains('timeout')) {
        errorMessage = 'Request timeout. Please try again';
      } else {
        errorMessage = 'Failed to validate token. Please try again';
      }

      return AuthResult(
        success: false,
        message: errorMessage,
      );
    }
  }
}

class AuthResult {
  final bool success;
  final String message;
  final User? user;
  final String? token;
  final Map<String, dynamic>? data;

  AuthResult({
    required this.success,
    required this.message,
    this.user,
    this.token,
    this.data,
  });
}
