import 'package:flutter/material.dart';
import 'package:ikirahaapp/pages/public/home.dart';
import 'package:ikirahaapp/pages/public/search_screen.dart';
import 'package:ikirahaapp/pages/public/orders_screen.dart';
import 'package:ikirahaapp/pages/public/profile_screen.dart';
import 'package:ikirahaapp/pages/public/cart_screen.dart';

class BottomNavigationWidget extends StatefulWidget {
  final int currentIndex;
  final List<Map<String, dynamic>>? cartItems;
  final Function(List<Map<String, dynamic>>)? onCartUpdated;
  final List<Map<String, dynamic>>? products;
  final List<Map<String, dynamic>>? restaurants;

  const BottomNavigationWidget({
    super.key,
    this.currentIndex = 0,
    this.cartItems,
    this.onCartUpdated,
    this.products,
    this.restaurants,
  });

  @override
  State<BottomNavigationWidget> createState() => _BottomNavigationWidgetState();
}

class _BottomNavigationWidgetState extends State<BottomNavigationWidget> {
  late int _currentBottomNavIndex;

  @override
  void initState() {
    super.initState();
    _currentBottomNavIndex = widget.currentIndex;
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Cart FAB
        Container(
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
                    cartItems: widget.cartItems ?? [],
                    onCartUpdated: widget.onCartUpdated ?? (items) {},
                  ),
                ),
              );
            },
            backgroundColor: Colors.transparent,
            elevation: 0,
            child: Stack(
              children: [
                const Icon(
                  Icons.shopping_cart_outlined,
                  color: Colors.white,
                  size: 24,
                ),
                if (widget.cartItems != null && widget.cartItems!.isNotEmpty)
                  Positioned(
                    right: 0,
                    top: 0,
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 16,
                        minHeight: 16,
                      ),
                      child: Text(
                        '${widget.cartItems!.length}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),
        // Bottom Navigation Bar
        BottomAppBar(
          height: 70,
          color: const Color.fromARGB(255, 201, 196, 196),
          elevation: 8,
          shape: const CircularNotchedRectangle(),
          notchMargin: 4.0,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildNavBarItem(Icons.home_outlined, Icons.home, 'Home', 0),
                _buildNavBarItem(Icons.search_outlined, Icons.search, 'Search', 1),
                const SizedBox(width: 40), // Space for the center FAB
                _buildNavBarItem(
                  Icons.receipt_long_outlined,
                  Icons.receipt_long,
                  'Orders',
                  2,
                ),
                _buildNavBarItem(Icons.menu_outlined, Icons.menu, 'Menu', 3),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildNavBarItem(
    IconData outlinedIcon,
    IconData filledIcon,
    String label,
    int index,
  ) {
    final bool isSelected = _currentBottomNavIndex == index;

    return GestureDetector(
      onTap: () {
        setState(() {
          _currentBottomNavIndex = index;
        });

        // Handle navigation based on index
        switch (index) {
          case 0: // Home
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(builder: (context) => const Home()),
              (route) => false,
            );
            break;
          case 1: // Search
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => SearchScreen(
                  products: widget.products ?? [],
                  restaurants: widget.restaurants ?? [],
                ),
              ),
            );
            break;
          case 2: // Orders
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const OrdersScreen()),
            );
            break;
          case 3: // Menu/Profile
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const ProfileScreen()),
            );
            break;
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isSelected ? filledIcon : outlinedIcon,
              color: isSelected ? Colors.deepOrange : Colors.grey[600],
              size: 24,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: isSelected ? Colors.deepOrange : Colors.grey[600],
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                fontFamily: 'Poppins',
              ),
            ),
          ],
        ),
      ),
    );
  }
}
