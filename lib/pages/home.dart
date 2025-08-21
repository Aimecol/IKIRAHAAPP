import 'package:flutter/material.dart';
import 'product_details_screen.dart';
import 'category_screen.dart';
import 'checkout_screen.dart';

class Home extends StatefulWidget {
  const Home({super.key});

  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> {
  String selectedCategory = '';
  int _currentBottomNavIndex = 0;
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> filteredProducts = [];
  List<Map<String, dynamic>> cartItems = [];

  // Category data with navigation functionality
  final List<Map<String, String>> categories = [
    {'name': 'Ice Cream', 'icon': 'images/ice-cream.png'},
    {'name': 'Pizza', 'icon': 'images/pizza.png'},
    {'name': 'Salad', 'icon': 'images/salad.png'},
    {'name': 'Burger', 'icon': 'images/burger.png'},
    {'name': 'Sushi', 'icon': 'images/sushi.png'},
    {'name': 'Pasta', 'icon': 'images/pasta.png'},
  ];

  // Product data with images
  final List<Map<String, dynamic>> featuredProducts = [
    {
      'name': 'Fresh Garden Salad',
      'description': 'Mixed greens with fresh vegetables and dressing',
      'price': 2700,
      'image': 'images/salad2.png',
      'category': 'Salad',
      'isFavorite': false,
    },
    {
      'name': 'Chicken Caesar Salad',
      'description': 'Grilled chicken with romaine lettuce and Caesar dressing',
      'price': 3850,
      'image': 'images/salad3.png',
      'category': 'Salad',
      'isFavorite': false,
    },
    {
      'name': 'Fruit Salad Bowl',
      'description': 'Assorted fresh fruits with yogurt dressing',
      'price': 2600,
      'image': 'images/salad4.png',
      'category': 'Salad',
      'isFavorite': false,
    },
  ];

  // Popular items data (10+ products)
  final List<Map<String, dynamic>> popularItems = [
    {
      'name': 'Classic Burger',
      'price': 3500,
      'category': 'Burger',
      'description': 'Juicy beef patty with fresh vegetables and special sauce',
      'image': 'images/burger1.png',
      'isFavorite': false,
    },
    {
      'name': 'Margherita Pizza',
      'price': 4200,
      'category': 'Pizza',
      'description': 'Classic pizza with tomato and fresh mozzarella cheese',
      'image': 'images/pizza1.png',
      'isFavorite': false,
    },
    {
      'name': 'Vanilla Ice Cream',
      'price': 1800,
      'category': 'Ice Cream',
      'description': 'Creamy vanilla ice cream with chocolate toppings',
      'image': 'images/icecream1.png',
      'isFavorite': false,
    },
    {
      'name': 'Greek Salad',
      'price': 2900,
      'category': 'Salad',
      'description': 'Traditional Greek salad with feta cheese and olives',
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
    {
      'name': 'BBQ Chicken Pizza',
      'price': 4500,
      'category': 'Pizza',
      'description': 'Smoky BBQ sauce with grilled chicken and red onions',
      'image': 'images/pizza2.png',
      'isFavorite': false,
    },
    {
      'name': 'Chocolate Sundae',
      'price': 2200,
      'category': 'Ice Cream',
      'description': 'Vanilla ice cream with hot fudge and nuts',
      'image': 'images/icecream2.png',
      'isFavorite': false,
    },
    {
      'name': 'Bacon Cheeseburger',
      'price': 3800,
      'category': 'Burger',
      'description': 'Beef patty with crispy bacon and melted cheese',
      'image': 'images/burger2.png',
      'isFavorite': false,
    },
    {
      'name': 'California Roll',
      'price': 3200,
      'category': 'Sushi',
      'description': 'Fresh crab with avocado and cucumber',
      'image': 'images/sushi1.png',
      'isFavorite': false,
    },
    {
      'name': 'Caesar Pasta',
      'price': 3100,
      'category': 'Pasta',
      'description': 'Creamy Caesar sauce with pasta and parmesan',
      'image': 'images/pasta1.png',
      'isFavorite': false,
    },
    {
      'name': 'Spicy Tuna Roll',
      'price': 3400,
      'category': 'Sushi',
      'description': 'Spicy tuna with cucumber and sesame seeds',
      'image': 'images/sushi2.png',
      'isFavorite': false,
    },
    {
      'name': 'Veggie Burger',
      'price': 3300,
      'category': 'Burger',
      'description': 'Plant-based patty with fresh vegetables',
      'image': 'images/burger3.png',
      'isFavorite': false,
    },
    {
      'name': 'Chocolate Chip Ice Cream',
      'price': 2000,
      'category': 'Ice Cream',
      'description': 'Creamy ice cream with chocolate chunks',
      'image': 'images/icecream3.png',
      'isFavorite': false,
    },
  ];

  @override
  void initState() {
    super.initState();
    filteredProducts = List.from(popularItems);
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
        filteredProducts = List.from(popularItems);
      });
    } else {
      setState(() {
        filteredProducts = popularItems.where((product) {
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

    // Navigate to category screen
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CategoryScreen(
          categoryName: categoryName,
          products: popularItems.where((product) => product['category'] == categoryName).toList(),
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
    // Check if product already exists in cart
    final existingIndex = cartItems.indexWhere((item) => item['name'] == product['name']);

    if (existingIndex != -1) {
      setState(() {
        cartItems[existingIndex]['quantity'] += quantity;
      });
    } else {
      setState(() {
        cartItems.add({
          ...product,
          'quantity': quantity,
        });
      });
    }

    // Show confirmation message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Added $quantity ${product['name']} to cart'),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _toggleFavorite(int index, String section) {
    setState(() {
      if (section == 'featured') {
        featuredProducts[index]['isFavorite'] = !(featuredProducts[index]['isFavorite'] ?? false);
      } else {
        popularItems[index]['isFavorite'] = !(popularItems[index]['isFavorite'] ?? false);
        // Update filtered products as well
        final productName = popularItems[index]['name'];
        final filteredIndex = filteredProducts.indexWhere((p) => p['name'] == productName);
        if (filteredIndex != -1) {
          filteredProducts[filteredIndex]['isFavorite'] = popularItems[index]['isFavorite'];
        }
      }
    });
  }

  void _viewAllPopularItems() {
    // Navigate to a screen showing all popular items
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CategoryScreen(
          categoryName: 'All Popular Items',
          products: List.from(popularItems),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Section
              _buildHeaderSection(),

              // Search Bar
              _buildSearchBar(),

              // Banner
              _buildBanner(),

              // Categories Section
              _buildCategoriesSection(),

              // Featured Products Section
              _buildFeaturedProductsSection(),

              // Popular Items Section
              _buildPopularItemsSection(),

              // Extra spacing for bottom navigation
              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomNavigationBar(),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Navigate to cart screen
          _showCartDialog();
        },
        backgroundColor: Colors.deepOrange,
        child: const Icon(Icons.shopping_cart, color: Colors.white),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }

// Add this method to show the checkout dialog
  void _showCheckoutDialog() {
    if (cartItems.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Your cart is empty. Add some items first!'),
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => CheckoutScreen(
          cartItems: cartItems,
          onOrderPlaced: () {
            setState(() {
              cartItems.clear();
            });
          },
        ),
      ),
    );
  }

// Update the cart dialog to include a checkout button
  void _showCartDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        double total = cartItems.fold(0, (sum, item) => sum + (item['price'] * item['quantity']));

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
                      subtitle: Text('Rwf ${item['price']} x ${item['quantity']}'),
                      trailing: Text('Rwf ${item['price'] * item['quantity']}'),
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
                  _showCheckoutDialog();
                },
                child: const Text('Checkout'),
              ),
          ],
        );
      },
    );
  }

  Widget _buildHeaderSection() {
    return Container(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "Hello Yoramu 👋",
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Poppins',
                      color: Colors.grey[800],
                    ),
                  ),
                  Text(
                    "What would you like to eat?",
                    style: TextStyle(
                      fontSize: 16,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 24.0),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
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
                hintText: 'Search for food...',
                hintStyle: TextStyle(
                  color: Colors.grey[500],
                  fontSize: 16,
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

  Widget _buildBanner() {
    return Container(
      margin: const EdgeInsets.all(24.0),
      height: 160,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.deepOrange.withOpacity(0.3),
            spreadRadius: 1,
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
        image: const DecorationImage(
          image: AssetImage('images/food.jpg'),
          fit: BoxFit.cover,
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Colors.transparent,
              Colors.black.withOpacity(0.7),
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
                'Special Offer!',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Poppins',
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Get 20% off on all salads this week',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 14,
                  fontFamily: 'Roboto',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCategoriesSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Categories",
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              fontFamily: 'Poppins',
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 100,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: categories.length,
              itemBuilder: (context, index) {
                final category = categories[index];
                bool isSelected = selectedCategory == category['name'];
                return Container(
                  width: 90,
                  margin: EdgeInsets.only(right: index == categories.length - 1 ? 0 : 12),
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
        ],
      ),
    );
  }

  Widget _buildCategoryButton(String name, String imagePath, bool isSelected, VoidCallback onTap) {
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
                  ? Colors.deepOrange.withOpacity(0.3)
                  : Colors.grey.withOpacity(0.1),
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
                color: isSelected ? Colors.white.withOpacity(0.2) : Colors.grey[100],
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

  Widget _buildFeaturedProductsSection() {
    return Container(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Featured Products",
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Poppins',
                  color: Colors.grey[800],
                ),
              ),
              TextButton(
                onPressed: () {},
                child: Text(
                  "See All",
                  style: TextStyle(
                    color: Colors.deepOrange,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: featuredProducts.length,
              itemBuilder: (context, index) {
                final product = featuredProducts[index];
                return _buildFoodCard(
                  product,
                  index,
                  'featured',
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFoodCard(Map<String, dynamic> product, int index, String section) {
    bool isFavorite = product['isFavorite'] ?? false;

    return GestureDetector(
      onTap: () => _onProductTap(product),
      child: Container(
        width: 200,
        margin: const EdgeInsets.only(right: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withOpacity(0.1),
              spreadRadius: 1,
              blurRadius: 15,
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
                  height: 140,
                  decoration: BoxDecoration(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(24),
                      topRight: Radius.circular(24),
                    ),
                    image: DecorationImage(
                      image: AssetImage(product['image'] ?? 'images/salad2.png'),
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: IconButton(
                    onPressed: () => _toggleFavorite(index, section),
                    icon: Icon(
                      isFavorite ? Icons.favorite : Icons.favorite_border,
                      color: isFavorite ? Colors.red : Colors.white,
                      size: 24,
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product['name'] ?? 'Product Name',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Poppins',
                      color: Colors.grey[800],
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product['description'] ?? 'Product description',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Rwf ${product['price']?.toString() ?? '0'}',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.deepOrange,
                          fontFamily: 'Poppins',
                        ),
                      ),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.deepOrange,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: IconButton(
                          onPressed: () => _onProductTap(product),
                          icon: const Icon(Icons.add, color: Colors.white, size: 20),
                          padding: const EdgeInsets.all(8),
                          constraints: const BoxConstraints(),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPopularItemsSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Popular Items",
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Poppins',
                  color: Colors.grey[800],
                ),
              ),
              TextButton(
                onPressed: _viewAllPopularItems,
                child: Text(
                  "View All",
                  style: TextStyle(
                    color: Colors.deepOrange,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
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
              final originalIndex = popularItems.indexWhere((p) => p['name'] == product['name']);

              return GestureDetector(
                onTap: () => _onProductTap(product),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.grey.withOpacity(0.1),
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
                            decoration: BoxDecoration(
                              borderRadius: const BorderRadius.only(
                                topLeft: Radius.circular(16),
                                topRight: Radius.circular(16),
                              ),
                              image: DecorationImage(
                                image: AssetImage(product['image'] ?? 'images/salad2.png'),
                                fit: BoxFit.cover,
                              ),
                            ),
                          ),
                          Positioned(
                            top: 4,
                            right: 4,
                            child: IconButton(
                              onPressed: () => _toggleFavorite(originalIndex, 'popular'),
                              icon: Icon(
                                isFavorite ? Icons.favorite : Icons.favorite_border,
                                color: isFavorite ? Colors.red : Colors.white,
                                size: 20,
                              ),
                            ),
                          ),
                        ],
                      ),
                      Padding(
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
                                fontSize: 12,
                                color: Colors.grey[600],
                                fontFamily: 'Roboto',
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Rwf ${product['price']?.toString() ?? '0'}',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: Colors.deepOrange,
                                fontFamily: 'Poppins',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildBottomNavigationBar() {
    return BottomAppBar(
      shape: const CircularNotchedRectangle(),
      notchMargin: 8.0,
      child: Container(
        height: 70,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildNavBarItem(Icons.home, 'Home', 0),
            _buildNavBarItem(Icons.search, 'Search', 1),
            const SizedBox(width: 40), // Space for the center FAB
            _buildNavBarItem(Icons.favorite, 'Favorites', 2),
            _buildNavBarItem(Icons.person, 'Profile', 3),
          ],
        ),
      ),
    );
  }

  Widget _buildNavBarItem(IconData icon, String label, int index) {
    return GestureDetector(
      onTap: () {
        setState(() {
          _currentBottomNavIndex = index;
        });
      },
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            color: _currentBottomNavIndex == index ? Colors.deepOrange : Colors.grey,
            size: 28,
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: _currentBottomNavIndex == index ? Colors.deepOrange : Colors.grey,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }
}