import 'package:flutter/material.dart';
import 'package:ikirahaapp/widgets/bottom_navigation_widget.dart';
import 'package:ikirahaapp/pages/public/cart_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  // Mock user data
  final Map<String, dynamic> userData = {
    'name': 'John Doe',
    'email': 'john.doe@example.com',
    'phone': '+250 788 123 456',
    'address': 'Kigali, Rwanda',
    'profileImage': 'images/wallet.png', // Using existing asset as placeholder
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text('Profile'),
        backgroundColor: Colors.deepOrange,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () {
              // Edit profile functionality
              _showEditProfileDialog();
            },
            icon: const Icon(Icons.edit),
            tooltip: 'Edit Profile',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Profile Header
            _buildProfileHeader(),
            const SizedBox(height: 24),

            // Profile Options
            _buildProfileOptions(),

            const SizedBox(height: 24),

            // Account Settings
            _buildAccountSettings(),

            const SizedBox(height: 24),

            // Logout Button
            _buildLogoutButton(),
          ],
        ),
      ),
      bottomNavigationBar: const BottomNavigationWidget(
        currentIndex: 3, // Profile/Menu tab is selected
      ),
      floatingActionButton: _buildFloatingActionButton(),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }

  Widget _buildProfileHeader() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.1),
            spreadRadius: 1,
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        children: [
          CircleAvatar(
            radius: 50,
            backgroundColor: Colors.deepOrange.withValues(alpha: 0.1),
            child: Icon(Icons.person, size: 60, color: Colors.deepOrange),
          ),
          const SizedBox(height: 16),
          Text(
            userData['name'],
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              fontFamily: 'Poppins',
            ),
          ),
          const SizedBox(height: 4),
          Text(
            userData['email'],
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[600],
              fontFamily: 'Roboto',
            ),
          ),
          const SizedBox(height: 8),
          Text(
            userData['phone'],
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
              fontFamily: 'Roboto',
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileOptions() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.1),
            spreadRadius: 1,
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildOptionTile(
            icon: Icons.history,
            title: 'Order History',
            subtitle: 'View your past orders',
            onTap: () {
              // Navigate to order history
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Order History - Coming Soon!')),
              );
            },
          ),
          _buildDivider(),
          _buildOptionTile(
            icon: Icons.favorite,
            title: 'Favorites',
            subtitle: 'Your favorite items',
            onTap: () {
              // Navigate to favorites
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Favorites - Coming Soon!')),
              );
            },
          ),
          _buildDivider(),
          _buildOptionTile(
            icon: Icons.location_on,
            title: 'Delivery Addresses',
            subtitle: 'Manage your addresses',
            onTap: () {
              // Navigate to addresses
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Addresses - Coming Soon!')),
              );
            },
          ),
          _buildDivider(),
          _buildOptionTile(
            icon: Icons.payment,
            title: 'Payment Methods',
            subtitle: 'Manage payment options',
            onTap: () {
              // Navigate to payment methods
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Payment Methods - Coming Soon!')),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildAccountSettings() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.1),
            spreadRadius: 1,
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildOptionTile(
            icon: Icons.notifications,
            title: 'Notifications',
            subtitle: 'Manage notification settings',
            onTap: () {
              // Navigate to notification settings
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Notifications - Coming Soon!')),
              );
            },
          ),
          _buildDivider(),
          _buildOptionTile(
            icon: Icons.help,
            title: 'Help & Support',
            subtitle: 'Get help and contact support',
            onTap: () {
              // Navigate to help
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Help & Support - Coming Soon!')),
              );
            },
          ),
          _buildDivider(),
          _buildOptionTile(
            icon: Icons.info,
            title: 'About',
            subtitle: 'App version and information',
            onTap: () {
              // Show about dialog
              _showAboutDialog();
            },
          ),
        ],
      ),
    );
  }

  Widget _buildOptionTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: Colors.deepOrange.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: Colors.deepOrange, size: 24),
      ),
      title: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          fontFamily: 'Poppins',
        ),
      ),
      subtitle: Text(
        subtitle,
        style: TextStyle(
          fontSize: 12,
          color: Colors.grey[600],
          fontFamily: 'Roboto',
        ),
      ),
      trailing: const Icon(
        Icons.arrow_forward_ios,
        size: 16,
        color: Colors.grey,
      ),
      onTap: onTap,
    );
  }

  Widget _buildDivider() {
    return Divider(
      height: 1,
      color: Colors.grey[200],
      indent: 16,
      endIndent: 16,
    );
  }

  Widget _buildLogoutButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: () {
          _showLogoutDialog();
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.red[600],
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: const Text(
          'Logout',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  void _showEditProfileDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Edit Profile'),
          content: const Text(
            'Profile editing functionality will be implemented here.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Profile editing - Coming Soon!'),
                  ),
                );
              },
              child: const Text('Save'),
            ),
          ],
        );
      },
    );
  }

  void _showAboutDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('About IKIRAHA'),
          content: const Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('IKIRAHA Food Delivery App'),
              SizedBox(height: 8),
              Text('Version: 1.0.0'),
              SizedBox(height: 8),
              Text('A modern food delivery application for Rwanda.'),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Close'),
            ),
          ],
        );
      },
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Logout'),
          content: const Text('Are you sure you want to logout?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.of(context).pop();
                // Implement logout logic here
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Logged out successfully')),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[600],
                foregroundColor: Colors.white,
              ),
              child: const Text('Logout'),
            ),
          ],
        );
      },
    );
  }

  Widget _buildFloatingActionButton() {
    return Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: const LinearGradient(
          colors: [Colors.deepOrange, Colors.orange],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.deepOrange.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CartScreen(
                cartItems: const [],
                onCartUpdated: (updatedCart) {
                  // Handle cart updates if needed
                },
              ),
            ),
          );
        },
        backgroundColor: Colors.transparent,
        elevation: 0,
        child: const Icon(
          Icons.shopping_cart_outlined,
          color: Colors.white,
          size: 24,
        ),
      ),
    );
  }
}
