# API Documentation

**Base URL:** `http://127.0.0.1:8000/api`

**Content-Type:** `application/json`

**Authentication header (protected routes):**

```
Authorization: Bearer {token}
```

---

## Authentication

### Register

| | |
|---|---|
| **Method** | `POST` |
| **URL** | `/api/auth/register` |
| **Authentication Required** | No |

#### Request Body

| Field | Type | Rules |
|---|---|---|
| `name` | string | required, max:255 |
| `email` | string | required, email, max:255, unique in `users` table |
| `password` | string | required, min:8 |
| `password_confirmation` | string | required (must match `password`) |

#### Request Example

```json
{
  "name": "Ahmed Ali",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Success Response — `201 Created`

```json
{
  "success": true,
  "message": "User registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Ahmed Ali",
      "email": "ahmed@example.com",
      "created_at": "2026-06-25 10:00:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "type": "bearer"
  }
}
```

#### Error Responses

| Status | Condition | Response |
|---|---|---|
| `422` | Validation failure | See [422 Validation Error](#422-validation-error) |

**Example — duplicate email:**

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

### Login

| | |
|---|---|
| **Method** | `POST` |
| **URL** | `/api/auth/login` |
| **Authentication Required** | No |

#### Request Body

| Field | Type | Rules |
|---|---|---|
| `email` | string | required, email |
| `password` | string | required |

#### Request Example

```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

#### Success Response — `200 OK`

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Ahmed Ali",
      "email": "ahmed@example.com",
      "created_at": "2026-06-25 10:00:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "type": "bearer"
  }
}
```

#### Error Responses

| Status | Condition | Response |
|---|---|---|
| `401` | Invalid credentials | See [401 Unauthorized](#401-unauthorized) |
| `422` | Validation failure | See [422 Validation Error](#422-validation-error) |

**Example — invalid credentials:**

```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

---

### Logout

| | |
|---|---|
| **Method** | `POST` |
| **URL** | `/api/auth/logout` |
| **Authentication Required** | Yes |

#### Request Body

None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Missing or invalid token |

---

### Profile

| | |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/auth/profile` |
| **Authentication Required** | Yes |

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Ahmed Ali",
    "email": "ahmed@example.com",
    "created_at": "2026-06-25 10:00:00"
  }
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Missing or invalid token |

---

## Orders

All order endpoints require authentication. Users can only access their own orders.

---

### Create Order

| | |
|---|---|
| **Method** | `POST` |
| **URL** | `/api/orders` |
| **Authentication Required** | Yes |

#### Request Body

| Field | Type | Rules |
|---|---|---|
| `items` | array | required, min:1 |
| `items.*.product_name` | string | required, max:255 |
| `items.*.quantity` | integer | required, min:1 |
| `items.*.price` | numeric | required, min:0.01 |

#### Request Example

```json
{
  "items": [
    {
      "product_name": "Laptop",
      "quantity": 1,
      "price": 999.99
    },
    {
      "product_name": "Mouse",
      "quantity": 2,
      "price": 25.50
    }
  ]
}
```

#### Success Response — `201 Created`

```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "status": "pending",
    "total_amount": 1050.99,
    "items": [
      {
        "id": 1,
        "product_name": "Laptop",
        "quantity": 1,
        "price": 999.99,
        "subtotal": 999.99
      },
      {
        "id": 2,
        "product_name": "Mouse",
        "quantity": 2,
        "price": 25.5,
        "subtotal": 51
      }
    ],
    "created_at": "2026-06-25 10:05:00",
    "updated_at": "2026-06-25 10:05:00"
  }
}
```

> **Note:** `total_amount` is calculated server-side as the sum of `quantity × price` for all items. New orders are created with status `pending`. The `payments` key is omitted when payments are not loaded.

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `422` | Validation failure |

---

### List Orders

| | |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/orders` |
| **Authentication Required** | Yes |

#### Query Parameters

| Parameter | Type | Description |
|---|---|---|
| `status` | string | Optional. Filter by status: `pending`, `confirmed`, or `cancelled` |
| `page` | integer | Optional. Page number (default: `1`) |

#### Request Example

```
GET /api/orders?status=confirmed&page=1
```

#### Success Response — `200 OK`

