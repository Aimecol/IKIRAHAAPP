import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/auth/reset_password_screen.dart';
import 'pages/public/home.dart';
import 'utils/constants.dart';
import 'config/environment.dart';

void main() async {
  // Ensure Flutter binding is initialized
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize environment configuration
  await Environment.initialize();

  // Print environment configuration for debugging
  Environment.instance.printConfig();

  // Validate environment configuration
  if (!Environment.instance.validateConfig()) {
    throw Exception('Invalid environment configuration');
  }

  runApp(const IkirahaApp());
}

class IkirahaApp extends StatelessWidget {
  const IkirahaApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MaterialApp(
        title: AppConstants.appName,
        theme: AppTheme.lightTheme,
        debugShowCheckedModeBanner: false,
        home: const LoginScreen(), // Start directly with login screen
        routes: {
          '/login': (context) => const LoginScreen(),
          '/register': (context) => const RegisterScreen(),
          '/home': (context) => const Home(),
        },
        onGenerateRoute: (settings) {
          // Handle reset password route with token parameter
          if (settings.name?.startsWith('/reset-password') == true) {
            final uri = Uri.parse(settings.name!);
            final token = uri.queryParameters['token'];

            if (token != null) {
              return MaterialPageRoute(
                builder: (context) => ResetPasswordScreen(token: token),
                settings: settings,
              );
            }
          }

          // Default route
          return MaterialPageRoute(
            builder: (context) => const LoginScreen(),
          );
        },
      ),
    );
  }
}

// Removed role-based home screens - all users now go to the same home page
