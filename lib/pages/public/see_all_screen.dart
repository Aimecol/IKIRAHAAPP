import 'package:flutter/material.dart';
import 'package:ikirahaapp/pages/public/product_details_screen.dart';
import 'package:ikirahaapp/pages/public/restaurant_detail_screen.dart';
import 'package:ikirahaapp/widgets/product_card.dart';
import 'package:ikirahaapp/widgets/bottom_navigation_widget.dart';
import 'package:ikirahaapp/pages/public/cart_screen.dart';

class SeeAllScreen extends StatefulWidget {
  final String title;
  final List<Map<String, dynamic>> items;
  final String itemType; // 'products', 'restaurants'

  const SeeAllScreen({
    Key? key,
    required this.title,
    required this.items,
    required this.itemType,
  }) : super(key: key);

  @override
  State<SeeAllScreen> createState() => _SeeAllScreenState();
}

class _SeeAllScreenState extends State<SeeAllScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> filteredItems = [];

  @override
  void initState() {
    super.initState();
    filteredItems = List.from(widget.items);
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    final query = _searchController.text.toLowerCase();
    if (query.isEmpty) {
      setState(() {
        filteredItems = List.from(widget.items);
      });
    } else {
      setState(() {
        filteredItems = widget.items.where((item) {
          if (widget.itemType == 'products') {
            return item['name'].toString().toLowerCase().contains(query) ||
                item['category'].toString().toLowerCase().contains(query) ||
                (item['description']?.toString().toLowerCase().contains(
                      query,
                    ) ??
                    false);
          } else if (widget.itemType == 'restaurants') {
            return item['name'].toString().toLowerCase().contains(query) ||
                (item['description']?.toString().toLowerCase().contains(
                      query,
                    ) ??
                    false);
          }
          return false;
        }).toList();
      });
    }
  }

  void _onItemTap(Map<String, dynamic> item) {
    if (widget.itemType == 'products') {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => ProductDetailsScreen(
            product: item,
            onAddToCart: (product, quantity) {
              // Handle add to cart
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Added $quantity ${product['name']} to cart'),
                  duration: const Duration(seconds: 2),
                ),
              );
            },
          ),
        ),
      );
    } else if (widget.itemType == 'restaurants') {
      // Navigate to restaurant detail page
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => RestaurantDetailScreen(restaurant: item),
        ),
      );
    }
  }

  void _toggleFavorite(int index) {
    setState(() {
      // Find the original item in widget.items and update it
      final itemName = filteredItems[index]['name'];
      final originalIndex = widget.items.indexWhere(
        (item) => item['name'] == itemName,
      );
      if (originalIndex != -1) {
        widget.items[originalIndex]['isFavorite'] =
            !(widget.items[originalIndex]['isFavorite'] ?? false);
        // Update the filtered item as well
        filteredItems[index]['isFavorite'] =
            widget.items[originalIndex]['isFavorite'];
      }
    });
  }

  void _addToCart(Map<String, dynamic> product, int quantity) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Added $quantity ${product['name']} to cart'),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: const Color(0xFFF8F9FA),
      appBar: AppBar(
        title: Text(
          widget.title,
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 18,
            letterSpacing: -0.3,
            color: Colors.black87,
          ),
        ),
        backgroundColor: const Color(0xFFF8F9FA),
        foregroundColor: Colors.black87,
        elevation: 0,
        centerTitle: true,
        leading: Container(
          margin: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.9),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.1),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: IconButton(
            icon: const Icon(
              Icons.arrow_back_ios_new,
              size: 18,
              color: Colors.black87,
            ),
            onPressed: () => Navigator.pop(context),
          ),
        ),
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            margin: const EdgeInsets.all(16.0),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 0),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withValues(alpha: 0.1),
                  spreadRadius: 1,
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Icon(Icons.search, color: Colors.grey[400]),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Search ${widget.title.toLowerCase()}...',
                      hintStyle: TextStyle(
                        color: Colors.grey[500],
                        fontSize: 14,
                        fontFamily: 'Roboto',
                      ),
                      border: InputBorder.none,
                    ),
                  ),
                ),
              ],
            ),
          ),
          // Items Grid/List
          Expanded(
            child: filteredItems.isEmpty
                ? Center(
                    child: Text(
                      _searchController.text.isEmpty
                          ? 'No items found'
                          : 'No items match your search',
                      style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                    ),
                  )
                : widget.itemType == 'restaurants'
                ? _buildRestaurantsList()
                : _buildProductsGrid(),
          ),
        ],
      ),
      bottomNavigationBar: const BottomNavigationWidget(
        currentIndex: -1, // No specific tab selected for see all screen
      ),
      floatingActionButton: _buildFloatingActionButton(),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }

  Widget _buildProductsGrid() {
    return OrientationBuilder(
      builder: (context, orientation) {
        final isLandscape = orientation == Orientation.landscape;
        final crossAxisCount = isLandscape ? 4 : 2;
        final childAspectRatio = isLandscape ? 0.8 : 0.75;

        return GridView.builder(
          padding: const EdgeInsets.all(16),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            childAspectRatio: childAspectRatio,
          ),
          itemCount: filteredItems.length,
          itemBuilder: (context, index) {
            final product = filteredItems[index];

            return ProductCard(
              product: product,
              onTap: () => _onItemTap(product),
              onFavoriteToggle: () => _toggleFavorite(index),
              onAddToCart: () => _addToCart(product, 1),
              showFavoriteButton: true,
              showAddButton: true,
            );
          },
        );
      },
    );
  }

  Widget _buildRestaurantsList() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredItems.length,
      itemBuilder: (context, index) {
        final restaurant = filteredItems[index];
        return Container(
          margin: const EdgeInsets.only(bottom: 16),
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
          child: ListTile(
            contentPadding: const EdgeInsets.all(16),
            leading: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.asset(
                restaurant['image'],
                width: 60,
                height: 60,
                fit: BoxFit.cover,
              ),
            ),
            title: Text(
              restaurant['name'],
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                fontFamily: 'Poppins',
              ),
            ),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  restaurant['description'],
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                    fontFamily: 'Roboto',
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.star, color: Colors.amber, size: 14),
                    const SizedBox(width: 4),
                    Text(
                      restaurant['rating'].toString(),
                      style: const TextStyle(fontSize: 12),
                    ),
                    const SizedBox(width: 16),
                    Icon(Icons.access_time, color: Colors.grey[600], size: 14),
                    const SizedBox(width: 4),
                    Text(
                      restaurant['deliveryTime'],
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                  ],
                ),
              ],
            ),
            onTap: () => _onItemTap(restaurant),
          ),
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
