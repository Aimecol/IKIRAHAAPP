# IKIRAHA Flutter Authentication Implementation

This document provides a comprehensive guide to the Flutter authentication screens and components created for the IKIRAHA food delivery app.

## 📱 Overview

The Flutter authentication system provides:
- **Login Screen** with email/password authentication
- **Registration Screen** with role selection (Client/Merchant)
- **JWT Token Management** with automatic refresh
- **Role-based Navigation** to appropriate dashboards
- **State Management** using Provider pattern
- **Professional UI Design** consistent with food delivery apps
- **Comprehensive Form Validation**
- **Error Handling** with user-friendly messages

## 🏗 Architecture

### File Structure
```
lib/
├── models/
│   └── user_model.dart              # User data models and enums
├── services/
│   └── auth_service.dart            # API authentication service
├── providers/
│   └── auth_provider.dart           # State management for auth
├── screens/
│   ├── auth/
│   │   ├── login_screen.dart        # Login screen UI
│   │   └── register_screen.dart     # Registration screen UI
│   └── splash_screen.dart           # App initialization screen
├── widgets/
│   ├── custom_button.dart           # Reusable button components
│   ├── custom_text_field.dart       # Reusable input components
│   └── loading_overlay.dart         # Loading states
├── utils/
│   ├── constants.dart               # App constants and theme
│   └── validators.dart              # Form validation logic
└── main.dart                        # App entry point
```

## 🔐 Authentication Features

### Login Screen
- **Email/Password Authentication**
- **Remember Me** functionality
- **Forgot Password** link (placeholder)
- **Demo Account Information** display
- **Professional UI** with animations
- **Form Validation** with real-time feedback
- **Loading States** during authentication

### Registration Screen
- **Role Selection** (Client/Merchant)
- **Complete Form Validation**:
  - Full name (2-100 characters, letters only)
  - Email (valid format)
  - Phone (Rwanda format: +250XXXXXXXXX)
  - Password (min 6 chars, letter + number)
  - Confirm password matching
- **Terms & Conditions** acceptance
- **Automatic Login** after successful registration

### User Roles & Permissions
```dart
enum UserRole {
  client,        // Can place orders, manage favorites
  merchant,      // Can manage restaurants, process orders
  accountant,    // Can view financials, generate reports
  superAdmin,    // Full system access
}
```

## 🛠 Technical Implementation

### State Management (Provider)
```dart
// Usage in widgets
Consumer<AuthProvider>(
  builder: (context, authProvider, child) {
    if (authProvider.isAuthenticated) {
      return HomeScreen();
    }
    return LoginScreen();
  },
)

// Access user data
final user = context.read<AuthProvider>().user;
final isClient = context.read<AuthProvider>().isClient;
```

### API Integration
```dart
// Login
final result = await AuthService.login(
  email: email,
  password: password,
);

// Register
final result = await AuthService.register(
  name: name,
  email: email,
  password: password,
  phone: phone,
  role: role,
);
```

### JWT Token Management
- **Automatic Storage** in SharedPreferences
- **Token Refresh** when expired
- **Secure Headers** for API requests
- **Session Management** with logout

## 🎨 UI/UX Design

