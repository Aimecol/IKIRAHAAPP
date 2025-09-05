// Custom exceptions for the IKIRAHA app

class AppException implements Exception {
  final String message;
  final String? code;
  final dynamic details;

  AppException(this.message, {this.code, this.details});

  @override
  String toString() => 'AppException: $message';
}

class NetworkException extends AppException {
  NetworkException(String message, {String? code, dynamic details})
      : super(message, code: code, details: details);

  @override
  String toString() => 'NetworkException: $message';
}

class ApiException extends AppException {
  final int? statusCode;
  final Map<String, dynamic>? responseData;

  ApiException(
    String message, {
    this.statusCode,
    this.responseData,
    String? code,
    dynamic details,
  }) : super(message, code: code, details: details);

  @override
  String toString() => 'ApiException: $message (Status: $statusCode)';
}

class AuthenticationException extends AppException {
  AuthenticationException(String message, {String? code, dynamic details})
      : super(message, code: code, details: details);

  @override
  String toString() => 'AuthenticationException: $message';
}

class ValidationException extends AppException {
  final Map<String, List<String>>? fieldErrors;

  ValidationException(
    String message, {
    this.fieldErrors,
    String? code,
    dynamic details,
  }) : super(message, code: code, details: details);

  @override
  String toString() => 'ValidationException: $message';
}

class ServerException extends AppException {
  ServerException(String message, {String? code, dynamic details})
      : super(message, code: code, details: details);

  @override
  String toString() => 'ServerException: $message';
}

class TimeoutException extends AppException {
  TimeoutException(String message, {String? code, dynamic details})
      : super(message, code: code, details: details);

  @override
  String toString() => 'TimeoutException: $message';
}

class ParseException extends AppException {
  ParseException(String message, {String? code, dynamic details})
      : super(message, code: code, details: details);

  @override
  String toString() => 'ParseException: $message';
}

// Exception handler utility
class ExceptionHandler {
  static String getErrorMessage(dynamic error) {
    if (error is AppException) {
      return error.message;
    } else if (error is FormatException) {
      return 'Invalid data format received from server';
    } else if (error is TypeError) {
      return 'Data type error: Please try again or contact support';
    } else if (error.toString().contains('SocketException')) {
      return 'Network connection error. Please check your internet connection.';
    } else if (error.toString().contains('TimeoutException')) {
      return 'Request timeout. The server is taking too long to respond.';
    } else if (error.toString().contains('HandshakeException')) {
      return 'SSL/TLS connection error. Please check your network settings.';
    } else {
      return 'An unexpected error occurred: ${error.toString()}';
    }
  }

  static AppException handleHttpError(int statusCode, String? responseBody) {
    String message;
    Map<String, dynamic>? responseData;

    try {
      if (responseBody != null && responseBody.isNotEmpty) {
        responseData = Map<String, dynamic>.from(
          const JsonDecoder().convert(responseBody) as Map,
        );
        message = responseData['message'] as String? ?? 
                 responseData['error'] as String? ?? 
                 'Unknown server error';
      } else {
        message = 'Empty response from server';
      }
    } catch (e) {
      message = 'Invalid response format from server';
      responseData = null;
    }

    switch (statusCode) {
      case 400:
        return ValidationException(
          message.isEmpty ? 'Bad request' : message,
          code: 'BAD_REQUEST',
          details: responseData,
        );
      case 401:
        return AuthenticationException(
          message.isEmpty ? 'Authentication failed' : message,
          code: 'UNAUTHORIZED',
          details: responseData,
        );
      case 403:
        return AuthenticationException(
          message.isEmpty ? 'Access forbidden' : message,
          code: 'FORBIDDEN',
          details: responseData,
        );
      case 404:
        return ApiException(
          message.isEmpty ? 'Resource not found' : message,
          statusCode: statusCode,
          responseData: responseData,
          code: 'NOT_FOUND',
        );
      case 422:
        return ValidationException(
          message.isEmpty ? 'Validation failed' : message,
          code: 'VALIDATION_ERROR',
          details: responseData,
        );
      case 429:
        return ApiException(
          message.isEmpty ? 'Too many requests' : message,
          statusCode: statusCode,
          responseData: responseData,
          code: 'RATE_LIMIT',
        );
      case 500:
        return ServerException(
          message.isEmpty ? 'Internal server error' : message,
          code: 'INTERNAL_ERROR',
          details: responseData,
        );
      case 502:
        return ServerException(
          'Bad gateway - Server is temporarily unavailable',
          code: 'BAD_GATEWAY',
          details: responseData,
        );
      case 503:
        return ServerException(
          'Service unavailable - Server is temporarily down',
          code: 'SERVICE_UNAVAILABLE',
          details: responseData,
        );
      default:
        return ApiException(
          message.isEmpty ? 'HTTP error $statusCode' : message,
          statusCode: statusCode,
          responseData: responseData,
          code: 'HTTP_ERROR',
        );
    }
  }
}

// Safe JSON parsing utilities
class SafeJsonParser {
  static String? getString(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    return value?.toString();
  }

  static int? getInt(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  static double? getDouble(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  static bool? getBool(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    if (value is bool) return value;
    if (value is String) {
      return value.toLowerCase() == 'true' || value == '1';
    }
    if (value is int) return value == 1;
    return null;
  }

  static List<T>? getList<T>(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    if (value is List) {
      try {
        return value.cast<T>();
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  static Map<String, dynamic>? getMap(Map<String, dynamic>? json, String key) {
    if (json == null || !json.containsKey(key)) return null;
    final value = json[key];
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      try {
        return Map<String, dynamic>.from(value);
      } catch (e) {
        return null;
      }
    }
    return null;
  }
}
