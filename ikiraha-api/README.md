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