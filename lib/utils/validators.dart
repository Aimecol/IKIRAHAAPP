import 'constants.dart';

class Validators {
  // Email validation
  static String? validateEmail(String? value) {
    if (value == null || value.isEmpty) {
      return 'Email is required';
    }
    
    if (!RegExp(AppConstants.emailRegex).hasMatch(value)) {
      return 'Please enter a valid email address';
    }
    
    return null;
  }

  // Password validation
  static String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    
    if (value.length < AppConstants.minPasswordLength) {
      return 'Password must be at least ${AppConstants.minPasswordLength} characters';
    }
    
    if (value.length > AppConstants.maxPasswordLength) {
      return 'Password must be less than ${AppConstants.maxPasswordLength} characters';
    }
    
    // Check for at least one letter and one number
    if (!RegExp(r'^(?=.*[A-Za-z])(?=.*\d)').hasMatch(value)) {
      return 'Password must contain at least one letter and one number';
    }
    
    return null;
  }

  // Confirm password validation
  static String? validateConfirmPassword(String? value, String? password) {
    if (value == null || value.isEmpty) {
      return 'Please confirm your password';
    }
    
    if (value != password) {
      return 'Passwords do not match';
    }
    
    return null;
  }

  // Name validation
  static String? validateName(String? value) {
    if (value == null || value.isEmpty) {
      return 'Name is required';
    }
    
    if (value.length < AppConstants.minNameLength) {
      return 'Name must be at least ${AppConstants.minNameLength} characters';
    }
    
    if (value.length > AppConstants.maxNameLength) {
      return 'Name must be less than ${AppConstants.maxNameLength} characters';
    }
    
    // Check for valid characters (letters, spaces, hyphens, apostrophes)
    if (!RegExp(r"^[a-zA-Z\s\-']+$").hasMatch(value)) {
      return 'Name can only contain letters, spaces, hyphens, and apostrophes';
    }
    
    return null;
  }

  // Phone number validation (Rwanda format)
  static String? validatePhone(String? value) {
    if (value == null || value.isEmpty) {
      return 'Phone number is required';
    }
    
    // Remove spaces and hyphens for validation
    String cleanPhone = value.replaceAll(RegExp(r'[\s\-]'), '');
    
    if (!RegExp(AppConstants.phoneRegex).hasMatch(cleanPhone)) {
      return 'Please enter a valid Rwanda phone number';
    }
    
    return null;
  }

  // Optional phone validation (can be empty)
  static String? validateOptionalPhone(String? value) {
    if (value == null || value.isEmpty) {
      return null; // Optional field
    }
    
    return validatePhone(value);
  }

  // Required field validation
  static String? validateRequired(String? value, String fieldName) {
    if (value == null || value.isEmpty) {
      return '$fieldName is required';
    }
    
    return null;
  }

  // Address validation
  static String? validateAddress(String? value) {
    if (value == null || value.isEmpty) {
      return 'Address is required';
    }
    
    if (value.length < 10) {
      return 'Please enter a more detailed address';
    }
    
    if (value.length > 500) {
      return 'Address is too long';
    }
    
    return null;
  }

  // Amount validation
  static String? validateAmount(String? value) {
    if (value == null || value.isEmpty) {
      return 'Amount is required';
    }
    
    final amount = double.tryParse(value);
    if (amount == null) {
      return 'Please enter a valid amount';
    }
    
    if (amount <= 0) {
      return 'Amount must be greater than 0';
    }
    
    if (amount > 1000000) {
      return 'Amount is too large';
    }
    
    return null;
  }

  // Quantity validation
  static String? validateQuantity(String? value) {
    if (value == null || value.isEmpty) {
      return 'Quantity is required';
    }
    
    final quantity = int.tryParse(value);
    if (quantity == null) {
      return 'Please enter a valid quantity';
    }
    
    if (quantity <= 0) {
      return 'Quantity must be greater than 0';
    }
    
    if (quantity > 100) {
      return 'Quantity cannot exceed 100';
    }
    
    return null;
  }

  // URL validation
  static String? validateUrl(String? value) {
    if (value == null || value.isEmpty) {
      return null; // Optional field
    }
    
    if (!RegExp(r'^https?://').hasMatch(value)) {
      return 'URL must start with http:// or https://';
    }
    
    try {
      Uri.parse(value);
      return null;
    } catch (e) {
      return 'Please enter a valid URL';
    }
  }

  // Description validation
  static String? validateDescription(String? value, {int maxLength = 1000}) {
    if (value == null || value.isEmpty) {
      return null; // Optional field
    }
    
    if (value.length > maxLength) {
      return 'Description must be less than $maxLength characters';
    }
    
    return null;
  }

  // Rating validation
  static String? validateRating(String? value) {
    if (value == null || value.isEmpty) {
      return null; // Optional field
    }
    
    final rating = double.tryParse(value);
    if (rating == null) {
      return 'Please enter a valid rating';
    }
    
    if (rating < 0 || rating > 5) {
      return 'Rating must be between 0 and 5';
    }
    
    return null;
  }

  // Time validation (HH:MM format)
  static String? validateTime(String? value) {
    if (value == null || value.isEmpty) {
      return 'Time is required';
    }
    
    if (!RegExp(r'^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$').hasMatch(value)) {
      return 'Please enter time in HH:MM format';
    }
    
    return null;
  }

  // Date validation
  static String? validateDate(String? value) {
    if (value == null || value.isEmpty) {
      return 'Date is required';
    }
    
    try {
      DateTime.parse(value);
      return null;
    } catch (e) {
      return 'Please enter a valid date';
    }
  }

  // Future date validation
  static String? validateFutureDate(String? value) {
    final dateError = validateDate(value);
    if (dateError != null) return dateError;
    
    final date = DateTime.parse(value!);
    if (date.isBefore(DateTime.now())) {
      return 'Date must be in the future';
    }
    
    return null;
  }

  // Past date validation
  static String? validatePastDate(String? value) {
    final dateError = validateDate(value);
    if (dateError != null) return dateError;
    
    final date = DateTime.parse(value!);
    if (date.isAfter(DateTime.now())) {
      return 'Date must be in the past';
    }
    
    return null;
  }

  // Credit card validation (basic)
  static String? validateCreditCard(String? value) {
    if (value == null || value.isEmpty) {
      return 'Card number is required';
    }
    
    // Remove spaces and hyphens
    String cleanCard = value.replaceAll(RegExp(r'[\s\-]'), '');
    
    if (!RegExp(r'^\d{13,19}$').hasMatch(cleanCard)) {
      return 'Please enter a valid card number';
    }
    
    // Luhn algorithm validation
    if (!_isValidLuhn(cleanCard)) {
      return 'Please enter a valid card number';
    }
    
    return null;
  }

  // CVV validation
  static String? validateCVV(String? value) {
    if (value == null || value.isEmpty) {
      return 'CVV is required';
    }
    
    if (!RegExp(r'^\d{3,4}$').hasMatch(value)) {
      return 'CVV must be 3 or 4 digits';
    }
    
    return null;
  }

  // Expiry date validation (MM/YY format)
  static String? validateExpiryDate(String? value) {
    if (value == null || value.isEmpty) {
      return 'Expiry date is required';
    }
    
    if (!RegExp(r'^(0[1-9]|1[0-2])\/\d{2}$').hasMatch(value)) {
      return 'Please enter date in MM/YY format';
    }
    
    final parts = value.split('/');
    final month = int.parse(parts[0]);
    final year = int.parse('20${parts[1]}');
    
    final now = DateTime.now();
    final expiry = DateTime(year, month);
    
    if (expiry.isBefore(DateTime(now.year, now.month))) {
      return 'Card has expired';
    }
    
    return null;
  }

  // Helper method for Luhn algorithm
  static bool _isValidLuhn(String cardNumber) {
    int sum = 0;
    bool alternate = false;
    
    for (int i = cardNumber.length - 1; i >= 0; i--) {
      int digit = int.parse(cardNumber[i]);
      
      if (alternate) {
        digit *= 2;
        if (digit > 9) {
          digit = (digit % 10) + 1;
        }
      }
      
      sum += digit;
      alternate = !alternate;
    }
    
    return sum % 10 == 0;
  }

  // Format phone number for display
  static String formatPhoneNumber(String phone) {
    // Remove all non-digit characters
    String digits = phone.replaceAll(RegExp(r'\D'), '');
    
    // Add Rwanda country code if not present
    if (digits.length == 9) {
      digits = '250$digits';
    } else if (digits.startsWith('0') && digits.length == 10) {
      digits = '250${digits.substring(1)}';
    }
    
    // Format as +250 XXX XXX XXX
    if (digits.length == 12 && digits.startsWith('250')) {
      return '+${digits.substring(0, 3)} ${digits.substring(3, 6)} ${digits.substring(6, 9)} ${digits.substring(9)}';
    }
    
    return phone; // Return original if formatting fails
  }

  // Clean phone number for API
  static String cleanPhoneNumber(String phone) {
    // Remove all non-digit characters
    String digits = phone.replaceAll(RegExp(r'\D'), '');
    
    // Add Rwanda country code if not present
    if (digits.length == 9) {
      return '+250$digits';
    } else if (digits.startsWith('0') && digits.length == 10) {
      return '+250${digits.substring(1)}';
    } else if (digits.length == 12 && digits.startsWith('250')) {
      return '+$digits';
    }
    
    return phone; // Return original if cleaning fails
  }
}
