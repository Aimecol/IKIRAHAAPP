import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import 'package:ikirahaapp/widgets/bottom_navigation_widget.dart';
import 'package:ikirahaapp/pages/public/cart_screen.dart';
import 'package:ikirahaapp/pages/public/edit_profile_screen.dart';
import 'package:ikirahaapp/providers/profile_provider.dart';
import 'package:ikirahaapp/providers/auth_provider.dart';
import 'package:ikirahaapp/models/user_model.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ImagePicker _imagePicker = ImagePicker();

  @override
  void initState() {
    super.initState();
    // Initialize profile data when screen loads
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final profileProvider = Provider.of<ProfileProvider>(context, listen: false);
      if (profileProvider.user == null) {
        profileProvider.initializeProfile();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ProfileProvider>(
      builder: (context, profileProvider, child) {
        return Scaffold(
          backgroundColor: Colors.grey[50],
          appBar: AppBar(
            title: const Text('Profile'),
            backgroundColor: Colors.deepOrange,
            foregroundColor: Colors.white,
            elevation: 0,
            actions: [
              IconButton(
                onPressed: profileProvider.isLoading ? null : () {
                  _showEditProfileDialog(profileProvider.user);
                },
                icon: const Icon(Icons.edit),
                tooltip: 'Edit Profile',
              ),
              IconButton(
                onPressed: profileProvider.isLoading ? null : () {
                  profileProvider.refreshProfile();
                },
                icon: const Icon(Icons.refresh),
                tooltip: 'Refresh Profile',
              ),
            ],
          ),
          body: profileProvider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : profileProvider.hasError
                  ? _buildErrorState(profileProvider.error!)
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          // Profile Header
                          _buildProfileHeader(profileProvider.user),
                          const SizedBox(height: 24),

                          // Profile Completion Card
                          _buildProfileCompletionCard(profileProvider),
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
      },
    );
  }

  Widget _buildProfileHeader(User? user) {
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
          Stack(
            children: [
              CircleAvatar(
                radius: 50,
                backgroundColor: Colors.deepOrange.withValues(alpha: 0.1),
                backgroundImage: user?.profileImage != null
                    ? NetworkImage('http://localhost/ikirahaapp/ikiraha-api/${user!.profileImage}')
                    : null,
                child: user?.profileImage == null
                    ? Text(
                        Provider.of<ProfileProvider>(context, listen: false).userInitials,
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.deepOrange,
                        ),
                      )
                    : null,
              ),
              Positioned(
                bottom: 0,
                right: 0,
                child: GestureDetector(
                  onTap: () => _showImagePickerDialog(),
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.deepOrange,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                    ),
                    child: const Icon(
                      Icons.camera_alt,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            user?.name ?? 'Guest User',
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              fontFamily: 'Poppins',
            ),
          ),
          const SizedBox(height: 4),
          Text(
            user?.email ?? 'No email',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[600],
              fontFamily: 'Roboto',
            ),
          ),
          if (user?.phone != null) ...[
            const SizedBox(height: 8),
            Text(
              user!.phone!,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
                fontFamily: 'Roboto',
              ),
            ),
          ],
          if (user?.address != null) ...[
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Flexible(
                  child: Text(
                    user!.address!,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildErrorState(String error) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.red[400],
          ),
          const SizedBox(height: 16),
          Text(
            'Error loading profile',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            error,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () {
              Provider.of<ProfileProvider>(context, listen: false).refreshProfile();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.deepOrange,
              foregroundColor: Colors.white,
            ),
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileCompletionCard(ProfileProvider profileProvider) {
    final completionPercentage = profileProvider.profileCompletionPercentage;

    if (completionPercentage >= 1.0) {
      return const SizedBox.shrink(); // Hide if profile is complete
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.orange[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.orange[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.info_outline, color: Colors.orange[700], size: 20),
              const SizedBox(width: 8),
              Text(
                'Complete Your Profile',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.orange[700],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          LinearProgressIndicator(
            value: completionPercentage,
            backgroundColor: Colors.orange[100],
            valueColor: AlwaysStoppedAnimation<Color>(Colors.orange[600]!),
          ),
          const SizedBox(height: 8),
          Text(
            '${(completionPercentage * 100).round()}% complete',
            style: TextStyle(
              fontSize: 12,
              color: Colors.orange[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Add more information to get better recommendations and faster service.',
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
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

  void _showEditProfileDialog(User? user) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => EditProfileScreen(user: user),
      ),
    );
  }

  void _showImagePickerDialog() {
    showModalBottomSheet(
      context: context,
      builder: (BuildContext context) {
        return SafeArea(
          child: Wrap(
            children: [
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Choose from Gallery'),
                onTap: () {
                  Navigator.of(context).pop();
                  _pickImage(ImageSource.gallery);
                },
              ),
              ListTile(
                leading: const Icon(Icons.photo_camera),
                title: const Text('Take a Photo'),
                onTap: () {
                  Navigator.of(context).pop();
                  _pickImage(ImageSource.camera);
                },
              ),
              if (Provider.of<ProfileProvider>(context, listen: false).user?.profileImage != null)
                ListTile(
                  leading: const Icon(Icons.delete, color: Colors.red),
                  title: const Text('Remove Photo', style: TextStyle(color: Colors.red)),
                  onTap: () {
                    Navigator.of(context).pop();
                    _removeProfilePicture();
                  },
                ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? image = await _imagePicker.pickImage(
        source: source,
        maxWidth: 512,
        maxHeight: 512,
        imageQuality: 80,
      );

      if (image != null) {
        final profileProvider = Provider.of<ProfileProvider>(context, listen: false);
        final success = await profileProvider.uploadProfilePicture(File(image.path));

        if (mounted) {
          if (success) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Profile picture updated successfully')),
            );
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(profileProvider.error ?? 'Failed to update profile picture')),
            );
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error picking image: $e')),
        );
      }
    }
  }

  Future<void> _removeProfilePicture() async {
    final profileProvider = Provider.of<ProfileProvider>(context, listen: false);
    final success = await profileProvider.deleteProfilePicture();

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profile picture removed successfully')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(profileProvider.error ?? 'Failed to remove profile picture')),
        );
      }
    }
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
              onPressed: () async {
                Navigator.of(context).pop();

                final authProvider = Provider.of<AuthProvider>(context, listen: false);
                final profileProvider = Provider.of<ProfileProvider>(context, listen: false);

                // Logout from auth provider
                await authProvider.logout();

                // Clear profile data
                profileProvider.clearProfile();

                // Navigate to login screen
                if (mounted) {
                  Navigator.of(context).pushNamedAndRemoveUntil(
                    '/login',
                    (route) => false,
                  );
                }
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
