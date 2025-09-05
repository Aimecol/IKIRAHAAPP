# IKIRAHA API Documentation

A comprehensive RESTful API for the IKIRAHA food delivery application built with PHP and MySQL. This API supports multiple user roles (Client, Merchant, Accountant, Super Admin) and provides complete functionality for a modern food delivery platform.

## 🚀 Features

- **Multi-role Authentication & Authorization** (JWT-based)
- **Complete User Management** (Registration, Profile, Addresses)
- **Restaurant & Menu Management**
- **Order Processing & Tracking**
- **Payment Transaction Handling**
- **Real-time Notifications**
- **User Favorites System**
- **Comprehensive Admin Dashboard Support**
- **Mobile Money Integration** (MTN Rwanda, Airtel Rwanda)

## 📋 Table of Contents

- [Installation](#installation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Request/Response Format](#requestresponse-format)
- [Error Handling](#error-handling)
- [User Roles & Permissions](#user-roles--permissions)
- [Database Schema](#database-schema)
- [Testing](#testing)

## 🛠 Installation

### Prerequisites
- XAMPP with PHP 7.4+ and MySQL 5.7+
- Composer (optional, for dependencies)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/Aimecol/IKIRAHAAPP.git
   cd IKIRAHAAPP/ikiraha-api
   ```

2. **Database Setup**
   ```bash
   # Start XAMPP services (Apache & MySQL)
   # Import the database schema
   mysql -u root -p < database/schema.sql
   ```

3. **Configuration**
   ```php
   // Update config/database.php with your database credentials
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ikiraha_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **File Permissions**
   ```bash
   chmod 755 logs/
   chmod 755 uploads/
   ```

5. **Access the API**
   ```
   Base URL: http://localhost/ikirahaapp/ikiraha-api/public/
   ```

## 🔐 Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```http
Authorization: Bearer <your-jwt-token>
```

### Token Lifecycle
- **Access Token**: 1 hour expiry
- **Refresh Token**: 7 days expiry
- **Auto-cleanup**: Expired tokens are automatically removed

## 📚 API Endpoints

### 🏥 Health Check
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/` | API status | No |
| GET | `/health` | Detailed health check | No |

### 👤 Authentication & User Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| POST | `/auth/register` | Register new user | No | - |
| POST | `/auth/login` | User login | No | - |
| POST | `/auth/logout` | User logout | Yes | All |
| POST | `/auth/refresh` | Refresh JWT token | Yes | All |
| GET | `/auth/profile` | Get user profile | Yes | All |
| PUT | `/auth/profile` | Update user profile | Yes | All |
| POST | `/auth/change-password` | Change password | Yes | All |

### 🏪 Restaurant Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/restaurants` | Get all restaurants | No | - |
| GET | `/restaurants/{id}` | Get restaurant by ID | No | - |
| POST | `/restaurants` | Create restaurant | Yes | Merchant, Super Admin |
| PUT | `/restaurants/{id}` | Update restaurant | Yes | Merchant, Super Admin |
| DELETE | `/restaurants/{id}` | Delete restaurant | Yes | Merchant, Super Admin |
| GET | `/restaurants/merchant/{id}` | Get restaurants by merchant | Yes | All |
| GET | `/my-restaurants` | Get my restaurants | Yes | Merchant |

### 🍕 Product Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/products` | Get all products | No | - |
| GET | `/products/{id}` | Get product by ID | No | - |
| POST | `/products` | Create product | Yes | Merchant, Super Admin |
| PUT | `/products/{id}` | Update product | Yes | Merchant, Super Admin |
| DELETE | `/products/{id}` | Delete product | Yes | Merchant, Super Admin |
| GET | `/products/featured` | Get featured products | No | - |
| GET | `/products/search` | Search products | No | - |

### 🏷 Category Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/categories` | Get all categories | No | - |
| GET | `/categories/{id}` | Get category by ID | No | - |
| POST | `/categories` | Create category | Yes | Super Admin |
| PUT | `/categories/{id}` | Update category | Yes | Super Admin |
| DELETE | `/categories/{id}` | Delete category | Yes | Super Admin |
| GET | `/categories/with-count` | Get categories with product count | No | - |

### 📦 Order Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| POST | `/orders` | Create new order | Yes | Client |
| GET | `/orders` | Get user orders | Yes | Client |
| GET | `/orders/{id}` | Get order by ID | Yes | All |
| PUT | `/orders/{id}/status` | Update order status | Yes | Merchant, Super Admin |
| GET | `/orders/all` | Get all orders | Yes | Super Admin, Accountant |
| GET | `/restaurants/{id}/orders` | Get restaurant orders | Yes | Merchant, Super Admin |

### 🏠 User Address Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/addresses` | Get user addresses | Yes | Client, Merchant |
| GET | `/addresses/{id}` | Get address by ID | Yes | Client, Merchant |
| POST | `/addresses` | Create address | Yes | Client, Merchant |
| PUT | `/addresses/{id}` | Update address | Yes | Client, Merchant |
| DELETE | `/addresses/{id}` | Delete address | Yes | Client, Merchant |
| PUT | `/addresses/{id}/default` | Set default address | Yes | Client, Merchant |
| GET | `/addresses/default` | Get default address | Yes | Client, Merchant |

### 💳 Transaction Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/transactions` | Get all transactions | Yes | Super Admin, Accountant |
| GET | `/transactions/{id}` | Get transaction by ID | Yes | Super Admin, Accountant, Merchant |
| GET | `/transactions/order/{id}` | Get order transactions | Yes | All |
| POST | `/transactions` | Create transaction | Yes | Super Admin, Merchant |
| PUT | `/transactions/{id}/status` | Update transaction status | Yes | Super Admin, Merchant |
| GET | `/transactions/stats` | Get transaction statistics | Yes | Super Admin, Accountant |

### 🔔 Notification Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/notifications` | Get user notifications | Yes | All |
| POST | `/notifications` | Create notification | Yes | Super Admin |
| POST | `/notifications/bulk` | Create bulk notifications | Yes | Super Admin |
| PUT | `/notifications/{id}/read` | Mark as read | Yes | All |
| PUT | `/notifications/read-all` | Mark all as read | Yes | All |
| DELETE | `/notifications/{id}` | Delete notification | Yes | All |
| GET | `/notifications/unread-count` | Get unread count | Yes | All |
| DELETE | `/notifications/old` | Clear old notifications | Yes | Super Admin |

### ❤️ Favorite Management
| Method | Endpoint | Description | Auth Required | Roles |
|--------|----------|-------------|---------------|-------|
| GET | `/favorites` | Get user favorites | Yes | Client |
| POST | `/favorites` | Add to favorites | Yes | Client |
| DELETE | `/favorites/{id}` | Remove from favorites | Yes | Client |
| PUT | `/favorites/{id}/toggle` | Toggle favorite status | Yes | Client |
| GET | `/favorites/{id}/status` | Check favorite status | Yes | Client |
| GET | `/favorites/count` | Get favorite count | Yes | Client |
| GET | `/favorites/popular` | Get popular products | No | - |
| DELETE | `/favorites/clear` | Clear all favorites | Yes | Client |

## 📝 Request/Response Format

### Request Headers
```http
Content-Type: application/json
Authorization: Bearer <jwt-token>  // For authenticated endpoints
```

### Standard Response Format
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  },
  "count": 10,  // For list responses
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

## 🚨 Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

### Common Error Codes
- `BAD_REQUEST` - Invalid request data
- `UNAUTHORIZED` - Authentication required
- `FORBIDDEN` - Insufficient permissions
- `NOT_FOUND` - Resource not found
- `INTERNAL_ERROR` - Server error

## 👥 User Roles & Permissions

### Client
- Register and manage profile
- Browse restaurants and products
- Place and track orders
- Manage delivery addresses
- Add products to favorites
- Receive notifications

### Merchant
- Manage restaurants
- Manage products and menu
- Process orders
- View transaction history
- Manage delivery addresses

### Accountant
- View all transactions
- Generate financial reports
- View order statistics
- Access transaction analytics

### Super Admin
- Full system access
- User management
- Category management
- System notifications
- Data cleanup operations

## 🗄 Database Schema

The API uses 11 main database tables:

1. **users** - User accounts and profiles
2. **user_addresses** - User delivery addresses
3. **restaurants** - Restaurant information
4. **categories** - Food categories
5. **products** - Menu items and products
6. **orders** - Customer orders
7. **order_items** - Order line items
8. **transactions** - Payment records
9. **user_favorites** - User favorite products
10. **auth_tokens** - JWT tokens
11. **notifications** - User notifications

## 🧪 Testing

### API Testing Tools
- **Postman** - Recommended for API testing
- **cURL** - Command line testing
- **Browser** - For GET endpoints

### Sample API Calls

#### Register User
```bash
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "+250788123456",
    "role": "client"
  }'
```

#### Login
```bash
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Get Products
```bash
curl -X GET "http://localhost/ikirahaapp/ikiraha-api/public/products?limit=10&category_id=1"
```

#### Create Order (Authenticated)
```bash
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-jwt-token>" \
  -d '{
    "restaurant_id": 1,
    "items": [
      {"product_id": 1, "quantity": 2, "price": 3500}
    ],
    "delivery_address": "Kigali, Rwanda",
    "delivery_phone": "+250788123456",
    "payment_method": "mtn_rwanda",
    "payment_phone": "+250788123456"
  }'
```

## 📞 Support

For technical support or questions about the API:
- **Email**: aimecol314@gmail.com
- **GitHub**: https://github.com/Aimecol/IKIRAHAAPP

## 📄 License

This project is licensed under the MIT License.

## 📊 API Statistics

### Total Endpoints: 64
- **Authentication**: 7 endpoints
- **Restaurants**: 7 endpoints
- **Products**: 7 endpoints
- **Categories**: 6 endpoints
- **Orders**: 6 endpoints
- **Addresses**: 7 endpoints
- **Transactions**: 6 endpoints
- **Notifications**: 8 endpoints
- **Favorites**: 8 endpoints
- **Health Check**: 2 endpoints

### Database Entities: 11 Tables
- Complete relational database design
- Foreign key constraints for data integrity
- Optimized indexes for performance
- Support for soft deletes where appropriate

### Security Features
- JWT-based authentication
- Role-based access control (RBAC)
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- CORS configuration

## 🔧 Development Guidelines

### Code Structure
```
ikiraha-api/
├── config/           # Configuration files
├── controllers/      # API controllers
├── models/          # Database models
├── middleware/      # Authentication middleware
├── utils/           # Utility functions
├── database/        # Database schema
├── logs/            # Application logs
├── uploads/         # File uploads
└── public/          # Public entry point
```

### Naming Conventions
- **Models**: PascalCase (e.g., `UserAddress.php`)
- **Controllers**: PascalCase + Controller (e.g., `RestaurantController.php`)
- **Methods**: camelCase (e.g., `getUserAddresses()`)
- **Database Tables**: snake_case (e.g., `user_addresses`)
- **API Endpoints**: kebab-case (e.g., `/user-addresses`)

### Best Practices
1. **Always validate input data**
2. **Use prepared statements for database queries**
3. **Implement proper error handling**
4. **Log important operations**
5. **Follow RESTful conventions**
6. **Maintain consistent response formats**

## 🚀 Deployment

### Production Checklist
- [ ] Update JWT secret key
- [ ] Configure production database
- [ ] Set up SSL certificate
- [ ] Configure proper file permissions
- [ ] Set up log rotation
- [ ] Configure backup strategy
- [ ] Test all endpoints
- [ ] Monitor performance

### Environment Variables
```php
// Production settings
define('APP_ENV', 'production');
define('JWT_SECRET', 'your-secure-production-secret');
define('DB_HOST', 'your-production-db-host');
define('DB_NAME', 'your-production-db-name');
define('DB_USER', 'your-production-db-user');
define('DB_PASS', 'your-production-db-password');
```

## 📈 Performance Optimization

### Database Optimization
- Proper indexing on frequently queried columns
- Query optimization for complex joins
- Connection pooling for high traffic
- Regular database maintenance

### Caching Strategy
- Implement Redis/Memcached for session storage
- Cache frequently accessed data
- Use HTTP caching headers
- Optimize image delivery

### Monitoring
- Set up application performance monitoring
- Log slow queries and errors
- Monitor API response times
- Track user activity and system usage

---

**IKIRAHA API v1.0.0** - Built with ❤️ for Rwanda's food delivery ecosystem
