# IKIRAHA Food Delivery System

A complete food delivery platform consisting of a Flutter mobile app, PHP REST API backend, and web dashboard. This system supports clients, merchants, accountants, and super admins with secure authentication and comprehensive order management.

## System Overview

The IKIRAHA system consists of three main components:

1. **Mobile App** (Flutter/Dart) - For clients to browse and order food
2. **Backend API** (PHP) - RESTful API handling all business logic
3. **Web Dashboard** (HTML/CSS/JS) - For merchants, accountants, and admins

## System Requirements

### Development Environment
- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **Flutter SDK** (3.0+)
- **Android Studio** or **VS Code** with Flutter extensions
- **Web Browser** (Chrome, Firefox, Safari)

### Mobile Development
- **Android Studio** with Android SDK
- **Android Emulator** or **Physical Android Device**
- **iOS Simulator** (Mac only) or **Physical iOS Device**

### Production Environment
- **Web Server** (Apache/Nginx)
- **PHP 7.4+** with extensions: PDO, MySQL, JSON, OpenSSL
- **MySQL 5.7+** or **MariaDB 10.3+**
- **SSL Certificate** (for HTTPS)
- **Domain Name** (for production deployment)

## Complete System Setup Guide

### Step 1: Backend API Setup

#### 1.1 Install XAMPP
1. Download and install **XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install to default location: `C:\xampp\`

#### 1.2 Setup Project Files
1. **Copy the project** to XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\ikirahaapp\
   ```

2. **Verify directory structure**:
   ```
   C:\xampp\htdocs\ikirahaapp\
   ├── lib\                    (Flutter mobile app)
   ├── lib\pages\dashboard\    (Web dashboard)
   └── ikiraha-api\           (PHP backend API)
   ```

#### 1.3 Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** service
3. Start **MySQL** service
4. Verify both services show "Running" status

#### 1.4 Initialize Database
1. Open **Command Prompt** or **Terminal**
2. Navigate to the API directory:
   ```bash
   cd C:\xampp\htdocs\ikirahaapp\ikiraha-api
   ```
3. Run the database setup script:
   ```bash
   php setup.php
   ```
4. You should see success messages for database creation

#### 1.5 Verify Backend API
1. Open web browser and test these URLs:
   - **API Health Check**: `http://localhost/ikirahaapp/ikiraha-api/public/health`
   - **API Root**: `http://localhost/ikirahaapp/ikiraha-api/public/`
   - **Test Endpoint**: `http://localhost/ikirahaapp/ikiraha-api/public/test.php`

2. **Expected Response** (Health Check):
   ```json
   {
     "success": true,
     "data": {
       "status": "healthy",
       "timestamp": "2024-01-01 12:00:00",
       "version": "1.0.0",
       "checks": {
         "database": {
           "status": "healthy",
           "message": "Database connection successful"
         }
       }
     }
   }
   ```

## Default Test Accounts

After setup, you can use these test accounts:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@ikiraha.com | password |
| Merchant | merchant@ikiraha.com | password |
| Accountant | accountant@ikiraha.com | password |
| Client | client@ikiraha.com | password |

## API Endpoints

### Authentication
- `POST /auth/register` - Register new user (client/merchant)
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `POST /auth/refresh` - Refresh access token
- `GET /auth/profile` - Get user profile
- `PUT /auth/profile` - Update user profile
- `POST /auth/change-password` - Change password

### Products
- `GET /products` - Get all products (with filters)
- `GET /products/{id}` - Get product by ID
- `POST /products` - Create product (merchant only)
- `PUT /products/{id}` - Update product (merchant only)
- `DELETE /products/{id}` - Delete product (merchant only)
- `GET /products/featured` - Get featured products
- `GET /products/search?q={term}` - Search products

### Categories
- `GET /categories` - Get all categories

### Orders
- `POST /orders` - Create new order (client only)
- `GET /orders` - Get user's orders (client only)
- `GET /orders/{id}` - Get order by ID
- `PUT /orders/{id}/status` - Update order status (merchant/admin)
- `GET /orders/all` - Get all orders (admin/accountant only)
- `GET /restaurants/{id}/orders` - Get restaurant orders (merchant only)