Paginated response (10 records per page). Does **not** include a top-level `success` wrapper.

```json
{
  "data": [
    {
      "id": 1,
      "status": "confirmed",
      "total_amount": 1050.99,
      "items": [
        {
          "id": 1,
          "product_name": "Laptop",
          "quantity": 1,
          "price": 999.99,
          "subtotal": 999.99
        }
      ],
      "created_at": "2026-06-25 10:05:00",
      "updated_at": "2026-06-25 10:10:00"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/orders?page=1",
    "last": "http://127.0.0.1:8000/api/orders?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/orders?page=1",
        "label": "1",
        "page": 1,
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "page": null,
        "active": false
      }
    ],
    "path": "http://127.0.0.1:8000/api/orders",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |

---

### Get Order

| | |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/orders/{order}` |
| **Authentication Required** | Yes |

#### URL Parameters

| Parameter | Type | Description |
|---|---|---|
| `order` | integer | Order ID |

#### Request Example

```
GET /api/orders/1
```

#### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "confirmed",
    "total_amount": 1050.99,
    "items": [
      {
        "id": 1,
        "product_name": "Laptop",
        "quantity": 1,
        "price": 999.99,
        "subtotal": 999.99
      }
    ],
    "payments": [
      {
        "id": 1,
        "payment_uuid": "550e8400-e29b-41d4-a716-446655440000",
        "order_id": 1,
        "gateway": "paypal",
        "amount": 1050.99,
        "status": "successful",
        "created_at": "2026-06-25 10:15:00"
      }
    ],
    "created_at": "2026-06-25 10:05:00",
    "updated_at": "2026-06-25 10:10:00"
  }
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `404` | Order not found or not owned by authenticated user |

---

### Update Order

| | |
|---|---|
| **Method** | `PUT` or `PATCH` |
| **URL** | `/api/orders/{order}` |
| **Authentication Required** | Yes |

#### Request Body

| Field | Type | Rules |
|---|---|---|
| `status` | string | required, one of: `pending`, `confirmed`, `cancelled` |

#### Request Example

```json
{
  "status": "confirmed"
}
```

#### Success Response — `200 OK`

```json
{
  "success": true,
  "message": "Order updated successfully.",
  "data": {
    "id": 1,
    "status": "confirmed",
    "total_amount": 1050.99,
    "created_at": "2026-06-25 10:05:00",
    "updated_at": "2026-06-25 10:10:00"
  }
}
```

> **Note:** The update response returns a fresh order model without eager-loaded relations. The `items` and `payments` keys are omitted unless those relations are loaded.

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `404` | Order not found or not owned by authenticated user |
| `422` | Validation failure (invalid status value) |

---

### Delete Order

| | |
|---|---|
| **Method** | `DELETE` |
| **URL** | `/api/orders/{order}` |
| **Authentication Required** | Yes |

#### URL Parameters

| Parameter | Type | Description |
|---|---|---|
| `order` | integer | Order ID |

#### Request Body

None.

#### Success Response — `200 OK`

```json
{
  "success": true,
  "message": "Order deleted successfully."
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `404` | Order not found or not owned by authenticated user |
| `409` | Order has associated payments |

**Example — order has payments:**

```json
{
  "success": false,
  "message": "Cannot delete an order that has associated payments."
}
```

---

## Payments

All payment endpoints require authentication. Users can only process payments and view payments for their own orders.

**Supported gateways:** `paypal`, `credit_card`

---

### Process Payment

| | |
|---|---|
| **Method** | `POST` |
| **URL** | `/api/payments` |
| **Authentication Required** | Yes |

#### Request Body

| Field | Type | Rules |
|---|---|---|
| `order_id` | integer | required, must exist in `orders` table |
| `gateway` | string | required, one of: `paypal`, `credit_card` |

#### Request Example

```json
{
  "order_id": 1,
  "gateway": "paypal"
}
```

#### Success Response — `201 Created`

```json
{
  "success": true,
  "message": "Payment processed.",
  "data": {
    "id": 1,
    "payment_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "order_id": 1,
    "gateway": "paypal",
    "amount": 1050.99,
    "status": "successful",
    "created_at": "2026-06-25 10:15:00"
  }
}
```

> **Note:** Payment `status` is determined by the gateway simulation and will be either `successful` or `failed`. Payment `amount` is taken from the order's `total_amount`. `payment_uuid` is auto-generated.

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `404` | Order not found or not owned by authenticated user |
| `422` | Validation failure, order not confirmed, or unsupported gateway |

**Example — order not confirmed:**

```json
{
  "success": false,
  "message": "Payment can only be processed for confirmed orders."
}
```

**Example — unsupported gateway:**

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "gateway": [
      "The selected gateway is not supported. Supported: paypal, credit_card."
    ]
  }
}
```

