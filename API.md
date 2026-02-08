# 🌐 Hyro API Documentation

Complete REST API reference for Hyro package.

---

## 📋 Table of Contents

- [Authentication](#authentication)
- [Users](#users)
- [Roles](#roles)
- [Privileges](#privileges)
- [Suspensions](#suspensions)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)

---

## 🔐 Authentication

### Login

Authenticate and receive an API token.

**Endpoint:** `POST /api/hyro/auth/login`

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:** `200 OK`
```json
{
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "roles": ["admin"],
    "privileges": ["users.*", "roles.*"]
  }
}
```

### Register

Create a new user account.

**Endpoint:** `POST /api/hyro/auth/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response:** `201 Created`
```json
{
  "token": "2|xyz789...",
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Logout

Revoke the current API token.

**Endpoint:** `POST /api/hyro/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

### Refresh Token

Refresh the current API token.

**Endpoint:** `POST /api/hyro/auth/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "token": "3|new_token..."
}
```

---

## 👥 Users

### List Users

Get a paginated list of users.

**Endpoint:** `GET /api/hyro/users`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page (max: 100)
- `search` (string): Search by name or email
- `role` (string): Filter by role
- `suspended` (boolean): Filter suspended users

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "roles": ["admin"],
      "is_suspended": false,
      "created_at": "2026-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### Get User

Get a specific user by ID.

**Endpoint:** `GET /api/hyro/users/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "roles": [
    {
      "id": 1,
      "name": "Admin",
      "slug": "admin"
    }
  ],
  "privileges": ["users.*", "roles.*"],
  "is_suspended": false,
  "created_at": "2026-01-01T00:00:00.000000Z",
  "updated_at": "2026-01-01T00:00:00.000000Z"
}
```

### Create User

Create a new user.

**Endpoint:** `POST /api/hyro/users`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password",
  "roles": ["editor"]
}
```

**Response:** `201 Created`
```json
{
  "id": 3,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "roles": ["editor"],
  "created_at": "2026-02-08T00:00:00.000000Z"
}
```

### Update User

Update an existing user.

**Endpoint:** `PUT /api/hyro/users/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com"
}
```

**Response:** `200 OK`
```json
{
  "id": 3,
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "updated_at": "2026-02-08T12:00:00.000000Z"
}
```

### Delete User

Delete a user.

**Endpoint:** `DELETE /api/hyro/users/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `204 No Content`

---

## 🎭 Roles

### List Roles

Get all roles.

**Endpoint:** `GET /api/hyro/roles`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin",
      "slug": "admin",
      "description": "Administrator role",
      "is_protected": true,
      "privileges_count": 25
    }
  ]
}
```

### Get Role

Get a specific role.

**Endpoint:** `GET /api/hyro/roles/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "Admin",
  "slug": "admin",
  "description": "Administrator role",
  "is_protected": true,
  "privileges": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "users.view"
    }
  ],
  "users_count": 5
}
```

### Create Role

Create a new role.

**Endpoint:** `POST /api/hyro/roles`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Editor",
  "slug": "editor",
  "description": "Content editor role",
  "privileges": ["posts.create", "posts.edit"]
}
```

**Response:** `201 Created`
```json
{
  "id": 4,
  "name": "Editor",
  "slug": "editor",
  "description": "Content editor role",
  "created_at": "2026-02-08T00:00:00.000000Z"
}
```

### Update Role

Update an existing role.

**Endpoint:** `PUT /api/hyro/roles/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Senior Editor",
  "description": "Senior content editor role"
}
```

**Response:** `200 OK`

### Delete Role

Delete a role (cannot delete protected roles).

**Endpoint:** `DELETE /api/hyro/roles/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `204 No Content`

### Assign Role to User

Assign a role to a user.

**Endpoint:** `POST /api/hyro/users/{userId}/roles`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "role": "editor",
  "expires_at": "2027-02-08T00:00:00.000000Z"
}
```

**Response:** `200 OK`
```json
{
  "message": "Role assigned successfully"
}
```

### Revoke Role from User

Remove a role from a user.

**Endpoint:** `DELETE /api/hyro/users/{userId}/roles/{roleSlug}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "message": "Role revoked successfully"
}
```

---

## 🔑 Privileges

### List Privileges

Get all privileges.

**Endpoint:** `GET /api/hyro/privileges`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `category` (string): Filter by category

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "View Users",
      "slug": "users.view",
      "category": "users",
      "is_wildcard": false
    }
  ]
}
```

### Get Privilege

Get a specific privilege.

**Endpoint:** `GET /api/hyro/privileges/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "id": 1,
  "name": "View Users",
  "slug": "users.view",
  "description": "Can view user list",
  "category": "users",
  "is_wildcard": false,
  "roles": [
    {
      "id": 1,
      "name": "Admin",
      "slug": "admin"
    }
  ]
}
```

### Create Privilege

Create a new privilege.

**Endpoint:** `POST /api/hyro/privileges`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Publish Posts",
  "slug": "posts.publish",
  "description": "Can publish posts",
  "category": "posts"
}
```

**Response:** `201 Created`

### Grant Privilege to Role

Grant a privilege to a role.

**Endpoint:** `POST /api/hyro/roles/{roleId}/privileges`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "privilege": "posts.publish"
}
```

**Response:** `200 OK`

### Revoke Privilege from Role

Remove a privilege from a role.

**Endpoint:** `DELETE /api/hyro/roles/{roleId}/privileges/{privilegeSlug}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`

---

## 🚫 Suspensions

### Suspend User

Suspend a user account.

**Endpoint:** `POST /api/hyro/users/{id}/suspend`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "reason": "Policy violation",
  "duration_days": 7
}
```

**Response:** `200 OK`
```json
{
  "message": "User suspended successfully",
  "suspended_until": "2026-02-15T00:00:00.000000Z"
}
```

### Unsuspend User

Reactivate a suspended user.

**Endpoint:** `POST /api/hyro/users/{id}/unsuspend`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "message": "User unsuspended successfully"
}
```

---

## ⚠️ Error Handling

### Error Response Format

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created
- `204 No Content` - Successful deletion
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

### Common Errors

**Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## 🚦 Rate Limiting

### Default Limits

- **API Endpoints:** 60 requests per minute
- **Authentication:** 5 attempts per minute

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1644307200
```

### Rate Limit Exceeded

**Response:** `429 Too Many Requests`
```json
{
  "message": "Too many requests. Please try again later."
}
```

---

## 📚 Additional Resources

- [Installation Guide](INSTALLATION.md)
- [Configuration Guide](CONFIGURATION.md)
- [Usage Guide](USAGE.md)
- [README](README.md)

---

**API Version:** 1.0.0-beta  
**Last Updated:** February 8, 2026