### Design System
- **Primary Color**: Green (#2E7D32) - Food/Nature theme
- **Secondary Color**: Orange (#FF6F00) - Appetite stimulating
- **Typography**: Roboto font family
- **Consistent Spacing**: 8px grid system
- **Border Radius**: 12px for modern look
- **Shadows**: Subtle elevation for depth

### Components
- **CustomTextField**: Reusable input with validation
- **CustomButton**: Multiple variants (filled, outline, text)
- **LoadingOverlay**: Professional loading states
- **Form Validation**: Real-time feedback

### Animations
- **Splash Screen**: Logo scale and fade animations
- **Form Focus**: Smooth color transitions
- **Loading States**: Circular progress indicators
- **Navigation**: Smooth screen transitions

## 📝 Form Validation

### Email Validation
```dart
static String? validateEmail(String? value) {
  if (value == null || value.isEmpty) {
    return 'Email is required';
  }
  if (!RegExp(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$').hasMatch(value)) {
    return 'Please enter a valid email address';
  }
  return null;
}
```

### Phone Validation (Rwanda Format)
```dart
static String? validatePhone(String? value) {
  if (value == null || value.isEmpty) {
    return 'Phone number is required';
  }
  String cleanPhone = value.replaceAll(RegExp(r'[\s\-]'), '');
  if (!RegExp(r'^(\+250|250)?[0-9]{9}$').hasMatch(cleanPhone)) {
    return 'Please enter a valid Rwanda phone number';
  }
  return null;
}
```

### Password Validation
```dart
static String? validatePassword(String? value) {
  if (value == null || value.isEmpty) {
    return 'Password is required';
  }
  if (value.length < 6) {
    return 'Password must be at least 6 characters';
  }
  if (!RegExp(r'^(?=.*[A-Za-z])(?=.*\d)').hasMatch(value)) {
    return 'Password must contain at least one letter and one number';
  }
  return null;
}
```

## 🔄 Navigation Flow

```
Splash Screen
     ↓
Authentication Check
     ↓
┌─────────────────┐    ┌──────────────────┐
│   Not Logged    │    │    Logged In     │
│       In        │    │                  │
│   Login Screen  │    │  Role-based      │
│       ↓         │    │  Navigation:     │
│ Register Screen │    │  • Client Home   │
└─────────────────┘    │  • Merchant Home │
                       │  • Accountant    │
                       │  • Admin Panel   │
                       └──────────────────┘
```

## 🚀 Getting Started

### 1. Dependencies
Add to `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  provider: ^6.1.1
  http: ^1.1.0
  shared_preferences: ^2.2.2
  cupertino_icons: ^1.0.6
  intl: ^0.19.0
```

### 2. API Configuration
Update `lib/utils/constants.dart`:
```dart
class ApiConstants {
  static const String baseUrl = 'http://your-api-domain.com/ikiraha-api/public';
  // For local development:
  // static const String baseUrl = 'http://localhost/ikirahaapp/ikiraha-api/public';
}
```

### 3. Initialize App
```dart
void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: const IkirahaApp(),
    ),
  );
}
```

### 4. Test Accounts
Use these demo accounts for testing:
- **Client**: client@ikiraha.com / password
- **Merchant**: merchant@ikiraha.com / password
- **Accountant**: accountant@ikiraha.com / password
- **Super Admin**: admin@ikiraha.com / password

## 🔧 Customization

### Theme Customization
Modify `lib/utils/constants.dart`:
```dart
class AppColors {
  static const Color primary = Color(0xFF2E7D32);    // Your brand color
  static const Color secondary = Color(0xFFFF6F00);  // Accent color
  // ... other colors
}
```

### Validation Rules
Customize validation in `lib/utils/validators.dart`:
```dart
static const int minPasswordLength = 8;  // Increase security
static const String phoneRegex = r'^your-country-format$';
```

### Role-based Features
Add new roles or permissions in `lib/models/user_model.dart`:
```dart
enum UserRole {
  client,
  merchant,
  accountant,
  superAdmin,
  newRole,  // Add new roles here
}
```

## 🧪 Testing

### Unit Tests
```dart
// Test authentication service
testWidgets('Login with valid credentials', (WidgetTester tester) async {
  // Test implementation
});

// Test form validation
test('Email validation returns error for invalid email', () {
  expect(Validators.validateEmail('invalid-email'), isNotNull);
});
```

### Integration Tests
```dart
// Test complete authentication flow
testWidgets('Complete registration flow', (WidgetTester tester) async {
  // Test implementation
});
```

## 📱 Platform Support

- **Android**: API level 21+ (Android 5.0+)
- **iOS**: iOS 11.0+
- **Web**: Modern browsers with JavaScript enabled

## 🔒 Security Features

- **JWT Token Storage**: Secure local storage
- **Input Sanitization**: All user inputs validated
- **HTTPS Communication**: Secure API communication
- **Session Management**: Automatic logout on token expiry
- **Password Requirements**: Strong password enforcement

## 📈 Performance Optimizations

- **Lazy Loading**: Screens loaded on demand
- **State Persistence**: User session maintained
- **Efficient Rebuilds**: Provider pattern optimization
- **Memory Management**: Proper disposal of controllers
- **Network Caching**: Token and user data caching

## 🐛 Error Handling

- **Network Errors**: User-friendly error messages
- **Validation Errors**: Real-time form feedback
- **API Errors**: Proper error parsing and display
- **Session Expiry**: Automatic token refresh
- **Offline Support**: Graceful degradation

## 📚 Additional Resources

- [Flutter Documentation](https://flutter.dev/docs)
- [Provider State Management](https://pub.dev/packages/provider)
- [HTTP Package](https://pub.dev/packages/http)
- [SharedPreferences](https://pub.dev/packages/shared_preferences)

## 🤝 Contributing

1. Follow Flutter/Dart style guidelines
2. Add tests for new features
3. Update documentation
4. Use meaningful commit messages
5. Test on multiple devices/platforms

---

**IKIRAHA Flutter Auth v1.0.0** - Professional authentication system for Rwanda's food delivery ecosystem
