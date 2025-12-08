# Laravel Sanctum Starter

A clean and minimal starter project built with **Laravel 12** and **Sanctum**.
This project includes basic structure organization, and several improvements to help you start new API-based applications faster.


## 🛠 Installation

```bash
git clone https://github.com/babak-rostami/sanctum-is-ready.git
cd sanctum-is-ready
composer install
cp .env.example .env
php artisan key:generate
```

Update your `.env` database settings, then run:

```bash
php artisan migrate
```

---

## 🔑 API Authentication (Sanctum)

Issue a token by logging in:

**POST** `/api/login`

Example response:

```json
{
  "status": true,
  "message": "Login successful",
  "token": "your-generated-token"
}
```

Use the token in headers:

```
Authorization: Bearer your-token
```

---

## 📡 Example Routes

```txt
POST   /api/register
POST   /api/login
POST   /api/logout         (requires token)
GET    /api/user           (requires token)
```

All routes are inside:

```
routes/api.php
```