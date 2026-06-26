# Order & Payment Management API

A Laravel-based REST API for managing orders and processing payments with an extensible payment gateway strategy pattern. All protected endpoints require JWT authentication.

---

## Project Overview

This API allows authenticated users to:

- Register and log in to receive a JWT bearer token
- Create and manage orders with line items (product name, quantity, price)
- Update order status (`pending`, `confirmed`, `cancelled`)
- Process simulated payments through pluggable gateways (`paypal`, `credit_card`)
- View paginated lists of orders and payments

Business rules enforced by the application:

- Payments can only be processed for orders in `confirmed` status
- Orders with associated payments cannot be deleted

---

## Features

- **Authentication** — Register, login, logout, and profile endpoints secured with JWT
- **Orders** — Full CRUD with status filtering and automatic total calculation
- **Payments** — Process payments via strategy-based gateways; list all payments or payments for a specific order
- **Payment Gateway Strategy Pattern** — Add new gateways by implementing `PaymentGatewayInterface` and registering in `PaymentGatewayFactory`
- **JWT Authentication** — Powered by `php-open-source-saver/jwt-auth`
- **Pagination** — Order and payment list endpoints return 10 records per page (Laravel default pagination)

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.3 |
| Composer | Latest stable |
| Database | SQLite (default) or MySQL/PostgreSQL |
| Laravel | ^13.8 |

---

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`. All API routes are prefixed with `/api`.

For SQLite (default in `.env.example`), ensure the database file exists:

```bash
touch database/database.sqlite
```

---

## Environment Variables

### Application (`.env.example`)

| Variable | Description |
|---|---|
| `APP_NAME` | Application name |
| `APP_ENV` | Environment (`local`, `production`, etc.) |
| `APP_KEY` | Laravel encryption key (generated via `php artisan key:generate`) |
| `APP_DEBUG` | Debug mode (`true` / `false`) |
| `APP_URL` | Base application URL |
| `APP_LOCALE` | Default locale |
| `APP_FALLBACK_LOCALE` | Fallback locale |
| `APP_FAKER_LOCALE` | Faker locale for seeding |
| `APP_MAINTENANCE_DRIVER` | Maintenance mode driver |
| `BCRYPT_ROUNDS` | Password hashing rounds |
| `LOG_CHANNEL` | Log channel |
| `LOG_STACK` | Log stack |
| `LOG_DEPRECATIONS_CHANNEL` | Deprecations log channel |
| `LOG_LEVEL` | Log level |
| `DB_CONNECTION` | Database driver (default: `sqlite`) |
| `DB_HOST` | Database host (when not using SQLite) |
| `DB_PORT` | Database port |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `SESSION_DRIVER` | Session driver |
| `SESSION_LIFETIME` | Session lifetime in minutes |
| `SESSION_ENCRYPT` | Encrypt sessions |
| `SESSION_PATH` | Session cookie path |
| `SESSION_DOMAIN` | Session cookie domain |
| `BROADCAST_CONNECTION` | Broadcast driver |
| `FILESYSTEM_DISK` | Default filesystem disk |
| `QUEUE_CONNECTION` | Queue driver |
| `CACHE_STORE` | Cache store |
| `MEMCACHED_HOST` | Memcached host |
| `REDIS_CLIENT` | Redis client |
| `REDIS_HOST` | Redis host |
| `REDIS_PASSWORD` | Redis password |
| `REDIS_PORT` | Redis port |
| `MAIL_MAILER` | Mail driver |
| `MAIL_SCHEME` | Mail scheme |
| `MAIL_HOST` | Mail host |
| `MAIL_PORT` | Mail port |
| `MAIL_USERNAME` | Mail username |
| `MAIL_PASSWORD` | Mail password |
| `MAIL_FROM_ADDRESS` | From email address |
| `MAIL_FROM_NAME` | From name |
| `AWS_ACCESS_KEY_ID` | AWS access key |
| `AWS_SECRET_ACCESS_KEY` | AWS secret key |
| `AWS_DEFAULT_REGION` | AWS region |
| `AWS_BUCKET` | S3 bucket |
| `AWS_USE_PATH_STYLE_ENDPOINT` | S3 path-style endpoint |
| `VITE_APP_NAME` | Vite app name |

### JWT (`config/jwt.php` / `.env.example`)

| Variable | Description | Default |
|---|---|---|
| `JWT_SECRET` | Token signing secret (generated via `php artisan jwt:secret`) | — |
| `JWT_TTL` | Token lifetime in minutes | `60` |
| `JWT_REFRESH_TTL` | Refresh token lifetime in minutes | `20160` |
| `JWT_ALGO` | Signing algorithm | `HS256` |
| `JWT_PUBLIC_KEY` | Public key (asymmetric algorithms) | — |
| `JWT_PRIVATE_KEY` | Private key (asymmetric algorithms) | — |
| `JWT_PASSPHRASE` | Private key passphrase | — |
| `JWT_REFRESH_IAT` | Include refresh iat | `false` |
| `JWT_LEEWAY` | Clock leeway in seconds | `0` |
| `JWT_BLACKLIST_ENABLED` | Enable token blacklist | `true` |
| `JWT_BLACKLIST_GRACE_PERIOD` | Blacklist grace period | `0` |
| `JWT_SHOW_BLACKLIST_EXCEPTION` | Show blacklist exception | `true` |

### Payment Gateways (`config/payment.php` / `.env.example`)

| Variable | Description |
|---|---|
| `PAYPAL_KEY` | PayPal client ID / API key |
| `PAYPAL_SECRET` | PayPal client secret |
| `CREDIT_CARD_KEY` | Credit card gateway API key |
| `CREDIT_CARD_SECRET` | Credit card gateway API secret |

### Authentication (`config/auth.php`)

| Variable | Description | Default |
|---|---|---|
| `AUTH_MODEL` | Authenticatable model class | `App\Models\User` |
| `AUTH_PASSWORD_RESET_TOKEN_TABLE` | Password reset tokens table | `password_reset_tokens` |
| `AUTH_PASSWORD_TIMEOUT` | Password confirmation timeout (seconds) | `10800` |

Example `.env` snippet:

```env
JWT_SECRET=
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