---

### List Payments

| | |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/payments` |
| **Authentication Required** | Yes |

#### Query Parameters

| Parameter | Type | Description |
|---|---|---|
| `page` | integer | Optional. Page number (default: `1`) |

#### Success Response — `200 OK`

Paginated response (10 records per page). Does **not** include a top-level `success` wrapper.

```json
{
  "data": [
    {
      "id": 1,
      "payment_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "order_id": 1,
      "gateway": "paypal",
      "amount": 1050.99,
      "status": "successful",
      "created_at": "2026-06-25 10:15:00"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/payments?page=1",
    "last": "http://127.0.0.1:8000/api/payments?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://127.0.0.1:8000/api/payments",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |

---

### Order Payments

| | |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/orders/{id}/payments` |
| **Authentication Required** | Yes |

#### URL Parameters

| Parameter | Type | Description |
|---|---|---|
| `id` | integer | Order ID |

#### Request Example

```
GET /api/orders/1/payments
```

#### Success Response — `200 OK`

Not paginated. Returns all payments for the order.

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "payment_uuid": "550e8400-e29b-41d4-a716-446655440000",
      "order_id": 1,
      "gateway": "paypal",
      "amount": 1050.99,
      "status": "successful",
      "created_at": "2026-06-25 10:15:00"
    }
  ]
}
```

#### Error Responses

| Status | Condition |
|---|---|
| `401` | Unauthenticated |
| `404` | Order not found or not owned by authenticated user |

---

# ERROR DOCUMENTATION

All API errors return JSON with a `success: false` field (except some Laravel resource paginated responses which omit `success` on `200` responses).

Errors are handled in `bootstrap/app.php`.

---

## 401 Unauthorized

Returned when:

- A protected route is accessed without a valid JWT token (`AuthenticationException`)
- Login credentials are invalid (`AuthController::login`)

### Missing / invalid token

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Invalid login credentials

```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

---

## 404 Not Found

Returned when a resource is not found (`NotFoundHttpException`), including:

- Order ID does not exist
- Order exists but belongs to another user
- Order referenced in `/api/orders/{id}/payments` is not found or not owned by the user

```json
{
  "success": false,
  "message": "Resource not found."
}
```

---

## 409 Conflict

Returned when attempting to delete an order that has associated payments (`OrderHasPaymentsException`).

```json
{
  "success": false,
  "message": "Cannot delete an order that has associated payments."
}
```

---

## 422 Validation Error

Returned for:

- Form request validation failures (`ValidationException`)
- Payment attempted on a non-confirmed order (`OrderNotConfirmedException`)
- Unsupported payment gateway passed to factory (`PaymentGatewayNotFoundException`)

### Form validation failure

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

### Order not confirmed (business rule)

```json
{
  "success": false,
  "message": "Payment can only be processed for confirmed orders."
}
```

### Unsupported gateway (factory exception)

```json
{
  "success": false,
  "message": "Payment gateway 'bitcoin' is not supported."
}
```

> **Note:** Invalid gateway values submitted via the API are typically caught by form validation before reaching the factory. The factory exception applies when a gateway is registered in the factory but missing from the request validation list, or when called internally.

---

# POSTMAN COLLECTION GUIDE

## Collection Structure

Import the collection from:

```
postman/Order-Payment-API.postman_collection.json
```

The collection is organized into three folders matching API functionality:

| Folder | Endpoints |
|---|---|
| **Authentication** | Register, Login, Logout, Profile |
| **Orders** | Create, List, Get, Update, Delete |
| **Payments** | Process Payment, List Payments, Order Payments |

## Folder Organization

```
Order & Payment Management API
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Logout
│   └── Profile
├── Orders
│   ├── Create Order
│   ├── List Orders
│   ├── Get Order
│   ├── Update Order
│   └── Delete Order
└── Payments
    ├── Process Payment
    ├── List Payments
    └── Order Payments
```

## Environment Variables

Create a Postman environment with the following variables:

| Variable | Initial Value | Description |
|---|---|---|
| `base_url` | `http://127.0.0.1:8000` | Application base URL (without `/api`) |
| `token` | *(empty)* | JWT bearer token (set automatically after login/register) |
| `order_id` | *(empty)* | Last created order ID (set manually or via test script) |

Example environment:

```json
{
  "base_url": "http://127.0.0.1:8000",
  "token": "",
  "order_id": "1"
}
```

## Authorization Setup

### Option A — Collection-level Bearer Token

1. Open the collection **Authorization** tab.
2. Type: **Bearer Token**
3. Token: `{{token}}`

### Option B — Per-request header

Each protected request includes:

```
Authorization: Bearer {{token}}
```

### Obtaining a token

1. Run **Register** or **Login** from the Authentication folder.
2. Copy the `data.token` value from the response.
3. Set the `token` environment variable.

The Register and Login requests in the collection include a test script that automatically saves the token:

```javascript
if (pm.response.code === 200 || pm.response.code === 201) {
    const json = pm.response.json();
    if (json.data && json.data.token) {
        pm.environment.set('token', json.data.token);
    }
}
```

## Testing Workflow

Recommended sequence to exercise the full API:

1. **Register** or **Login** — obtain JWT token
2. **Create Order** — create an order with line items (status starts as `pending`)
3. **Update Order** — set status to `confirmed`
4. **Process Payment** — pay with `paypal` or `credit_card`
5. **Get Order** — verify order includes payment records
6. **List Payments** — view paginated payment history
7. **Order Payments** — view payments for a specific order
8. **Delete Order** (without payments) — should succeed
9. **Delete Order** (with payments) — should return `409 Conflict`

### Error case testing

| Scenario | Expected Status |
|---|---|
| Access `/api/orders` without token | `401` |
| Login with wrong password | `401` |
| Create order with empty items | `422` |
| Pay a `pending` order | `422` |
| Use gateway `bitcoin` | `422` |
| Get non-existent order ID | `404` |
| Delete order with payments | `409` |

---

# FINAL AUDIT

## Missing Documentation Items

- **OpenAPI / Swagger spec** — Not generated; documentation is Markdown + Postman only.
- **Postman environment file** — Environment variables are documented but no separate `.postman_environment.json` export is included.
- **Rate limiting documentation** — No rate limiting middleware is configured in the application.
- **Gateway `transaction_id` field** — Returned by gateway `process()` but not persisted; not exposed in API responses.

## Potential Reviewer Concerns

- **Inconsistent response envelopes** — Paginated list endpoints (`GET /orders`, `GET /payments`) omit the `success` wrapper, while other endpoints include it.
- **Update order response** — Does not include `items` or `payments` because the service returns a fresh model without eager-loaded relations.
- **Order create scope** — Does not accept separate customer/user detail fields; user is inferred from JWT only.
- **Order update scope** — Only `status` can be updated; line items cannot be modified.
- **Payment field naming** — Request uses `gateway` rather than `payment_method`.
- **Delete status code** — Returns `200 OK` with a JSON body instead of `204 No Content`.
- **PSR-4 directory casing** — Form request files live under `app/Http/requests/` (lowercase) while the namespace is `App\Http\Requests\`; may fail on case-sensitive filesystems.

## Documentation Completeness Score

**88/100**

| Area | Score | Notes |
|---|---|---|
| Endpoint coverage | 10/10 | All 12 routes documented |
| Request/response accuracy | 9/10 | Based on actual controllers, resources, and validation |
| Error documentation | 9/10 | All custom exception handlers documented |
| Setup & extensibility | 9/10 | README covers install, env, gateway extension |
| Postman collection | 8/10 | Importable JSON provided; no environment export |
| Examples & edge cases | 8/10 | Success and error examples for all major flows |
| Assumptions & limitations | 9/10 | Documented transparently |
| OpenAPI / alternate formats | 0/10 | Not provided |

---

*Generated from the actual Laravel codebase: routes, controllers, form requests, resources, services, models, migrations, exception handlers, and tests.*
