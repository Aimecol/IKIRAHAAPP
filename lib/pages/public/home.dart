import 'package:flutter/material.dart';
import 'package:ikirahaapp/pages/public/product_details_screen.dart';
import 'package:ikirahaapp/pages/public/category_screen.dart';
import 'package:ikirahaapp/pages/public/checkout_screen.dart';
import 'package:ikirahaapp/pages/public/restaurant_detail_screen.dart';
import 'package:ikirahaapp/pages/public/see_all_screen.dart';
import 'package:ikirahaapp/pages/public/cart_screen.dart';
import 'package:ikirahaapp/pages/public/profile_screen.dart';
import 'package:ikirahaapp/pages/public/search_screen.dart';

class Home extends StatefulWidget {
  const Home({super.key});

  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> with TickerProviderStateMixin {
  String selectedCategory = '';
  int _currentBottomNavIndex = 0;
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> filteredProducts = [];
  List<Map<String, dynamic>> cartItems = [];

  // Banner related variables
  PageController _bannerPageController = PageController();
  int _currentBannerIndex = 0;
  late AnimationController _bannerAnimationController;
  late Animation<double> _bannerAnimation;

  // Category data with navigation functionality
  final List<Map<String, String>> categories = [
    {'name': 'Ice Cream', 'icon': 'images/ice-cream.png'},
    {'name': 'Pizza', 'icon': 'images/pizza.png'},
    {'name': 'Salad', 'icon': 'images/salad.png'},
    {'name': 'Burger', 'icon': 'images/burger.png'},
    {'name': 'Sushi', 'icon': 'images/salad.png'},
    {'name': 'Pasta', 'icon': 'images/pizza.png'},
  ];

  // Banner data for multiple banners
  final List<Map<String, String>> banners = [
    {
      'image': 'images/food.jpg',
      'title': 'Special Offer!',
      'subtitle': 'Get 20% off on all salads this week',
    },
    {
      'image': 'images/burger.png',
      'title': 'New Burgers!',
      'subtitle': 'Try our delicious new burger collection',
    },
    {
      'image': 'images/pizza.png',
      'title': 'Pizza Party!',
      'subtitle': 'Buy 2 pizzas and get 1 free today only',
    },
    {
      'image': 'images/ice-cream.png',
      'title': 'Sweet Treats!',
      'subtitle': 'Cool down with our premium ice cream',
    },
  ];

  // Restaurant data
  final List<Map<String, dynamic>> restaurants = [
    {
      'id': 1,
      'name': 'Avuma Restaurant',
      'image': 'images/avuma-restaurant.jpg',
      'rating': 4.5,
      'deliveryTime': '25-30 min',
      'deliveryFee': 500,
      'categories': ['Pizza', 'Burger', 'Salad'],
      'description': 'Delicious Italian and American cuisine',
    },
    {
      'id': 2,
      'name': 'Blessing Restaurant',
      'image': 'images/blessing-restaurant.jpg',
      'rating': 4.3,
      'deliveryTime': '20-25 min',
      'deliveryFee': 300,
      'categories': ['Sushi', 'Pasta', 'Salad'],
      'description': 'Fresh sushi and Asian fusion',
    },
    {
      'id': 3,
      'name': 'Glass Restaurant',
      'image': 'images/glass-restaurant.jpg',
      'rating': 4.7,
      'deliveryTime': '30-35 min',
      'deliveryFee': 700,
      'categories': ['Ice Cream', 'Burger', 'Pizza'],
      'description': 'Premium dining experience',
    },
    {
      'id': 4,
      'name': 'Ice Restaurant',
      'image': 'images/ice-restaurant.jpg',
      'rating': 4.2,
      'deliveryTime': '15-20 min',
      'deliveryFee': 200,
      'categories': ['Ice Cream', 'Salad'],
      'description': 'Cool treats and healthy options',
    },
    {
      'id': 5,
      'name': 'Ikigugu Restaurant',
      'image': 'images/ikigugu-restaurant.jpg',
      'rating': 4.6,
      'deliveryTime': '25-30 min',
      'deliveryFee': 400,
      'categories': ['Pasta', 'Pizza', 'Burger'],
      'description': 'Traditional and modern cuisine',
    },
    {
      'id': 6,
      'name': 'Peace Restaurant',
      'image': 'images/peace-restaurant.webp',
      'rating': 4.4,
      'deliveryTime': '20-25 min',
      'deliveryFee': 350,
      'categories': ['Sushi', 'Salad', 'Ice Cream'],
      'description': 'Peaceful dining with great food',
    },
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
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
    {
      'name': 'Margherita Pizza',
      'price': 4200,
      'category': 'Pizza',
      'description': 'Classic pizza with tomato and fresh mozzarella cheese',
      'image': 'images/salad3.png',
      'isFavorite': false,
    },
    {
      'name': 'Vanilla Ice Cream',
      'price': 1800,
      'category': 'Ice Cream',
      'description': 'Creamy vanilla ice cream with chocolate toppings',
      'image': 'images/salad4.png',
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
      'image': 'images/salad3.png',
      'isFavorite': false,
    },
    {
      'name': 'Chocolate Sundae',
      'price': 2200,
      'category': 'Ice Cream',
      'description': 'Vanilla ice cream with hot fudge and nuts',
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
    {
      'name': 'Bacon Cheeseburger',
      'price': 3800,
      'category': 'Burger',
      'description': 'Beef patty with crispy bacon and melted cheese',
      'image': 'images/salad4.png',
      'isFavorite': false,
    },
    {
      'name': 'California Roll',
      'price': 3200,
      'category': 'Sushi',
      'description': 'Fresh crab with avocado and cucumber',
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
    {
      'name': 'Caesar Pasta',
      'price': 3100,
      'category': 'Pasta',
      'description': 'Creamy Caesar sauce with pasta and parmesan',
      'image': 'images/salad3.png',
      'isFavorite': false,
    },
    {
      'name': 'Spicy Tuna Roll',
      'price': 3400,
      'category': 'Sushi',
      'description': 'Spicy tuna with cucumber and sesame seeds',
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
    {
      'name': 'Veggie Burger',
      'price': 3300,
      'category': 'Burger',
      'description': 'Plant-based patty with fresh vegetables',
      'image': 'images/salad4.png',
      'isFavorite': false,
    },
    {
      'name': 'Chocolate Chip Ice Cream',
      'price': 2000,
      'category': 'Ice Cream',
      'description': 'Creamy ice cream with chocolate chunks',
      'image': 'images/salad2.png',
      'isFavorite': false,
    },
  ];

  @override
  void initState() {
    super.initState();
    filteredProducts = List.from(popularItems);
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
          _currentBannerIndex = (_currentBannerIndex + 1) % banners.length;
        });
        _bannerPageController.animateToPage(
          _currentBannerIndex,
          duration: const Duration(milliseconds: 500),
          curve: Curves.easeInOut,
        );
        _startBannerAutoSlide(); // Continue the cycle
      }
    });
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
          products: popularItems
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
    // Check if product already exists in cart
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
        featuredProducts[index]['isFavorite'] =
            !(featuredProducts[index]['isFavorite'] ?? false);
      } else {
        popularItems[index]['isFavorite'] =
            !(popularItems[index]['isFavorite'] ?? false);
        // Update filtered products as well
        final productName = popularItems[index]['name'];
        final filteredIndex = filteredProducts.indexWhere(
          (p) => p['name'] == productName,
        );
        if (filteredIndex != -1) {
          filteredProducts[filteredIndex]['isFavorite'] =
              popularItems[index]['isFavorite'];
        }
      }
    });
  }

  void _viewAllPopularItems() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => SeeAllScreen(
          title: 'All Popular Items',
          items: popularItems,
          itemType: 'products',
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

              // Banner
              _buildBanner(),

              // Categories Section
              _buildCategoriesSection(),

              // Restaurants Section
              _buildRestaurantsSection(),

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
      floatingActionButton: Container(
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
                  cartItems: cartItems,
                  onCartUpdated: (updatedCart) {
                    setState(() {
                      cartItems = updatedCart;
                    });
                  },
                ),
              ),
            );
          },
          backgroundColor: Colors.transparent,
          elevation: 0,
          child: Stack(
            alignment: Alignment.center,
            children: [
              const Icon(
                Icons.shopping_bag_outlined,
                color: Colors.white,
                size: 24,
              ),
              if (cartItems.isNotEmpty)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      cartItems.length.toString(),
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

      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }

  String _getGreeting() {
    final hour = DateTime.now().hour;
    if (hour >= 5 && hour < 12) {
      return "Good Morning 🌅";
    } else if (hour >= 12 && hour < 17) {
      return "Good Afternoon ☀️";
    } else if (hour >= 17 && hour < 21) {
      return "Good Evening 🌆";
    } else {
      return "Good Night 🌙";
    }
  }

  Widget _buildHeaderSection() {
    return Container(
      padding: const EdgeInsets.all(8.0),
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
                    _getGreeting(),
                    style: TextStyle(
                      fontSize: 25,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Poppins',
                      color: Colors.grey[800],
                    ),
                  ),
                  Text(
                    "What would you like to eat?",
                    style: TextStyle(
                      fontSize: 14,
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

  Widget _buildBanner() {
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
              itemCount: banners.length,
              itemBuilder: (context, index) {
                final banner = banners[index];
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
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'Poppins',
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  banner['subtitle']!,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 14,
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
          // Banner indicators
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: banners.asMap().entries.map((entry) {
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
            "Food Categories",
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
                  // ignore: deprecated_member_use
                  ? Colors.deepOrange.withOpacity(0.3)
                  // ignore: deprecated_member_use
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
                color: isSelected
                    // ignore: deprecated_member_use
                    ? Colors.white.withOpacity(0.2)
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

  Widget _buildRestaurantsSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Restaurants",
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Poppins',
                  color: Colors.grey[800],
                ),
              ),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => SeeAllScreen(
                        title: 'All Restaurants',
                        items: restaurants,
                        itemType: 'restaurants',
                      ),
                    ),
                  );
                },
                child: const Text(
                  "See All",
                  style: TextStyle(
                    color: Colors.deepOrange,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 200,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: restaurants.length,
              itemBuilder: (context, index) {
                final restaurant = restaurants[index];
                return _buildRestaurantCard(restaurant);
              },
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildRestaurantCard(Map<String, dynamic> restaurant) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) =>
                RestaurantDetailScreen(restaurant: restaurant),
          ),
        );
      },
      child: Container(
        width: 280,
        margin: const EdgeInsets.only(right: 16),
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
            Container(
              height: 120,
              decoration: BoxDecoration(
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(16),
                  topRight: Radius.circular(16),
                ),
                image: DecorationImage(
                  image: AssetImage(restaurant['image']),
                  fit: BoxFit.cover,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    restaurant['name'],
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Poppins',
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    restaurant['description'],
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(Icons.star, color: Colors.amber, size: 16),
                      const SizedBox(width: 4),
                      Text(
                        restaurant['rating'].toString(),
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Icon(
                        Icons.access_time,
                        color: Colors.grey[600],
                        size: 16,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        restaurant['deliveryTime'],
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const Spacer(),
                      Text(
                        'Rwf ${restaurant['deliveryFee']}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.deepOrange,
                          fontWeight: FontWeight.w600,
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

  Widget _buildFeaturedProductsSection() {
    return Container(
      padding: const EdgeInsets.all(14.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Today's Trends",
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Poppins',
                  color: Colors.grey[800],
                ),
              ),
              TextButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => SeeAllScreen(
                        title: "Today's Trends",
                        items: featuredProducts,
                        itemType: 'products',
                      ),
                    ),
                  );
                },
                child: const Text(
                  "See All",
                  style: TextStyle(
                    color: Colors.deepOrange,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: featuredProducts.length,
              itemBuilder: (context, index) {
                final product = featuredProducts[index];
                return _buildFoodCard(product, index, 'featured');
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFoodCard(
    Map<String, dynamic> product,
    int index,
    String section,
  ) {
    bool isFavorite = product['isFavorite'] ?? false;

    return GestureDetector(
      onTap: () => _onProductTap(product),
      child: Container(
        width: 180,
        height: 280,
        margin: const EdgeInsets.only(right: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withValues(alpha: 0.1),
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
                  height: 120,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(20),
                      topRight: Radius.circular(20),
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
                  top: 6,
                  right: 6,
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.9),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.1),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: IconButton(
                      onPressed: () => _toggleFavorite(index, section),
                      icon: Icon(
                        isFavorite ? Icons.favorite : Icons.favorite_border,
                        color: isFavorite ? Colors.red : Colors.grey[600],
                        size: 16,
                      ),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ),
                ),
              ],
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
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
                      product['description'] ?? 'Product description',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey[600],
                        fontFamily: 'Roboto',
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Expanded(
                          child: Text(
                            'Rwf ${product['price']?.toString() ?? '0'}',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: Colors.deepOrange,
                              fontFamily: 'Poppins',
                            ),
                          ),
                        ),
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            color: Colors.deepOrange,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.deepOrange.withValues(alpha: 0.3),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: IconButton(
                            onPressed: () => _addToCart(product, 1),
                            icon: const Icon(
                              Icons.add,
                              color: Colors.white,
                              size: 14,
                            ),
                            padding: EdgeInsets.zero,
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
  }

  Widget _buildPopularItemsSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "All Items",
                style: TextStyle(
                  fontSize: 20,
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
              final originalIndex = popularItems.indexWhere(
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
                            top: 6,
                            right: 6,
                            child: Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.9),
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.1),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: IconButton(
                                onPressed: () =>
                                    _toggleFavorite(originalIndex, 'popular'),
                                icon: Icon(
                                  isFavorite
                                      ? Icons.favorite
                                      : Icons.favorite_border,
                                  color: isFavorite
                                      ? Colors.red
                                      : Colors.grey[600],
                                  size: 16,
                                ),
                                padding: EdgeInsets.zero,
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
                                    width: 28,
                                    height: 28,
                                    decoration: BoxDecoration(
                                      color: Colors.deepOrange,
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.deepOrange.withValues(
                                            alpha: 0.3,
                                          ),
                                          blurRadius: 4,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: IconButton(
                                      onPressed: () => _addToCart(product, 1),
                                      icon: const Icon(
                                        Icons.add,
                                        color: Colors.white,
                                        size: 14,
                                      ),
                                      padding: EdgeInsets.zero,
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
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildBottomNavigationBar() {
    return BottomAppBar(
      height: 70,
      color: Colors.white,
      elevation: 8,
      shadowColor: Colors.black.withValues(alpha: 0.1),
      shape: const CircularNotchedRectangle(),
      notchMargin: 8.0,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildNavBarItem(Icons.home_outlined, Icons.home, 'Home', 0),
            _buildNavBarItem(Icons.search_outlined, Icons.search, 'Search', 1),
            const SizedBox(width: 40), // Space for the center FAB
            _buildNavBarItem(
              Icons.favorite_outline,
              Icons.favorite,
              'Favorites',
              2,
            ),
            _buildNavBarItem(Icons.menu_outlined, Icons.menu, 'Menu', 3),
          ],
        ),
      ),
    );
  }

  Widget _buildNavBarItem(
    IconData outlinedIcon,
    IconData filledIcon,
    String label,
    int index,
  ) {
    final isSelected = _currentBottomNavIndex == index;

    return GestureDetector(
      onTap: () {
        setState(() {
          _currentBottomNavIndex = index;
        });

        // Handle navigation based on index
        switch (index) {
          case 0: // Home - already on home, do nothing
            break;
          case 1: // Search
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => SearchScreen(
                  products: popularItems,
                  restaurants: restaurants,
                ),
              ),
            );
            break;
          case 2: // Favorites
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Favorites - Coming Soon!')),
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
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 200),
              child: Icon(
                isSelected ? filledIcon : outlinedIcon,
                key: ValueKey(isSelected),
                color: isSelected ? Colors.deepOrange : Colors.grey[600],
                size: 24,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.deepOrange : Colors.grey[600],
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                letterSpacing: -0.2,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
