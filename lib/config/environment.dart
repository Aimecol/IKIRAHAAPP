import 'package:flutter_dotenv/flutter_dotenv.dart';

class Environment {
  // Private constructor
  Environment._();
  
  // Singleton instance
  static final Environment _instance = Environment._();
  static Environment get instance => _instance;
  
  // Initialize environment
  static Future<void> initialize() async {
    try {
      await dotenv.load(fileName: ".env");
    } catch (e) {
      // If .env file is not found, use default values
      print('Warning: .env file not found, using default configuration');
    }
  }
  
  // App Configuration
  String get appEnv => dotenv.env['APP_ENV'] ?? 'development';
  String get appName => dotenv.env['APP_NAME'] ?? 'IKIRAHA';
  String get appVersion => dotenv.env['APP_VERSION'] ?? '1.0.0';
  
  // API Configuration
  String get apiBaseUrl => dotenv.env['API_BASE_URL'] ?? 'http://localhost/ikirahaapp/ikiraha-api/public';
  int get apiTimeout => int.tryParse(dotenv.env['API_TIMEOUT'] ?? '30') ?? 30;
  
  // Authentication Configuration
  String get jwtStorageKey => dotenv.env['JWT_STORAGE_KEY'] ?? 'ikiraha_auth_token';
  String get jwtRefreshKey => dotenv.env['JWT_REFRESH_KEY'] ?? 'ikiraha_refresh_token';
  String get userDataKey => dotenv.env['USER_DATA_KEY'] ?? 'ikiraha_user_data';
  
  // App Configuration
  String get defaultLanguage => dotenv.env['DEFAULT_LANGUAGE'] ?? 'en';
  String get defaultCurrency => dotenv.env['DEFAULT_CURRENCY'] ?? 'RWF';
  String get defaultCountryCode => dotenv.env['DEFAULT_COUNTRY_CODE'] ?? '+250';
  
  // Debug Configuration
  bool get enableLogging => dotenv.env['ENABLE_LOGGING']?.toLowerCase() == 'true';
  String get logLevel => dotenv.env['LOG_LEVEL'] ?? 'info';
  bool get enableApiLogging => dotenv.env['ENABLE_API_LOGGING']?.toLowerCase() == 'true';
  
  // Feature Flags
  bool get enableBiometricAuth => dotenv.env['ENABLE_BIOMETRIC_AUTH']?.toLowerCase() == 'true';
  bool get enableSocialLogin => dotenv.env['ENABLE_SOCIAL_LOGIN']?.toLowerCase() == 'true';
  bool get enablePushNotifications => dotenv.env['ENABLE_PUSH_NOTIFICATIONS']?.toLowerCase() == 'true';
  bool get enableAnalytics => dotenv.env['ENABLE_ANALYTICS']?.toLowerCase() == 'true';
  
  // Network Configuration
  int get retryAttempts => int.tryParse(dotenv.env['RETRY_ATTEMPTS'] ?? '3') ?? 3;
  int get retryDelay => int.tryParse(dotenv.env['RETRY_DELAY'] ?? '1000') ?? 1000;
  int get connectionTimeout => int.tryParse(dotenv.env['CONNECTION_TIMEOUT'] ?? '10000') ?? 10000;
  int get receiveTimeout => int.tryParse(dotenv.env['RECEIVE_TIMEOUT'] ?? '30000') ?? 30000;
  
  // Cache Configuration
  int get cacheDuration => int.tryParse(dotenv.env['CACHE_DURATION'] ?? '300000') ?? 300000;
  bool get enableOfflineMode => dotenv.env['ENABLE_OFFLINE_MODE']?.toLowerCase() == 'true';
  
  // Security Configuration
  bool get enableCertificatePinning => dotenv.env['ENABLE_CERTIFICATE_PINNING']?.toLowerCase() == 'true';
  bool get enableRootDetection => dotenv.env['ENABLE_ROOT_DETECTION']?.toLowerCase() == 'true';
  
  // UI Configuration
  String get themeMode => dotenv.env['THEME_MODE'] ?? 'light';
  int get primaryColor => int.tryParse(dotenv.env['PRIMARY_COLOR'] ?? '0xFF5722') ?? 0xFF5722;
  int get secondaryColor => int.tryParse(dotenv.env['SECONDARY_COLOR'] ?? '0xFF9800') ?? 0xFF9800;
  
  // Development Tools
  bool get enableDevTools => dotenv.env['ENABLE_DEV_TOOLS']?.toLowerCase() == 'true';
  bool get showDebugBanner => dotenv.env['SHOW_DEBUG_BANNER']?.toLowerCase() == 'true';
  bool get enableHotReload => dotenv.env['ENABLE_HOT_RELOAD']?.toLowerCase() == 'true';
  
  // Environment checks
  bool get isDevelopment => appEnv == 'development';
  bool get isStaging => appEnv == 'staging';
  bool get isProduction => appEnv == 'production';
  
  // Get all environment variables as a map (for debugging)
  Map<String, String> get allEnvVars => Map<String, String>.from(dotenv.env);
  
  // Print environment configuration (for debugging)
  void printConfig() {
    if (enableLogging) {
      print('=== IKIRAHA Environment Configuration ===');
      print('Environment: $appEnv');
      print('App Name: $appName');
      print('App Version: $appVersion');
      print('API Base URL: $apiBaseUrl');
      print('API Timeout: ${apiTimeout}s');
      print('Enable Logging: $enableLogging');
      print('Enable API Logging: $enableApiLogging');
      print('Default Language: $defaultLanguage');
      print('Default Currency: $defaultCurrency');
      print('Default Country Code: $defaultCountryCode');
      print('==========================================');
    }
  }
  
  // Validate configuration
  bool validateConfig() {
    final errors = <String>[];
    
    // Validate API URL
    if (apiBaseUrl.isEmpty) {
      errors.add('API_BASE_URL is required');
    }
    
    // Validate timeout values
    if (apiTimeout <= 0) {
      errors.add('API_TIMEOUT must be greater than 0');
    }
    
    if (connectionTimeout <= 0) {
      errors.add('CONNECTION_TIMEOUT must be greater than 0');
    }
    
    if (receiveTimeout <= 0) {
      errors.add('RECEIVE_TIMEOUT must be greater than 0');
    }
    
    // Validate retry configuration
    if (retryAttempts < 0) {
      errors.add('RETRY_ATTEMPTS must be 0 or greater');
    }
    
    if (retryDelay < 0) {
      errors.add('RETRY_DELAY must be 0 or greater');
    }
    
    if (errors.isNotEmpty) {
      print('Environment Configuration Errors:');
      for (final error in errors) {
        print('- $error');
      }
      return false;
    }
    
    return true;
  }
  
  // Get API endpoint URL
  String getApiEndpoint(String endpoint) {
    final baseUrl = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    final cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/$endpoint';
    return '$baseUrl$cleanEndpoint';
  }
  
  // Get full API URL with query parameters
  String getApiUrl(String endpoint, {Map<String, dynamic>? queryParams}) {
    String url = getApiEndpoint(endpoint);
    
    if (queryParams != null && queryParams.isNotEmpty) {
      final queryString = queryParams.entries
          .where((entry) => entry.value != null)
          .map((entry) => '${entry.key}=${Uri.encodeComponent(entry.value.toString())}')
          .join('&');
      
      if (queryString.isNotEmpty) {
        url += '?$queryString';
      }
    }
    
    return url;
  }
}