PAYPAL_KEY=your-paypal-client-id
PAYPAL_SECRET=your-paypal-client-secret

CREDIT_CARD_KEY=your-cc-key
CREDIT_CARD_SECRET=your-cc-secret
```

---

## Running Tests

```bash
php artisan test
```

The test suite uses an in-memory SQLite database via `RefreshDatabase`.

---

## Authentication Flow

### 1. Register

Send `POST /api/auth/register` with `name`, `email`, `password`, and `password_confirmation`.

On success (`201`), the response includes a JWT `token` and `type: "bearer"`.

### 2. Login

Send `POST /api/auth/login` with `email` and `password`.

On success (`200`), the response includes a JWT `token`.

### 3. JWT Token

Include the token on all protected requests:

```
Authorization: Bearer {token}
```

### 4. Protected Routes

The following routes require the `auth:api` middleware:

- `POST /api/auth/logout`
- `GET /api/auth/profile`
- All `/api/orders` routes
- All `/api/payments` routes
- `GET /api/orders/{id}/payments`

Unauthenticated requests to protected routes return `401` with:

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

---

## Payment Gateway Extensibility

The payment system uses the **Strategy Pattern**. Each gateway implements `App\Payment\Contracts\PaymentGatewayInterface`.

### 1. Create a gateway class implementing `PaymentGatewayInterface`

```php
namespace App\Payment\Gateways;

use App\Payment\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    public function process(array $data): array
    {
        // Integrate with Stripe SDK here
        return [
            'status'         => 'successful',
            'transaction_id' => 'STRIPE-EXAMPLE',
            'message'        => 'Payment processed.',
        ];
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
```

The `process()` method must return an array with keys: `status`, `transaction_id`, `message`.  
The `status` value is persisted on the payment record (`successful` or `failed`).

### 2. Register the gateway in `PaymentGatewayFactory`

In `app/Payment/PaymentGatewayFactory.php`, add an entry to the `$gateways` array:

```php
private array $gateways = [
    'paypal'      => PaypalGateway::class,
    'credit_card' => CreditCardGateway::class,
    'stripe'      => StripeGateway::class,
];
```

### 3. Add configuration values

In `config/payment.php`:

```php
'stripe' => [
    'key'    => env('STRIPE_KEY', ''),
    'secret' => env('STRIPE_SECRET', ''),
],
```

Add corresponding variables to `.env`:

```env
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
```

### 4. Use the gateway in requests

Send `POST /api/payments` with the registered gateway name:

```json
{
  "order_id": 1,
  "gateway": "stripe"
}
```

The gateway name is validated dynamically via `PaymentGatewayFactory::supportedGateways()`.

---

## Assumptions

- **User identity for orders** — Orders are linked to the authenticated user via JWT; no separate customer details are stored on the order record.
- **Order creation** — New orders always start with status `pending`. Total amount is calculated server-side from item quantities and prices.
- **Order updates** — Only the `status` field can be updated; line items cannot be modified after creation.
- **Payment simulation** — Gateways simulate processing with a random success/failure outcome (~90% success). No external payment provider is called.
- **Payment amount** — Payment amount is taken from the order's `total_amount`; it is not supplied in the request.
- **Payment ID** — A UUID (`payment_uuid`) is auto-generated when a payment is created; it is not provided by the client.
- **Gateway transaction ID** — The `transaction_id` returned by gateways is not persisted in the database.
- **Pagination** — Page size is fixed at 10 records per page in repository classes.
- **Order ownership** — Users can only access their own orders and payments belonging to their orders.
- **Order deletion** — Deleting an order with existing payments returns HTTP `409 Conflict`.

---

## API Documentation

Full endpoint reference, error documentation, and Postman collection guide:

- **[docs/API.md](docs/API.md)** — Complete API reference
- **[postman/Order-Payment-API.postman_collection.json](postman/Order-Payment-API.postman_collection.json)** — Importable Postman collection

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # AuthController, OrderController, PaymentController
│   ├── Requests/        # Form request validation
│   └── Resources/       # JSON API resources
├── Models/              # User, Order, OrderItem, Payment
├── Payment/
│   ├── Contracts/       # PaymentGatewayInterface
│   ├── Gateways/        # CreditCardGateway, PaypalGateway
│   └── PaymentGatewayFactory.php
├── Repositories/        # Data access layer
├── Services/            # Business logic
└── Exceptions/          # Domain exceptions
routes/api.php           # API route definitions
tests/                   # Feature and unit tests
```
