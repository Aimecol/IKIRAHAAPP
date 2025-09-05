import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'utils/constants.dart';
import 'models/user_model.dart';
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
          '/client-home': (context) => const ClientHomeScreen(),
          '/merchant-home': (context) => const MerchantHomeScreen(),
          '/accountant-home': (context) => const AccountantHomeScreen(),
          '/admin-home': (context) => const AdminHomeScreen(),
        },
        onGenerateRoute: (settings) {
          // Handle dynamic routes if needed
          switch (settings.name) {
            default:
              return MaterialPageRoute(
                builder: (context) => const LoginScreen(),
              );
          }
        },
      ),
    );
  }
}

// Placeholder screens for different user roles
class ClientHomeScreen extends StatelessWidget {
  const ClientHomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('IKIRAHA - Client'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 50,
                  backgroundColor: AppColors.primary,
                  child: Text(
                    authProvider.userInitials,
                    style: AppTextStyles.h2.copyWith(
                      color: AppColors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Welcome, ${authProvider.displayName}!',
                  style: AppTextStyles.h4,
                ),
                const SizedBox(height: 8),
                Text(
                  'Role: ${authProvider.user?.role.displayName}',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 32),
                const Text(
                  'Client Dashboard',
                  style: AppTextStyles.h5,
                ),
                const SizedBox(height: 16),
                const Text(
                  '• Browse restaurants\n• Place orders\n• Track deliveries\n• Manage favorites',
                  style: AppTextStyles.bodyMedium,
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class MerchantHomeScreen extends StatelessWidget {
  const MerchantHomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('IKIRAHA - Merchant'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 50,
                  backgroundColor: AppColors.secondary,
                  child: Text(
                    authProvider.userInitials,
                    style: AppTextStyles.h2.copyWith(
                      color: AppColors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Welcome, ${authProvider.displayName}!',
                  style: AppTextStyles.h4,
                ),
                const SizedBox(height: 8),
                Text(
                  'Role: ${authProvider.user?.role.displayName}',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 32),
                const Text(
                  'Merchant Dashboard',
                  style: AppTextStyles.h5,
                ),
                const SizedBox(height: 16),
                const Text(
                  '• Manage restaurants\n• Update menu items\n• Process orders\n• View analytics',
                  style: AppTextStyles.bodyMedium,
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class AccountantHomeScreen extends StatelessWidget {
  const AccountantHomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('IKIRAHA - Accountant'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 50,
                  backgroundColor: AppColors.accent,
                  child: Text(
                    authProvider.userInitials,
                    style: AppTextStyles.h2.copyWith(
                      color: AppColors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Welcome, ${authProvider.displayName}!',
                  style: AppTextStyles.h4,
                ),
                const SizedBox(height: 8),
                Text(
                  'Role: ${authProvider.user?.role.displayName}',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 32),
                const Text(
                  'Accountant Dashboard',
                  style: AppTextStyles.h5,
                ),
                const SizedBox(height: 16),
                const Text(
                  '• View transactions\n• Generate reports\n• Financial analytics\n• Revenue tracking',
                  style: AppTextStyles.bodyMedium,
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class AdminHomeScreen extends StatelessWidget {
  const AdminHomeScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('IKIRAHA - Admin'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                Navigator.of(context).pushReplacementNamed('/login');
              }
            },
          ),
        ],
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 50,
                  backgroundColor: AppColors.error,
                  child: Text(
                    authProvider.userInitials,
                    style: AppTextStyles.h2.copyWith(
                      color: AppColors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Welcome, ${authProvider.displayName}!',
                  style: AppTextStyles.h4,
                ),
                const SizedBox(height: 8),
                Text(
                  'Role: ${authProvider.user?.role.displayName}',
                  style: AppTextStyles.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 32),
                const Text(
                  'Admin Dashboard',
                  style: AppTextStyles.h5,
                ),
                const SizedBox(height: 16),
                const Text(
                  '• Manage users\n• System settings\n• Full access control\n• Platform oversight',
                  style: AppTextStyles.bodyMedium,
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
