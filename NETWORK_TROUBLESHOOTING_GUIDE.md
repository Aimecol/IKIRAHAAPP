# IKIRAHA Network Error Resolution Guide

## 🔧 **Implemented Solutions**

### ✅ **1. Environment Configuration System**
- **Added `flutter_dotenv` package** for environment management
- **Created `.env` file** with comprehensive configuration options
- **Environment class** for centralized configuration management
- **API URL configuration** now uses environment variables

### ✅ **2. Enhanced Error Handling**
- **Custom exception classes** for different error types
- **Safe JSON parsing** with null checks
- **Retry logic** for network requests
- **User-friendly error messages**

### ✅ **3. Robust API Service**
- **Enhanced HTTP request helper** with timeout and retry
- **Comprehensive logging** for debugging
- **Null safety** throughout the authentication flow
- **Proper response validation**

## 🔍 **Troubleshooting Steps**

### **Step 1: Verify IKIRAHA API Backend**

First, ensure your IKIRAHA API backend is running:

```bash
# Navigate to your XAMPP htdocs
cd C:\xampp\htdocs\ikirahaapp

# Check if Apache and MySQL are running in XAMPP Control Panel
# Open browser and test: http://localhost/ikirahaapp/ikiraha-api/public/health
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "IKIRAHA API is running",
  "timestamp": "2024-01-XX XX:XX:XX"
}
```

### **Step 2: Test API Endpoints Manually**

Test the authentication endpoints directly:

```bash
# Test login endpoint
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@ikiraha.com","password":"password"}'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "def50200...",
    "user": {
      "id": 1,
      "email": "client@ikiraha.com",
      "name": "Client User",
      "role": "client"
    }
  }
}
```

### **Step 3: Configure Environment Variables**

Update the `.env` file with your specific configuration:

```env
# Update API_BASE_URL if needed
API_BASE_URL=http://localhost/ikirahaapp/ikiraha-api/public

# For network debugging
ENABLE_LOGGING=true
ENABLE_API_LOGGING=true
LOG_LEVEL=debug

# Network timeouts (in seconds)
API_TIMEOUT=30
CONNECTION_TIMEOUT=10000
RECEIVE_TIMEOUT=30000

# Retry configuration
RETRY_ATTEMPTS=3
RETRY_DELAY=1000
```

### **Step 4: Check Network Connectivity**

Test network connectivity from Flutter app:

1. **Open Chrome DevTools** (F12)
2. **Go to Network tab**
3. **Try logging in** with demo credentials
4. **Check for failed requests**

**Common Issues:**
- **CORS errors**: Backend needs proper CORS headers
- **404 errors**: API endpoint URL is incorrect
- **Timeout errors**: API server is slow or unresponsive
- **SSL errors**: Mixed content (HTTP/HTTPS) issues

### **Step 5: Debug Flutter App**

Use the enhanced logging system:

```dart
// In your browser console, you should see:
[INFO] AuthService: Attempting login for email: client@ikiraha.com
[INFO] AuthService: Token retrieved: Found
=== API Call ===
Method: POST
URL: http://localhost/ikirahaapp/ikiraha-api/public/auth/login
Body: {"email":"client@ikiraha.com","password":"password"}
Status: 200
Response: {"success":true,"message":"Login successful",...}
===============
[INFO] AuthService: Login successful for user: client@ikiraha.com
```

## 🛠 **Common Error Solutions**

### **Error: "type 'Null' is not a subtype of type 'String'"**

**Cause**: API response contains null values being cast to String

**Solution**: ✅ **Already implemented** - Enhanced null safety in User model and API parsing

### **Error: "Network error: Please check your internet connection"**

**Possible Causes & Solutions**:

1. **XAMPP not running**
   ```bash
   # Start Apache and MySQL in XAMPP Control Panel
   ```

2. **Wrong API URL**
   ```env
   # Check .env file
   API_BASE_URL=http://localhost/ikirahaapp/ikiraha-api/public
   ```

3. **Firewall blocking requests**
   ```bash
   # Add exception for XAMPP in Windows Firewall
   ```

### **Error: "Request timeout"**

**Solutions**:
1. **Increase timeout values** in `.env`:
   ```env
   API_TIMEOUT=60
   CONNECTION_TIMEOUT=20000
   RECEIVE_TIMEOUT=60000
   ```

2. **Check server performance**:
   - Restart XAMPP
   - Check MySQL is running
   - Verify database connection

### **Error: "Invalid response format from server"**

**Solutions**:
1. **Check API response format**:
   ```bash
   # Test API directly
   curl -X GET http://localhost/ikirahaapp/ikiraha-api/public/health
   ```

2. **Verify Content-Type headers**:
   - API should return `application/json`
   - Check for HTML error pages instead of JSON

## 🧪 **Testing Procedures**

### **1. Backend API Testing**

```bash
# Test health endpoint
curl http://localhost/ikirahaapp/ikiraha-api/public/health

# Test login endpoint
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"client@ikiraha.com","password":"password"}'

# Test register endpoint
curl -X POST http://localhost/ikirahaapp/ikiraha-api/public/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","phone":"+250123456789","role":"client"}'
```

### **2. Flutter App Testing**

1. **Start the app**:
   ```bash
   flutter run -d chrome
   ```

2. **Test login with demo accounts**:
   - Client: `client@ikiraha.com` / `password`
   - Merchant: `merchant@ikiraha.com` / `password`

3. **Monitor console logs** for debugging information

4. **Test registration flow**:
   - Click "Sign Up"
   - Fill form with valid data
   - Submit and verify automatic login

### **3. Network Debugging**

1. **Chrome DevTools Network Tab**:
   - Monitor all HTTP requests
   - Check request/response headers
   - Verify response status codes

2. **Flutter DevTools**:
   - Open: `http://127.0.0.1:9101?uri=http://127.0.0.1:61260/...`
   - Check console for Dart exceptions
   - Monitor widget tree for UI issues

## 🔄 **Alternative Testing Methods**

### **Option 1: Use Postman/Insomnia**
Test API endpoints independently of Flutter app

### **Option 2: Mock API Responses**
Create mock responses for testing UI without backend

### **Option 3: Use Different Network**
Test with mobile hotspot to rule out network issues

## 📞 **Getting Help**

If issues persist:

1. **Check logs** in browser console and Flutter DevTools
2. **Verify XAMPP** Apache and MySQL are running
3. **Test API endpoints** directly with curl or Postman
4. **Check firewall settings** and antivirus software
5. **Try different network** (mobile hotspot)

## 🎯 **Success Indicators**

✅ **API Health Check** returns 200 OK
✅ **Login API** returns JWT token and user data
✅ **Flutter App** starts without errors
✅ **Authentication** works with demo accounts
✅ **Network requests** complete successfully
✅ **Error handling** shows user-friendly messages

---

**Status**: Enhanced error handling and environment configuration implemented
**Next Steps**: Test authentication flow with improved error handling