### Health Check
- `GET /` - API information
- `GET /health` - System health check

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_jwt_token}
```

## Request/Response Format

### Request Format
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

### Success Response Format
```json
{
  "success": true,
  "data": {
    "message": "Operation successful",
    "result": {}
  },
  "timestamp": "2024-01-01 12:00:00"
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "timestamp": "2024-01-01 12:00:00"
}
```

## Example API Usage

### 1. User Registration
```bash
curl -X POST http://localhost/ikiraha-api/public/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "+250788123456",
    "role": "client"
  }'
```

### 2. User Login
```bash
curl -X POST http://localhost/ikiraha-api/public/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 3. Get Products
```bash
curl -X GET "http://localhost/ikiraha-api/public/products?limit=10&category_id=1"
```

### 4. Create Order (requires authentication)
```bash
curl -X POST http://localhost/ikiraha-api/public/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "restaurant_id": 1,
    "items": [
      {
        "product_id": 1,
        "quantity": 2,
        "price": 2700
      }
    ],
    "payment_method": "mtn_rwanda",
    "payment_phone": "+250788123456",
    "delivery_address": "Kigali, Rwanda",
    "delivery_phone": "+250788123456",
    "notes": "Please call when you arrive"
  }'
```

## Database Schema

The system uses the following main tables:

- **users**: User accounts (clients, merchants, accountants, admins)
- **restaurants**: Restaurant information managed by merchants
- **categories**: Product categories (Pizza, Burger, Salad, etc.)
- **products**: Menu items with pricing and availability
- **orders**: Customer orders with status tracking
- **order_items**: Individual items within orders
- **transactions**: Payment transaction records
- **auth_tokens**: JWT refresh token storage
- **user_addresses**: Customer delivery addresses
- **user_favorites**: User favorite products
- **notifications**: System notifications

## Security Features

- **Password Hashing**: Uses PHP's password_hash() with strong algorithms
- **JWT Authentication**: Secure token-based authentication
- **SQL Injection Prevention**: All queries use prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based request validation
- **Role-Based Access Control**: Granular permissions per user type
- **Input Validation**: Comprehensive data validation
- **Error Logging**: Detailed error logging for debugging

## File Structure

```
ikiraha-api/
├── config/
│   ├── config.php          # Main configuration
│   └── database.php        # Database connection
├── controllers/
│   ├── AuthController.php  # Authentication endpoints
│   ├── ProductController.php # Product management
│   ├── OrderController.php # Order management
│   └── HealthController.php # Health checks
├── models/
│   ├── User.php           # User model
│   ├── Product.php        # Product model
│   └── Order.php          # Order model
├── middleware/
│   └── AuthMiddleware.php # Authentication middleware
├── utils/
│   └── JWT.php            # JWT utility functions
├── database/
│   └── schema.sql         # Database schema
├── public/
│   ├── index.php          # Main API entry point
│   └── .htaccess          # URL rewriting rules
├── logs/                  # Application logs
├── uploads/               # File uploads
├── setup.php              # Database setup script
└── README.md              # This file
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`
   - Verify database exists by running setup script

2. **404 Not Found**
   - Ensure mod_rewrite is enabled in Apache
   - Check .htaccess file exists in public directory
   - Verify correct URL format

3. **Permission Denied**
   - Check file permissions on logs and uploads directories
   - Ensure Apache has write access to required directories

4. **JWT Token Issues**
   - Check token expiration (default 1 hour)
   - Verify Authorization header format: `Bearer {token}`
   - Use refresh token to get new access token

## Production Deployment

For production deployment:

1. Change JWT secret in `config/config.php`
2. Update database credentials
3. Set `APP_ENV` to 'production'
4. Configure proper SSL/HTTPS
5. Set up proper error logging
6. Configure backup procedures
7. Implement rate limiting
8. Set up monitoring and alerts

## Support

This backend system is designed to work seamlessly with the IKIRAHA mobile application and dashboard. It provides all necessary endpoints for a complete food delivery system with proper security, validation, and error handling.

The system is production-ready and requires no further modifications to run directly once deployed in a XAMPP environment.