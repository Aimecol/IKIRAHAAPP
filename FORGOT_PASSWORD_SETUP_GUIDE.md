# IKIRAHA Forgot Password System - Setup Guide

## 📋 **Overview**

This guide provides step-by-step instructions to set up the comprehensive forgot password system for the IKIRAHA Flutter authentication app.

## 🔧 **Backend Setup (PHP)**

### **1. Database Setup**

Run the following SQL script in your MySQL database to create the password_resets table:

```sql
-- Navigate to phpMyAdmin or your MySQL client
-- Select your IKIRAHA database
-- Run this SQL script:

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `used` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token` (`token`),
    KEY `user_id` (`user_id`),
    KEY `email` (`email`),
    KEY `expires_at` (`expires_at`),
    KEY `created_at` (`created_at`),
    CONSTRAINT `password_resets_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **2. Verify API Endpoints**

The following endpoints have been added to your API:

- **POST** `/auth/forgot-password` - Send password reset email
- **POST** `/auth/reset-password` - Reset password with token
- **GET** `/auth/validate-reset-token/{token}` - Validate reset token

### **3. Test API Endpoints**

#### **Test Forgot Password:**
```bash
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```

#### **Test Reset Password:**
```bash
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "token": "your_reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

#### **Test Token Validation:**
```bash
curl -X GET http://localhost/ikirahaapp/ikiraha-api/public/auth/validate-reset-token/your_token_here
```

### **4. Email Configuration (Optional)**

For production use, you can configure PHPMailer with Gmail SMTP:

1. **Install PHPMailer** (if not already installed):
```bash
composer require phpmailer/phpmailer
```

2. **Update the email service** in `ikiraha-api/controllers/ForgotPasswordController.php`:
   - Replace the simple `mail()` function with PHPMailer
   - Configure SMTP settings with your Gmail credentials
   - Use app-specific passwords for Gmail

## 🔧 **Frontend Setup (Flutter)**

### **1. Updated Files**

The following Flutter files have been created/updated:

#### **New Files:**
- `lib/screens/auth/forgot_password_screen.dart` - Forgot password form
- `lib/screens/auth/reset_password_screen.dart` - Password reset form

#### **Updated Files:**
- `lib/screens/auth/login_screen.dart` - Added "Forgot Password?" link
- `lib/services/auth_service.dart` - Added forgot password API methods
- `lib/main.dart` - Added routing for reset password screen

### **2. New Features Added**

#### **Login Screen:**
- ✅ "Forgot Password?" link added
- ✅ Navigation to forgot password screen

#### **Forgot Password Screen:**
- ✅ Email input with validation
- ✅ Send reset link functionality
- ✅ Success/error message handling
- ✅ Email sent confirmation state
- ✅ Resend email option
- ✅ Help information panel

#### **Reset Password Screen:**
- ✅ Token validation on load
- ✅ New password input with confirmation
- ✅ Password visibility toggles
- ✅ Form validation
- ✅ Success/error handling
- ✅ Navigation back to login

#### **Auth Service:**
- ✅ `forgotPassword()` method
- ✅ `resetPassword()` method
- ✅ `validateResetToken()` method
- ✅ Enhanced error handling

## 🧪 **Testing the System**

### **1. Test Forgot Password Flow**

1. **Start the Flutter app:**
```bash
flutter run -d chrome
```

2. **Navigate to login screen**
3. **Click "Forgot Password?" link**
4. **Enter a valid email address**
5. **Click "Send Reset Link"**
6. **Check for success message**

### **2. Test Password Reset Flow**

1. **Check your email** (or database for the token)
2. **Copy the reset token from the database**
3. **Navigate to:** `http://localhost:port/#/reset-password?token=YOUR_TOKEN`
4. **Enter new password and confirmation**
5. **Click "Reset Password"**
6. **Verify redirect to login screen**
7. **Test login with new password**

### **3. Test Error Scenarios**

#### **Invalid Email:**
- Enter non-existent email
- Should show success message (security feature)

#### **Expired Token:**
- Use a token older than 1 hour
- Should show "expired token" error

#### **Invalid Token:**
- Use a malformed token
- Should show "invalid token" error

#### **Password Mismatch:**
- Enter different passwords in confirmation
- Should show validation error

## 🔒 **Security Features**

### **1. Token Security**
- ✅ Tokens are hashed before storage
- ✅ Tokens expire after 1 hour
- ✅ Tokens can only be used once
- ✅ Old tokens are cleaned up

### **2. Rate Limiting**
- ✅ Maximum 3 reset attempts per email per hour
- ✅ File-based rate limiting implementation
- ✅ Prevents abuse and spam

### **3. Email Enumeration Protection**
- ✅ Always returns success message
- ✅ Only sends email if account exists
- ✅ Prevents email discovery attacks

### **4. Password Security**
- ✅ Minimum 6 character requirement
- ✅ Password confirmation validation
- ✅ Secure password hashing (bcrypt)

## 🚀 **Production Deployment**

### **1. Email Configuration**
- Configure proper SMTP settings
- Use environment variables for credentials
- Set up email templates with branding

### **2. Security Enhancements**
- Implement Redis for rate limiting
- Add CAPTCHA for reset requests
- Enable HTTPS for all endpoints
- Configure proper CORS settings

### **3. Monitoring**
- Log all password reset attempts
- Monitor for suspicious activity
- Set up alerts for high failure rates

## 🐛 **Troubleshooting**

### **Common Issues:**

#### **"Email not sent" error:**
- Check PHP mail configuration
- Verify SMTP settings
- Check server logs for mail errors

#### **"Token validation failed":**
- Verify database connection
- Check token format and expiry
- Ensure proper URL encoding

#### **"Network error" in Flutter:**
- Verify API endpoints are accessible
- Check CORS configuration
- Confirm base URL in environment config

#### **Database errors:**
- Ensure password_resets table exists
- Check foreign key constraints
- Verify user table structure

### **Debug Steps:**

1. **Check API logs:** `ikiraha-api/logs/error.log`
2. **Verify database:** Check password_resets table
3. **Test endpoints:** Use curl or Postman
4. **Check Flutter logs:** Monitor console output
5. **Verify environment:** Confirm API base URL

## ✅ **Success Checklist**

- [ ] Database table created successfully
- [ ] API endpoints respond correctly
- [ ] Flutter app compiles without errors
- [ ] Forgot password link works
- [ ] Email reset flow completes
- [ ] Password reset form validates
- [ ] New password login works
- [ ] Error handling displays properly
- [ ] Rate limiting prevents abuse
- [ ] Security features are active

## 📞 **Support**

If you encounter issues:

1. Check the troubleshooting section above
2. Verify all setup steps were completed
3. Test individual components separately
4. Check logs for specific error messages
5. Ensure all dependencies are installed

The forgot password system is now fully integrated and ready for use! 🎉
