import 'package:flutter/material.dart';
import 'package:ikirahaapp/pages/public/product_details_screen.dart';
import 'package:ikirahaapp/pages/public/category_screen.dart';

class RestaurantDetailScreen extends StatefulWidget {
  final Map<String, dynamic> restaurant;

  const RestaurantDetailScreen({Key? key, required this.restaurant})
    : super(key: key);

  @override
  State<RestaurantDetailScreen> createState() => _RestaurantDetailScreenState();
}

class _RestaurantDetailScreenState extends State<RestaurantDetailScreen>
    with TickerProviderStateMixin {
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> filteredProducts = [];
  List<Map<String, dynamic>> cartItems = [];
  String selectedCategory = '';

  // Banner related variables
  PageController _bannerPageController = PageController();
  int _currentBannerIndex = 0;
  late AnimationController _bannerAnimationController;
  late Animation<double> _bannerAnimation;

  // Restaurant-specific categories
  final List<Map<String, String>> categories = [
    {'name': 'Ice Cream', 'icon': 'images/ice-cream.png'},
    {'name': 'Pizza', 'icon': 'images/pizza.png'},
    {'name': 'Salad', 'icon': 'images/salad.png'},
    {'name': 'Burger', 'icon': 'images/burger.png'},
    {'name': 'Sushi', 'icon': 'images/salad.png'},
    {'name': 'Pasta', 'icon': 'images/pizza.png'},
  ];

  // Restaurant-specific banners
  final List<Map<String, String>> restaurantBanners = [
    {
      'image': 'images/food.jpg',
      'title': 'Restaurant Special!',
      'subtitle': 'Exclusive deals only at this restaurant',
    },
    {
      'image': 'images/burger.png',
      'title': 'Chef\'s Choice!',
      'subtitle': 'Try our signature dishes',
    },
  ];

  // Restaurant products (filtered from main products)
  final List<Map<String, dynamic>> restaurantProducts = [
    {
      'name': 'Restaurant Special Salad',
      'description': 'Fresh garden salad with house dressing',
      'price': 2800,
      'image': 'images/salad2.png',
      'category': 'Salad',
      'isFavorite': false,
    },
    {
      'name': 'Signature Burger',
      'description': 'Our famous burger with special sauce',
      'price': 3600,
      'image': 'images/burger.png',
      'category': 'Burger',
      'isFavorite': false,
    },
    {
      'name': 'Artisan Pizza',
      'description': 'Hand-crafted pizza with premium ingredients',
      'price': 4300,
      'image': 'images/pizza.png',
      'category': 'Pizza',
      'isFavorite': false,
    },
    {
      'name': 'Premium Ice Cream',
      'description': 'Homemade ice cream with natural flavors',
      'price': 2100,
      'image': 'images/ice-cream.png',
      'category': 'Ice Cream',
      'isFavorite': false,
    },
  ];

  @override
  void initState() {
    super.initState();
    filteredProducts = List.from(restaurantProducts);
    _searchController.addListener(_onSearchChanged);

    // Initialize banner animation
    _bannerAnimationController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
    _bannerAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _bannerAnimationController,
        curve: Curves.easeInOut,
      ),
    );

    // Start banner auto-sliding
    _startBannerAutoSlide();
    _bannerAnimationController.forward();
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    _bannerPageController.dispose();
    _bannerAnimationController.dispose();
    super.dispose();
  }

  void _startBannerAutoSlide() {
    Future.delayed(const Duration(seconds: 4), () {
      if (mounted) {
        setState(() {
          _currentBannerIndex =
              (_currentBannerIndex + 1) % restaurantBanners.length;
        });
        _bannerPageController.animateToPage(
          _currentBannerIndex,
          duration: const Duration(milliseconds: 500),
          curve: Curves.easeInOut,
        );
        _startBannerAutoSlide();
      }
    });
  }

  void _onSearchChanged() {
    final query = _searchController.text.toLowerCase();
    if (query.isEmpty) {
      setState(() {
        filteredProducts = List.from(restaurantProducts);
      });
    } else {
      setState(() {
        filteredProducts = restaurantProducts.where((product) {
          return product['name'].toString().toLowerCase().contains(query) ||
              product['category'].toString().toLowerCase().contains(query) ||
              product['description'].toString().toLowerCase().contains(query);
        }).toList();
      });
    }
  }

  void _onCategoryTap(String categoryName) {
    setState(() {
      selectedCategory = categoryName;
    });

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CategoryScreen(
          categoryName: categoryName,
          products: restaurantProducts
              .where((product) => product['category'] == categoryName)
              .toList(),
        ),
      ),
    );
  }

  void _onProductTap(Map<String, dynamic> product) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ProductDetailsScreen(
          product: product,
          onAddToCart: (product, quantity) {
            _addToCart(product, quantity);
          },
        ),
      ),
    );
  }

  void _addToCart(Map<String, dynamic> product, int quantity) {
    final existingIndex = cartItems.indexWhere(
      (item) => item['name'] == product['name'],
    );

    if (existingIndex != -1) {
      setState(() {
        cartItems[existingIndex]['quantity'] += quantity;
      });
    } else {
      setState(() {
        cartItems.add({...product, 'quantity': quantity});
      });
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Added $quantity ${product['name']} to cart'),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _toggleFavorite(int index) {
    setState(() {
      restaurantProducts[index]['isFavorite'] =
          !(restaurantProducts[index]['isFavorite'] ?? false);
      final productName = restaurantProducts[index]['name'];
      final filteredIndex = filteredProducts.indexWhere(
        (p) => p['name'] == productName,
      );
      if (filteredIndex != -1) {
        filteredProducts[filteredIndex]['isFavorite'] =
            restaurantProducts[index]['isFavorite'];
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(widget.restaurant['name']),
        backgroundColor: Colors.deepOrange,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Restaurant Header Info
              _buildRestaurantHeader(),

              // Search Bar
              _buildSearchBar(),

              // Restaurant Banners
              _buildRestaurantBanners(),

              // Categories Section
              _buildCategoriesSection(),

              // Restaurant Products Section
              _buildRestaurantProductsSection(),

              // Extra spacing for bottom
              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
      floatingActionButton: Container(
        margin: const EdgeInsets.only(top: 20),
        child: SizedBox(
          width: 50,
          height: 50,
          child: FloatingActionButton(
            onPressed: () {
              _showCartDialog();
            },
            backgroundColor: Colors.deepOrange,
            child: const Icon(
              Icons.shopping_cart,
              color: Colors.white,
              size: 20,
            ),
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }

  Widget _buildRestaurantHeader() {
    return Container(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Image.asset(
              widget.restaurant['image'],
              width: 80,
              height: 80,
              fit: BoxFit.cover,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.restaurant['name'],
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'Poppins',
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  widget.restaurant['description'],
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                    fontFamily: 'Roboto',
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.star, color: Colors.amber, size: 16),
                    const SizedBox(width: 4),
                    Text(
                      widget.restaurant['rating'].toString(),
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Icon(Icons.access_time, color: Colors.grey[600], size: 16),
                    const SizedBox(width: 4),
                    Text(
                      widget.restaurant['deliveryTime'],
                      style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16.0),
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
                hintText: 'Search restaurant menu...',
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
    );
  }

  Widget _buildRestaurantBanners() {
    return Container(
      margin: const EdgeInsets.all(14.0),
      height: 140,
      child: Column(
        children: [
          Expanded(
            child: PageView.builder(
              controller: _bannerPageController,
              onPageChanged: (index) {
                setState(() {
                  _currentBannerIndex = index;
                });
              },
              itemCount: restaurantBanners.length,
              itemBuilder: (context, index) {
                final banner = restaurantBanners[index];
                return AnimatedBuilder(
                  animation: _bannerAnimation,
                  builder: (context, child) {
                    return Transform.scale(
                      scale: 0.95 + (0.05 * _bannerAnimation.value),
                      child: Container(
                        margin: const EdgeInsets.symmetric(horizontal: 4.0),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.deepOrange.withValues(alpha: 0.3),
                              spreadRadius: 1,
                              blurRadius: 15,
                              offset: const Offset(0, 8),
                            ),
                          ],
                          image: DecorationImage(
                            image: AssetImage(banner['image']!),
                            fit: BoxFit.cover,
                          ),
                        ),
                        child: Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            gradient: LinearGradient(
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                              colors: [
                                Colors.transparent,
                                Colors.black.withValues(alpha: 0.7),
                              ],
                            ),
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.end,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  banner['title']!,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'Poppins',
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  banner['subtitle']!,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontFamily: 'Roboto',
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: restaurantBanners.asMap().entries.map((entry) {
              return Container(
                width: 8.0,
                height: 8.0,
                margin: const EdgeInsets.symmetric(horizontal: 4.0),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _currentBannerIndex == entry.key
                      ? Colors.deepOrange
                      : Colors.grey.withValues(alpha: 0.4),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoriesSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Categories",
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              fontFamily: 'Poppins',
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 81,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: categories.length,
              itemBuilder: (context, index) {
                final category = categories[index];
                bool isSelected = selectedCategory == category['name'];
                return Container(
                  width: 90,
                  margin: EdgeInsets.only(
                    right: index == categories.length - 1 ? 0 : 12,
                  ),
                  child: _buildCategoryButton(
                    category['name']!,
                    category['icon']!,
                    isSelected,
                    () => _onCategoryTap(category['name']!),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildCategoryButton(
    String name,
    String imagePath,
    bool isSelected,
    VoidCallback onTap,
  ) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: isSelected ? Colors.deepOrange : Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: isSelected
                  ? Colors.deepOrange.withValues(alpha: 0.3)
                  : Colors.grey.withValues(alpha: 0.1),
              spreadRadius: 1,
              blurRadius: isSelected ? 10 : 5,
              offset: Offset(0, isSelected ? 5 : 2),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: isSelected
                    ? Colors.white.withValues(alpha: 0.2)
                    : Colors.grey[100],
                borderRadius: BorderRadius.circular(15),
              ),
              child: Image.asset(
                imagePath,
                color: isSelected ? Colors.white : Colors.deepOrange,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              name,
              style: TextStyle(
                color: isSelected ? Colors.white : Colors.grey[700],
                fontWeight: FontWeight.w600,
                fontSize: 12,
                fontFamily: 'Roboto',
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRestaurantProductsSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Menu Items",
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              fontFamily: 'Poppins',
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
              childAspectRatio: 0.75,
            ),
            itemCount: filteredProducts.length,
            itemBuilder: (context, index) {
              final product = filteredProducts[index];
              bool isFavorite = product['isFavorite'] ?? false;
              final originalIndex = restaurantProducts.indexWhere(
                (p) => p['name'] == product['name'],
              );

              return GestureDetector(
                onTap: () => _onProductTap(product),
                child: Container(
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
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Stack(
                        children: [
                          Container(
                            height: 120,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              borderRadius: const BorderRadius.only(
                                topLeft: Radius.circular(16),
                                topRight: Radius.circular(16),
                              ),
                              image: DecorationImage(
                                image: AssetImage(
                                  product['image'] ?? 'images/salad2.png',
                                ),
                                fit: BoxFit.cover,
                              ),
                            ),
                          ),
                          Positioned(
                            top: 4,
                            right: 4,
                            child: Container(
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.9),
                                shape: BoxShape.circle,
                              ),
                              child: IconButton(
                                onPressed: () => _toggleFavorite(originalIndex),
                                icon: Icon(
                                  isFavorite
                                      ? Icons.favorite
                                      : Icons.favorite_border,
                                  color: isFavorite
                                      ? Colors.red
                                      : Colors.grey[600],
                                  size: 18,
                                ),
                                padding: const EdgeInsets.all(4),
                                constraints: const BoxConstraints(),
                              ),
                            ),
                          ),
                        ],
                      ),
                      Expanded(
                        child: Padding(
                          padding: const EdgeInsets.all(12.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                product['name'] ?? 'Product Name',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'Poppins',
                                  color: Colors.grey[800],
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                product['category'] ?? 'Category',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.grey[600],
                                  fontFamily: 'Roboto',
                                ),
                              ),
                              const Spacer(),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Text(
                                      'Rwf ${product['price']?.toString() ?? '0'}',
                                      style: const TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.deepOrange,
                                        fontFamily: 'Poppins',
                                      ),
                                    ),
                                  ),
                                  Container(
                                    decoration: BoxDecoration(
                                      color: Colors.deepOrange,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: IconButton(
                                      onPressed: () => _addToCart(product, 1),
                                      icon: const Icon(
                                        Icons.add,
                                        color: Colors.white,
                                        size: 16,
                                      ),
                                      padding: const EdgeInsets.all(4),
                                      constraints: const BoxConstraints(),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  void _showCartDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        double total = cartItems.fold(
          0,
          (sum, item) => sum + (item['price'] * item['quantity']),
        );

        return AlertDialog(
          title: const Text('Your Cart'),
          content: SizedBox(
            width: double.maxFinite,
            child: cartItems.isEmpty
                ? const Text('Your cart is empty')
                : Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      ListView.builder(
                        shrinkWrap: true,
                        itemCount: cartItems.length,
                        itemBuilder: (context, index) {
                          final item = cartItems[index];
                          return ListTile(
                            leading: Image.asset(
                              item['image'] ?? 'images/salad2.png',
                              width: 40,
                              height: 40,
                              fit: BoxFit.cover,
                            ),
                            title: Text(item['name']),
                            subtitle: Text(
                              'Rwf ${item['price']} x ${item['quantity']}',
                            ),
                            trailing: Text(
                              'Rwf ${item['price'] * item['quantity']}',
                            ),
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Total: Rwf $total',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: const Text('Continue Shopping'),
            ),
            if (cartItems.isNotEmpty)
              ElevatedButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  // Navigate to checkout
                },
                child: const Text('Checkout'),
              ),
          ],
        );
      },
    );
  }
}
