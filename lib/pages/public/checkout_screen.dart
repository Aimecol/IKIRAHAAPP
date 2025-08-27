import 'package:flutter/material.dart';
import 'package:ikirahaapp/widgets/bottom_navigation_widget.dart';

class CheckoutScreen extends StatefulWidget {
  final List<Map<String, dynamic>> cartItems;
  final VoidCallback onOrderPlaced;

  const CheckoutScreen({
    super.key,
    required this.cartItems,
    required this.onOrderPlaced,
  });

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _addressController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _additionalPhoneController =
      TextEditingController();

  String _selectedPaymentMethod = 'MTN Rwanda';
  String _selectedAddressType = 'Home';
  List<Map<String, String>> _additionalAddresses = [];
  bool _isAddingAddress = false;

  @override
  void dispose() {
    _phoneController.dispose();
    _addressController.dispose();
    _emailController.dispose();
    _additionalPhoneController.dispose();
    super.dispose();
  }

  double get totalAmount {
    return widget.cartItems.fold(
      0,
      (sum, item) => sum + (item['price'] * item['quantity']),
    );
  }

  void _placeOrder() {
    if (_formKey.currentState!.validate()) {
      // Process the order
      showDialog(
        context: context,
        builder: (BuildContext context) {
          return AlertDialog(
            title: const Text('Order Confirmation'),
            content: Text(
              'Your order of Rwf $totalAmount has been placed successfully with $_selectedPaymentMethod.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  widget.onOrderPlaced();
                  Navigator.of(context).pop();
                },
                child: const Text('OK'),
              ),
            ],
          );
        },
      );
    }
  }

  void _addAdditionalAddress() {
    setState(() {
      _isAddingAddress = true;
    });
  }

  void _saveAdditionalAddress() {
    if (_addressController.text.isNotEmpty) {
      setState(() {
        _additionalAddresses.add({
          'type': _selectedAddressType,
          'address': _addressController.text,
          'phone': _additionalPhoneController.text,
        });
        _addressController.clear();
        _additionalPhoneController.clear();
        _isAddingAddress = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA),
      appBar: AppBar(
        title: const Text(
          'Checkout',
          style: TextStyle(
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
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              // Order Summary
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Order Summary',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),
                      ...widget.cartItems.map(
                        (item) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 4.0),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('${item['name']} x${item['quantity']}'),
                              Text('Rwf ${item['price'] * item['quantity']}'),
                            ],
                          ),
                        ),
                      ),
                      const Divider(),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total Amount:',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Rwf $totalAmount',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.deepOrange,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Payment Method
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Payment Method',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: Colors.grey.withValues(alpha: 0.3),
                            width: 1,
                          ),
                        ),
                        child: DropdownButtonFormField<String>(
                          value: _selectedPaymentMethod,
                          items: [
                            DropdownMenuItem<String>(
                              value: 'MTN Rwanda',
                              child: Row(
                                children: [
                                  Container(
                                    width: 24,
                                    height: 24,
                                    decoration: BoxDecoration(
                                      color: Colors.yellow[700],
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Center(
                                      child: Text(
                                        'M',
                                        style: TextStyle(
                                          color: Colors.black,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Text('MTN Rwanda'),
                                ],
                              ),
                            ),
                            DropdownMenuItem<String>(
                              value: 'Airtel Rwanda',
                              child: Row(
                                children: [
                                  Container(
                                    width: 24,
                                    height: 24,
                                    decoration: const BoxDecoration(
                                      color: Colors.red,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Center(
                                      child: Text(
                                        'A',
                                        style: TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Text('Airtel Rwanda'),
                                ],
                              ),
                            ),
                          ],
                          onChanged: (newValue) {
                            setState(() {
                              _selectedPaymentMethod = newValue!;
                            });
                          },
                          decoration: const InputDecoration(
                            labelText: 'Select Payment Method',
                            border: InputBorder.none,
                            contentPadding: EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 12,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        controller: _phoneController,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          labelText: 'Phone Number for Payment',
                          border: OutlineInputBorder(),
                          prefixText: '+250 ',
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter your phone number';
                          }
                          if (value.length != 9 || !value.startsWith('7')) {
                            return 'Please enter a valid Rwandan phone number';
                          }
                          return null;
                        },
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Contact Information
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Contact Information',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Email Address',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter your email address';
                          }
                          if (!value.contains('@')) {
                            return 'Please enter a valid email address';
                          }
                          return null;
                        },
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Delivery Address
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Delivery Address',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          IconButton(
                            onPressed: _addAdditionalAddress,
                            icon: const Icon(Icons.add),
                            tooltip: 'Add Additional Address',
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),

                      // Additional Addresses
                      if (_additionalAddresses.isNotEmpty) ...[
                        ..._additionalAddresses.map(
                          (address) => ListTile(
                            title: Text(
                              '${address['type']}: ${address['address']}',
                            ),
                            subtitle: Text(
                              address['phone']!.isNotEmpty
                                  ? 'Phone: ${address['phone']}'
                                  : '',
                            ),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete),
                              onPressed: () {
                                setState(() {
                                  _additionalAddresses.remove(address);
                                });
                              },
                            ),
                          ),
                        ),
                        const Divider(),
                      ],

                      // Add New Address Form
                      if (_isAddingAddress) ...[
                        DropdownButtonFormField<String>(
                          value: _selectedAddressType,
                          items: ['Home', 'Work', 'Other'].map((String value) {
                            return DropdownMenuItem<String>(
                              value: value,
                              child: Text(value),
                            );
                          }).toList(),
                          onChanged: (newValue) {
                            setState(() {
                              _selectedAddressType = newValue!;
                            });
                          },
                          decoration: const InputDecoration(
                            labelText: 'Address Type',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 10),
                        TextFormField(
                          controller: _addressController,
                          decoration: const InputDecoration(
                            labelText: 'Address (Street, Cell, Village, etc.)',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 10),
                        TextFormField(
                          controller: _additionalPhoneController,
                          keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(
                            labelText:
                                'Phone Number for this address (optional)',
                            border: OutlineInputBorder(),
                            prefixText: '+250 ',
                          ),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            TextButton(
                              onPressed: () {
                                setState(() {
                                  _isAddingAddress = false;
                                });
                              },
                              child: const Text('Cancel'),
                            ),
                            ElevatedButton(
                              onPressed: _saveAdditionalAddress,
                              child: const Text('Save Address'),
                            ),
                          ],
                        ),
                        const Divider(),
                      ],

                      // Primary Address
                      TextFormField(
                        decoration: const InputDecoration(
                          labelText: 'Primary Delivery Address',
                          border: OutlineInputBorder(),
                          hintText:
                              'Enter your complete address (street, cell, village, etc.)',
                        ),
                        maxLines: 2,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter your delivery address';
                          }
                          return null;
                        },
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 30),

              // Place Order Button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _placeOrder,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.deepOrange,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: const Text(
                    'Place Order',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: const BottomNavigationWidget(
        currentIndex: -1, // No specific tab selected for checkout screen
      ),
      floatingActionButton: Container(), // Empty container to hide default FAB
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
    );
  }
}
