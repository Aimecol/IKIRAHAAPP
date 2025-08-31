# IKIRAHA Food Delivery API

A production-ready REST API backend for the IKIRAHA food delivery mobile application, built with pure PHP and custom MVC architecture.

## Features

- **Secure Authentication**: JWT-based authentication with role-based access control
- **Four User Types**: Client, Merchant, Accountant, Super Admin
- **Complete Food Delivery System**: Products, orders, restaurants, categories
- **Security First**: SQL injection prevention, XSS protection, password hashing
- **Production Ready**: Error handling, logging, input validation
- **XAMPP Compatible**: Designed to run seamlessly in XAMPP environment

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP (recommended)

## Installation

1. **Clone/Copy the project** to your XAMPP htdocs directory:
   ```
   c:\xampp\htdocs\ikirahaapp\ikiraha-api\
   ```

2. **Start XAMPP** services (Apache and MySQL)

3. **Run the setup script** to initialize the database:
   ```bash
   cd c:\xampp\htdocs\ikirahaapp\ikiraha-api
   php setup.php
   ```

4. **Verify installation** by accessing:
   - Health check: `http://localhost/ikiraha-api/public/health`
   - API root: `http://localhost/ikiraha-api/public/`

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