# Mobile API Documentation

## Integrated Web Portal & CRM System - Mobile API Guide

### Table of Contents
1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [API Endpoints](#api-endpoints)
4. [Request/Response Format](#requestresponse-format)
5. [Error Handling](#error-handling)
6. [Rate Limiting](#rate-limiting)
7. [Security](#security)
8. [Code Examples](#code-examples)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)

---

## API Overview

The Mobile API provides secure access to the CRM system for mobile applications, specifically designed for enquirer users to manage their assigned leads and log call activities.

### Base URL
```
https://your-domain.com/api/
```

### API Version
Current Version: `v1.0`

### Supported Features
- User authentication with JWT tokens
- Retrieve assigned phone numbers for calling
- Log call outcomes and duration
- Update enquiry status
- Retrieve call history

### Content Type
All API requests and responses use JSON format:
```
Content-Type: application/json
```

---

## Authentication

### Authentication Method
The API uses JWT (JSON Web Token) based authentication for secure access.

### Login Endpoint
**POST** `/api/login.php`

#### Request Body
```json
{
    "username": "enquirer_username",
    "password": "user_password"
}
```

#### Success Response
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user_id": 123,
        "username": "enquirer_username",
        "full_name": "John Doe",
        "role": "enquirer",
        "expires_at": "2024-01-01T12:00:00Z"
    },
    "timestamp": "2024-01-01T10:00:00Z"
}
```

#### Error Response
```json
{
    "success": false,
    "message": "Invalid credentials",
    "error_code": "AUTH_FAILED",
    "timestamp": "2024-01-01T10:00:00Z"
}
```

### Token Usage
Include the JWT token in the Authorization header for all subsequent requests:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Token Expiration
- **Default Expiration**: 24 hours
- **Refresh**: Re-authenticate when token expires
- **Validation**: Server validates token on each request

---

## API Endpoints

### 1. User Authentication

#### Login
**POST** `/api/login.php`

Authenticates user and returns JWT token.

**Parameters**: None (credentials in request body)

**Request Body**:
```json
{
    "username": "string",
    "password": "string"
}
```

**Response**: JWT token and user information

---

### 2. Phone Number Retrieval

#### Get Assigned Phone Numbers
**GET** `/api/get_numbers.php`

Retrieves phone numbers of enquiries assigned to the authenticated user.

**Headers**:
```
Authorization: Bearer {jwt_token}
```

**Query Parameters**:
- `limit` (optional): Number of records to return (default: 50, max: 100)
- `offset` (optional): Number of records to skip (default: 0)
- `status` (optional): Filter by enquiry status (new, assigned, contacted, etc.)

**Example Request**:
```
GET /api/get_numbers.php?limit=20&status=assigned
```

**Success Response**:
```json
{
    "success": true,
    "message": "Phone numbers retrieved successfully",
    "data": {
        "enquiries": [
            {
                "enquiry_id": 123,
                "full_name": "Jane Smith",
                "phone": "+1234567890",
                "course_interest": "Web Development",
                "status": "assigned",
                "assigned_date": "2024-01-01T09:00:00Z",
                "last_contact": null,
                "priority": "normal"
            },
            {
                "enquiry_id": 124,
                "full_name": "Bob Johnson",
                "phone": "+1234567891",
                "course_interest": "Data Science",
                "status": "contacted",
                "assigned_date": "2024-01-01T08:00:00Z",
                "last_contact": "2024-01-01T14:30:00Z",
                "priority": "high"
            }
        ],
        "pagination": {
            "total": 45,
            "limit": 20,
            "offset": 0,
            "has_more": true
        }
    },
    "timestamp": "2024-01-01T10:00:00Z"
}
```

---

### 3. Call Logging

#### Log Call Activity
**POST** `/api/log_call.php`

Logs call outcome and duration for an enquiry.

**Headers**:
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "enquiry_id": 123,
    "call_duration": 180,
    "call_outcome": "answered",
    "notes": "Interested in web development course. Scheduled follow-up call.",
    "follow_up_required": true,
    "follow_up_date": "2024-01-02T10:00:00Z"
}
```

**Field Descriptions**:
- `enquiry_id`: ID of the enquiry being called
- `call_duration`: Duration in seconds (0 for unanswered calls)
- `call_outcome`: One of: "answered", "no_answer", "busy", "invalid"
- `notes`: Optional call notes
- `follow_up_required`: Boolean indicating if follow-up is needed
- `follow_up_date`: ISO 8601 formatted date for follow-up (if required)

**Success Response**:
```json
{
    "success": true,
    "message": "Call logged successfully",
    "data": {
        "call_log_id": 456,
        "enquiry_id": 123,
        "updated_status": "contacted",
        "call_timestamp": "2024-01-01T10:15:00Z"
    },
    "timestamp": "2024-01-01T10:15:00Z"
}
```

---

### 4. Call History

#### Get Call History
**GET** `/api/call_history.php`

Retrieves call history for the authenticated user.

**Headers**:
```
Authorization: Bearer {jwt_token}
```

**Query Parameters**:
- `limit` (optional): Number of records (default: 50, max: 100)
- `offset` (optional): Records to skip (default: 0)
- `enquiry_id` (optional): Filter by specific enquiry
- `date_from` (optional): Start date (YYYY-MM-DD format)
- `date_to` (optional): End date (YYYY-MM-DD format)
- `outcome` (optional): Filter by call outcome

**Example Request**:
```
GET /api/call_history.php?limit=10&date_from=2024-01-01&outcome=answered
```

**Success Response**:
```json
{
    "success": true,
    "message": "Call history retrieved successfully",
    "data": {
        "call_logs": [
            {
                "call_log_id": 456,
                "enquiry_id": 123,
                "enquiry_name": "Jane Smith",
                "phone": "+1234567890",
                "call_duration": 180,
                "call_outcome": "answered",
                "notes": "Interested in web development course",
                "follow_up_required": true,
                "follow_up_date": "2024-01-02T10:00:00Z",
                "call_timestamp": "2024-01-01T10:15:00Z"
            }
        ],
        "pagination": {
            "total": 25,
            "limit": 10,
            "offset": 0,
            "has_more": true
        },
        "statistics": {
            "total_calls": 25,
            "answered_calls": 18,
            "total_duration": 3600,
            "average_duration": 200
        }
    },
    "timestamp": "2024-01-01T11:00:00Z"
}
```

---

### 5. Status Update

#### Update Enquiry Status
**PUT** `/api/update_status.php`

Updates the status of an enquiry.

**Headers**:
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "enquiry_id": 123,
    "status": "interested",
    "notes": "Showed strong interest, wants to enroll next month"
}
```

**Valid Status Values**:
- `new`: Newly assigned
- `contacted`: Initial contact made
- `interested`: Prospect shows interest
- `not_interested`: Prospect declined
- `enrolled`: Successfully converted

**Success Response**:
```json
{
    "success": true,
    "message": "Status updated successfully",
    "data": {
        "enquiry_id": 123,
        "old_status": "contacted",
        "new_status": "interested",
        "updated_at": "2024-01-01T11:30:00Z"
    },
    "timestamp": "2024-01-01T11:30:00Z"
}
```

---

## Request/Response Format

### Standard Response Structure
All API responses follow this structure:

```json
{
    "success": boolean,
    "message": "string",
    "data": object|array|null,
    "error_code": "string (only on errors)",
    "timestamp": "ISO 8601 datetime"
}
```

### HTTP Status Codes
- **200 OK**: Successful request
- **201 Created**: Resource created successfully
- **400 Bad Request**: Invalid request parameters
- **401 Unauthorized**: Authentication required or failed
- **403 Forbidden**: Access denied
- **404 Not Found**: Resource not found
- **429 Too Many Requests**: Rate limit exceeded
- **500 Internal Server Error**: Server error

### Date/Time Format
All dates and times use ISO 8601 format:
```
2024-01-01T10:00:00Z
```

---

## Error Handling

### Error Response Format
```json
{
    "success": false,
    "message": "Human-readable error message",
    "error_code": "MACHINE_READABLE_CODE",
    "details": {
        "field": "Additional error details"
    },
    "timestamp": "2024-01-01T10:00:00Z"
}
```

### Common Error Codes

#### Authentication Errors
- `AUTH_REQUIRED`: Authentication token required
- `AUTH_INVALID`: Invalid or expired token
- `AUTH_FAILED`: Login credentials incorrect
- `AUTH_EXPIRED`: Token has expired

#### Validation Errors
- `VALIDATION_FAILED`: Request validation failed
- `MISSING_PARAMETER`: Required parameter missing
- `INVALID_PARAMETER`: Parameter value invalid
- `INVALID_FORMAT`: Data format incorrect

#### Resource Errors
- `RESOURCE_NOT_FOUND`: Requested resource not found
- `ACCESS_DENIED`: Insufficient permissions
- `RESOURCE_CONFLICT`: Resource conflict (duplicate, etc.)

#### System Errors
- `RATE_LIMIT_EXCEEDED`: Too many requests
- `SERVER_ERROR`: Internal server error
- `SERVICE_UNAVAILABLE`: Service temporarily unavailable

### Error Handling Best Practices
1. **Check HTTP Status**: Always check HTTP status code first
2. **Parse Error Response**: Extract error_code and message
3. **Handle Specific Errors**: Implement specific handling for common errors
4. **User-Friendly Messages**: Show appropriate messages to users
5. **Retry Logic**: Implement retry for temporary errors

---

## Rate Limiting

### Rate Limits
- **Authentication**: 10 requests per minute per IP
- **Data Retrieval**: 100 requests per hour per user
- **Call Logging**: 200 requests per hour per user
- **Status Updates**: 50 requests per hour per user

### Rate Limit Headers
Response headers indicate current rate limit status:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### Rate Limit Exceeded Response
```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "error_code": "RATE_LIMIT_EXCEEDED",
    "details": {
        "limit": 100,
        "reset_time": "2024-01-01T11:00:00Z"
    },
    "timestamp": "2024-01-01T10:45:00Z"
}
```

---

## Security

### Security Measures
1. **HTTPS Only**: All API calls must use HTTPS
2. **JWT Authentication**: Secure token-based authentication
3. **Input Validation**: All inputs validated and sanitized
4. **SQL Injection Protection**: Parameterized queries used
5. **XSS Protection**: Output encoding implemented
6. **Rate Limiting**: Prevents abuse and DoS attacks

### Security Headers
Required security headers in requests:
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
User-Agent: YourApp/1.0
```

### Data Privacy
- **Minimal Data**: Only necessary data is returned
- **Access Control**: Users can only access their assigned data
- **Audit Logging**: All API calls are logged for security
- **Data Encryption**: Sensitive data encrypted in transit and at rest

---

## Code Examples

### Android (Java) Example

#### Authentication
```java
public class APIClient {
    private static final String BASE_URL = "https://your-domain.com/api/";
    private String authToken;
    
    public void login(String username, String password, LoginCallback callback) {
        JSONObject credentials = new JSONObject();
        try {
            credentials.put("username", username);
            credentials.put("password", password);
        } catch (JSONException e) {
            callback.onError("JSON Error: " + e.getMessage());
            return;
        }
        
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST,
            BASE_URL + "login.php",
            credentials,
            response -> {
                try {
                    if (response.getBoolean("success")) {
                        JSONObject data = response.getJSONObject("data");
                        authToken = data.getString("token");
                        callback.onSuccess(authToken);
                    } else {
                        callback.onError(response.getString("message"));
                    }
                } catch (JSONException e) {
                    callback.onError("Parse Error: " + e.getMessage());
                }
            },
            error -> callback.onError("Network Error: " + error.getMessage())
        );
        
        // Add request to queue
        Volley.newRequestQueue(context).add(request);
    }
}
```

#### Get Phone Numbers
```java
public void getPhoneNumbers(int limit, PhoneNumbersCallback callback) {
    String url = BASE_URL + "get_numbers.php?limit=" + limit;
    
    JsonObjectRequest request = new JsonObjectRequest(
        Request.Method.GET,
        url,
        null,
        response -> {
            try {
                if (response.getBoolean("success")) {
                    JSONObject data = response.getJSONObject("data");
                    JSONArray enquiries = data.getJSONArray("enquiries");
                    
                    List<Enquiry> enquiryList = new ArrayList<>();
                    for (int i = 0; i < enquiries.length(); i++) {
                        JSONObject enquiry = enquiries.getJSONObject(i);
                        enquiryList.add(new Enquiry(
                            enquiry.getInt("enquiry_id"),
                            enquiry.getString("full_name"),
                            enquiry.getString("phone"),
                            enquiry.getString("course_interest"),
                            enquiry.getString("status")
                        ));
                    }
                    
                    callback.onSuccess(enquiryList);
                } else {
                    callback.onError(response.getString("message"));
                }
            } catch (JSONException e) {
                callback.onError("Parse Error: " + e.getMessage());
            }
        },
        error -> callback.onError("Network Error: " + error.getMessage())
    ) {
        @Override
        public Map<String, String> getHeaders() {
            Map<String, String> headers = new HashMap<>();
            headers.put("Authorization", "Bearer " + authToken);
            return headers;
        }
    };
    
    Volley.newRequestQueue(context).add(request);
}
```

#### Log Call
```java
public void logCall(int enquiryId, int duration, String outcome, String notes, CallLogCallback callback) {
    JSONObject callData = new JSONObject();
    try {
        callData.put("enquiry_id", enquiryId);
        callData.put("call_duration", duration);
        callData.put("call_outcome", outcome);
        callData.put("notes", notes);
    } catch (JSONException e) {
        callback.onError("JSON Error: " + e.getMessage());
        return;
    }
    
    JsonObjectRequest request = new JsonObjectRequest(
        Request.Method.POST,
        BASE_URL + "log_call.php",
        callData,
        response -> {
            try {
                if (response.getBoolean("success")) {
                    callback.onSuccess(response.getString("message"));
                } else {
                    callback.onError(response.getString("message"));
                }
            } catch (JSONException e) {
                callback.onError("Parse Error: " + e.getMessage());
            }
        },
        error -> callback.onError("Network Error: " + error.getMessage())
    ) {
        @Override
        public Map<String, String> getHeaders() {
            Map<String, String> headers = new HashMap<>();
            headers.put("Authorization", "Bearer " + authToken);
            headers.put("Content-Type", "application/json");
            return headers;
        }
    };
    
    Volley.newRequestQueue(context).add(request);
}
```

### JavaScript/React Native Example

#### API Client Setup
```javascript
class APIClient {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.authToken = null;
    }
    
    async login(username, password) {
        try {
            const response = await fetch(`${this.baseURL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.authToken = data.data.token;
                return { success: true, token: this.authToken };
            } else {
                return { success: false, error: data.message };
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    async getPhoneNumbers(limit = 50) {
        try {
            const response = await fetch(`${this.baseURL}/get_numbers.php?limit=${limit}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.authToken}`,
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    async logCall(enquiryId, duration, outcome, notes) {
        try {
            const response = await fetch(`${this.baseURL}/log_call.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.authToken}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    enquiry_id: enquiryId,
                    call_duration: duration,
                    call_outcome: outcome,
                    notes: notes
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// Usage
const apiClient = new APIClient('https://your-domain.com/api');

// Login
const loginResult = await apiClient.login('username', 'password');
if (loginResult.success) {
    console.log('Login successful');
    
    // Get phone numbers
    const phoneNumbers = await apiClient.getPhoneNumbers(20);
    console.log('Phone numbers:', phoneNumbers);
    
    // Log a call
    const callResult = await apiClient.logCall(123, 180, 'answered', 'Interested in course');
    console.log('Call logged:', callResult);
}
```

---

## Testing

### API Testing Tools
1. **Postman**: GUI-based API testing
2. **cURL**: Command-line testing
3. **Insomnia**: Alternative GUI tool
4. **Unit Tests**: Automated testing in your app

### Test Environment
- **Base URL**: `https://test.your-domain.com/api/`
- **Test Credentials**: Provided separately
- **Rate Limits**: Relaxed for testing

### Sample cURL Commands

#### Login
```bash
curl -X POST https://your-domain.com/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test_enquirer","password":"test123"}'
```

#### Get Phone Numbers
```bash
curl -X GET "https://your-domain.com/api/get_numbers.php?limit=10" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Log Call
```bash
curl -X POST https://your-domain.com/api/log_call.php \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "enquiry_id": 123,
    "call_duration": 180,
    "call_outcome": "answered",
    "notes": "Test call log"
  }'
```

### Testing Checklist
- [ ] Authentication works correctly
- [ ] Token expiration handled properly
- [ ] Phone numbers retrieved successfully
- [ ] Call logging functions correctly
- [ ] Error handling works as expected
- [ ] Rate limiting respected
- [ ] Network error handling implemented

---

## Troubleshooting

### Common Issues

#### Authentication Problems
**Issue**: "Authentication required" error
**Solutions**:
- Verify JWT token is included in Authorization header
- Check token format: `Bearer {token}`
- Ensure token hasn't expired
- Verify login credentials are correct

#### Network Connectivity
**Issue**: Network timeout or connection errors
**Solutions**:
- Check internet connectivity
- Verify API base URL is correct
- Ensure HTTPS is used
- Check firewall/proxy settings

#### Invalid Responses
**Issue**: Unexpected response format
**Solutions**:
- Verify Content-Type header is set correctly
- Check request body JSON format
- Ensure all required parameters are included
- Validate parameter values

#### Rate Limiting
**Issue**: "Rate limit exceeded" error
**Solutions**:
- Implement exponential backoff
- Check rate limit headers
- Reduce request frequency
- Cache responses when possible

### Debug Mode
Enable debug logging in your app to troubleshoot issues:

```javascript
// Enable debug logging
const DEBUG = true;

function debugLog(message, data) {
    if (DEBUG) {
        console.log(`[API Debug] ${message}`, data);
    }
}

// Use in API calls
debugLog('Making API request', { url, method, headers, body });
```

### Support Contacts
- **API Support**: api-support@your-domain.com
- **Technical Issues**: tech-support@your-domain.com
- **Documentation**: docs@your-domain.com

---

## Changelog

### Version 1.0 (Current)
- Initial API release
- JWT authentication
- Phone number retrieval
- Call logging functionality
- Status updates
- Call history

### Planned Features
- Push notifications
- Offline sync capability
- Advanced filtering options
- Real-time updates
- File attachments

---

**End of API Documentation**

This documentation covers the complete Mobile API for the Integrated Web Portal & CRM system. For additional support or feature requests, please contact the development team.