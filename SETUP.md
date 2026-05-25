# NextTrade — Setup & Installation Guide

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| MySQL | 8.0+ |
| PHP Extensions | bcmath, pdo, pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json |

---

## Step 1 — Unzip the project

```bash
unzip next-trade.zip -d next-trade
cd next-trade
```

---

## Step 2 — Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

> For local development (with dev tools):
> ```bash
> composer install
> ```

---

## Step 3 — Install Node dependencies and build assets

```bash
npm install
npm run build
```

---

## Step 4 — Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Then open `.env` and fill in the required values:

```env
APP_NAME=NextTrade
APP_ENV=production
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=next_trade
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# M-Pesa (PalPluss) — leave blank to disable
PALPLUSS_BASE_URL=https://api.palpluss.com
PALPLUSS_BASIC_AUTH=
PALPLUSS_CHANNEL_ID=
PALPLUSS_STK_CALLBACK_URL=https://yourdomain.com/webhooks/palpluss

# USDT TRC20 deposits — leave blank to disable
USDT_TRC20_WALLET_ADDRESS=
USDT_TRC20_NETWORK=TRC20
USDT_USD_RATE=1

# Currency
USD_KES_RATE=130
```

---

## Step 5 — Create the database

Create a MySQL database:

```sql
CREATE DATABASE next_trade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Step 6 — Run migrations and seed data

```bash
php artisan migrate --force
php artisan db:seed --force
```

This seeds:
- All 6 trading assets (BTC/USD, ETH/USD, EUR/USD, GBP/USD, XAU/USD, VOLTEX)
- Roles and permissions (Super Admin, Finance Admin, Trading Admin, Support Admin, Moderator)
- All system settings with sensible defaults
- Bot plans (Conservative, Balanced, Aggressive, Extreme Scalper)
- Simulation settings

---

## Step 7 — Set up file storage symlink

```bash
php artisan storage:link
```

This creates `public/storage → storage/app/public` so uploaded files (logos, etc.) are served correctly.

---

## Step 8 — Create your admin account

Register normally at `/register`, then run this SQL to make yourself Super Admin:

```sql
-- Replace 1 with your user ID if different
UPDATE users SET is_admin = 1 WHERE id = 1;

INSERT IGNORE INTO roles (name, slug, description, is_system, created_at, updated_at)
VALUES ('Super Admin', 'super-admin', 'Full platform access', 1, NOW(), NOW());

INSERT INTO admin_role_user (user_id, role_id, assigned_by, created_at, updated_at)
SELECT 1, id, 1, NOW(), NOW() FROM roles WHERE slug = 'super-admin';
```

Then log out and log back in.

---

## Step 9 — Optimize for production

```bash
php artisan optimize
```

---

## Step 10 — Run the application

### Local development

```bash
composer run dev
```

This starts all services concurrently:
- PHP dev server on `http://localhost:8000`
- Queue worker
- Log viewer (Pail)
- Vite HMR dev server

### Production (with a web server)

Point your web server (Nginx/Apache) document root to the `public/` folder.

**Nginx example:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/next-trade/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Then run the queue worker as a background process:

```bash
php artisan queue:work --daemon --tries=3 --timeout=60
```

---

## Deploying to Railway

1. Push the repo to GitHub
2. Connect the GitHub repo in Railway
3. Add a **MySQL** database service
4. Set environment variables in your service's **Variables** tab
5. Add a **Volume** mounted at `/app/storage/app/public` for persistent file uploads
6. Railway auto-deploys on every push — the `railway.toml` handles migrations, seeding, and optimization

---

## Directory structure

```
next-trade/
├── app/                  # Laravel application code
│   ├── Http/Controllers/ # Route controllers (Admin/, Auth/, etc.)
│   ├── Models/           # Eloquent models
│   └── Services/         # Business logic services
├── config/               # Laravel configuration files
├── database/
│   ├── migrations/       # Database schema migrations
│   └── seeders/          # Default data seeders
├── public/               # Web server document root
│   └── build/            # Compiled JS/CSS assets (Vite output)
├── resources/
│   ├── js/               # React/TypeScript frontend
│   │   ├── components/trading/  # Trading chart components
│   │   └── lib/trading/         # Price engine adapter
│   └── views/            # Blade templates
├── routes/
│   └── web.php           # All application routes
├── storage/
│   └── app/public/       # User-uploaded files (logos, etc.)
├── .env.example          # Environment variable template
├── composer.json         # PHP dependencies
├── package.json          # Node dependencies
├── railway.toml          # Railway deployment config
└── vite.config.js        # Vite/React build config
```

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `Call to undefined function bcsub()` | Enable the `bcmath` PHP extension |
| Blank trading chart | Run `npm run build` — compiled assets are missing |
| `No trading assets available` | Visit `/trade` once — assets auto-seed on first load |
| Uploaded images not showing | Run `php artisan storage:link` |
| Admin panel shows nothing | Run `php artisan db:seed --force` to seed roles and settings |
| 500 error on register | Check DB connection and run `php artisan migrate` |
