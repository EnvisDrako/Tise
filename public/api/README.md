# Mobile API Documentation

## Overview

This API provides endpoints for the Android mobile application to integrate with the CRM system. It allows enquirer users to authenticate, retrieve assigned phone numbers, and log call outcomes.

## Authentication

All API endpoints (except login) require JWT authentication using the Bearer token format:

```
Authorization: Bearer <jwt_token>
```

## Endpoints

### POST /api/login.php

Authenticate an enquirer user and receive a JWT token.

**Request:**
```json
{
    "username": "enquirer_username",
    "password": "user_password"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "jwt_token_here",
        "user": {
            "id": 123,
            "username": "enquirer_username",
            "full_name": "John Doe",
            "role": "enquirer"
        },
        "expires_in": 86400
    },
    "message": "Login successful",
    "timestamp": "2024-01-01T12:00:00Z"
}
```

### GET /api/get_numbers.php

Retrieve assigned enquiry phone numbers with filtering and pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Records per page (default: 25, max: 50)
- `status` (optional): Filter by status (new, assigned, contacted, interested, not_interested, enrolled)
- `search` (optional): Search in name, phone, or course interest

**Response:**
```json
{
    "success": true,
    "data": {
        "phone_numbers": [
            {
                "enquiry_id": 1,
                "name": "Jane Smith",
                "phone": "+1234567890",
                "email": "jane@example.com",
                "course_interest": "Web Development",
                "status": "new",
                "priority": "high",
                "is_overdue": false,
                "days_since_created": 2,
                "created_at": "2024-01-01 10:00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_records": 120,
            "per_page": 25,
            "has_next": true,
            "has_previous": false
        },
        "statistics": {
            "total_assigned": 120,
            "new_count": 45,
            "contacted_count": 30,
            "interested_count": 25,
            "enrolled_count": 15,
            "overdue_count": 5
        }
    }
}
```

### POST /api/log_call.php

Log a call outcome and duration for an enquiry.

**Request:**
```json
{
    "enquiry_id": 1,
    "call_outcome": "answered",
    "call_duration": 300,
    "notes": "Interested in course, will call back tomorrow",
    "follow_up_required": true,
    "call_timestamp": "2024-01-01T14:30:00Z"
}
```

**Valid call outcomes:**
- `answered`: Call was answered
- `no_answer`: No one answered
- `busy`: Line was busy
- `invalid`: Invalid/wrong number
- `voicemail`: Reached voicemail

**Response:**
```json
{
    "success": true,
    "data": {
        "call_log_id": 456,
        "enquiry": {
            "id": 1,
            "name": "Jane Smith",
            "phone": "+1234567890",
            "status": "contacted"
        },
        "call_details": {
            "outcome": "answered",
            "duration": 300,
            "follow_up_required": true,
            "timestamp": "2024-01-01 14:30:00"
        },
        "status_updated": true,
        "new_status": "contacted"
    },
    "message": "Call logged successfully"
}
```

## Error Handling

All endpoints return standardized error responses:

```json
{
    "success": false,
    "data": {
        "error_code": "INVALID_CREDENTIALS"
    },
    "message": "Invalid username or password",
    "timestamp": "2024-01-01T12:00:00Z"
}
```

## Rate Limiting

API requests are limited to 100 requests per hour per user. Exceeded limits return HTTP 429 with error code `RATE_LIMITED`.

## Security Features

- JWT token authentication with expiry
- Rate limiting per user
- Input validation and sanitization
- SQL injection protection
- CORS headers for web clients
- Secure password verification
- Request logging for audit trails

## Testing

In debug mode, you can access the test interface at `/api/test.php` to manually test the endpoints.

## Status Codes

- `200`: Success
- `400`: Bad Request (validation errors)
- `401`: Unauthorized (invalid/missing token)
- `403`: Forbidden (insufficient permissions)
- `404`: Not Found
- `405`: Method Not Allowed
- `429`: Too Many Requests (rate limited)
- `500`: Internal Server Error