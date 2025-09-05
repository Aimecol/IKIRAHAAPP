# IKIRAHA App Startup Flow

## 🚀 App Launch Sequence

The IKIRAHA Flutter app has been configured to **always start with the login screen** for a clean user experience.

### Current Startup Flow

```
App Launch
    ↓
main.dart
    ↓
IkirahaApp (MaterialApp)
    ↓
LoginScreen (Direct Navigation)
    ↓
User Authentication
    ↓
Role-based Navigation
```

## 📱 Implementation Details

### 1. Main App Entry Point
**File**: `lib/main.dart`

```dart
class IkirahaApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: const LoginScreen(), // Direct start with login
      // ... other configurations
    );
  }
}
```

### 2. Login Screen Behavior
**File**: `lib/screens/auth/login_screen.dart`

- **No Auto-Authentication**: App doesn't automatically check for stored tokens
- **Fresh Start**: Every app launch requires user to login
- **Clean State**: No background authentication processes
- **User Control**: User explicitly chooses to login

### 3. Authentication Provider
**File**: `lib/providers/auth_provider.dart`

- **Manual Initialization**: Authentication state is not auto-initialized
- **On-Demand Loading**: Auth state is loaded only after successful login
- **Clean Startup**: Provider starts in unauthenticated state

## 🔄 User Journey

### First Time Users
1. **App Opens** → Login Screen
2. **No Account** → Tap "Sign Up" → Registration Screen
3. **Complete Registration** → Automatic login → Role-based home screen

### Returning Users
1. **App Opens** → Login Screen
2. **Enter Credentials** → Tap "Login"
3. **Successful Authentication** → Role-based home screen

### Role-based Navigation
After successful login, users are redirected based on their role:

- **Client** → `/client-home` - Browse restaurants, place orders
- **Merchant** → `/merchant-home` - Manage restaurants, process orders
- **Accountant** → `/accountant-home` - View financials, generate reports
- **Super Admin** → `/admin-home` - Full system management

## 🛡 Security Considerations

### Why Always Start with Login?

1. **Security First**: No automatic token validation on startup
2. **User Awareness**: Users are always aware of their authentication state
3. **Clean Sessions**: Each app launch is a fresh session
4. **Explicit Authentication**: Users must explicitly choose to login
5. **No Background Processes**: No hidden authentication checks

### Token Management
- **Secure Storage**: JWT tokens stored in SharedPreferences
- **Session Control**: Tokens are loaded only after manual login
- **Logout Cleanup**: All tokens cleared on logout
- **Expiry Handling**: Automatic token refresh during active sessions

## 🔧 Configuration Options

### Option 1: Current Implementation (Always Login)
```dart
// main.dart
home: const LoginScreen(), // Always start here
```

### Option 2: Auto-Authentication (Alternative)
If you want to enable auto-authentication in the future:

```dart
// main.dart
home: const SplashScreen(), // Check auth first

// splash_screen.dart
// Add authentication check logic
```

### Option 3: Remember Me Feature
To add "Remember Me" functionality:

```dart
// In login_screen.dart
if (_rememberMe && await AuthService.isAuthenticated()) {
  // Auto-login logic
}
```

## 📋 Demo Accounts

For testing, use these pre-configured accounts:

| Role | Email | Password |
|------|-------|----------|
| Client | client@ikiraha.com | password |
| Merchant | merchant@ikiraha.com | password |
| Accountant | accountant@ikiraha.com | password |
| Super Admin | admin@ikiraha.com | password |

## 🎯 Benefits of Current Approach

### User Experience
- **Clear Entry Point**: Users always know where they are
- **No Confusion**: No automatic redirects or background processes
- **Consistent Behavior**: Same experience every time
- **Fast Startup**: No authentication checks delay app launch

### Development Benefits
- **Predictable Flow**: Always starts at the same point
- **Easy Testing**: Clear starting state for all tests
- **Simple Debugging**: No complex initialization logic
- **Maintainable**: Straightforward code flow

### Security Benefits
- **Explicit Authentication**: Users must actively login
- **No Token Exposure**: Tokens not accessed until needed
- **Session Control**: Clear session boundaries
- **Audit Trail**: All logins are explicit user actions

## 🔄 Future Enhancements

### Potential Additions
1. **Biometric Authentication**: Fingerprint/Face ID login
2. **Remember Me**: Optional auto-login for trusted devices
3. **Quick Login**: PIN-based authentication for returning users
4. **Social Login**: Google/Facebook authentication options
5. **Multi-Factor Authentication**: SMS/Email verification

### Implementation Considerations
- **User Preference**: Make auto-login optional
- **Security Settings**: Allow users to control authentication behavior
- **Device Trust**: Remember trusted devices only
- **Session Timeout**: Configurable session durations

---

**Current Status**: ✅ App always starts with login screen
**Security Level**: 🔒 High - Explicit authentication required
**User Experience**: 🎯 Clear and predictable startup flow
