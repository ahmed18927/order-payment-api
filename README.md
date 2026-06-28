# Order & Payment Management API

A Laravel REST API for managing orders and payments with JWT authentication and an extensible payment gateway strategy pattern.

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.3 |
| Laravel | ^13 |
| Docker | Latest |

---

## Installation

### With Docker (Recommended)

```bash
git clone https://github.com/ahmed18927/order-payment-api.git
cd order-payment-api
docker compose build --no-cache
docker compose up -d
```

The API will be available at `http://localhost:8000`.

## Environment Variables

```env
JWT_SECRET=           # generated via php artisan jwt:secret
JWT_TTL=60

PAYPAL_KEY=
PAYPAL_SECRET=
CREDIT_CARD_KEY=
CREDIT_CARD_SECRET=
```

---

## Running Tests

```bash
php artisan test
```


## API Reference

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/auth/register` | Register | No |
| POST | `/api/auth/login` | Login | No |
| POST | `/api/auth/logout` | Logout | Yes |
| GET | `/api/auth/profile` | Profile | Yes |
| GET | `/api/orders` | List orders | Yes |
| POST | `/api/orders` | Create order | Yes |
| GET | `/api/orders/{id}` | Get order | Yes |
| PUT | `/api/orders/{id}` | Update order | Yes |
| DELETE | `/api/orders/{id}` | Delete order | Yes |
| GET | `/api/payments` | List payments | Yes |
| POST | `/api/payments` | Process payment | Yes |
| GET | `/api/orders/{id}/payments` | Order payments | Yes |
