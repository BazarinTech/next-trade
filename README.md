# Next Trade

A Laravel-based educational binary options trading simulation platform. All trading outcomes, bot earnings, and wallet activity are for demonstration purposes only — no real money is involved.

---

## Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13.9 / PHP 8.3 |
| Database | MySQL 8 |
| Frontend | Blade + Alpine.js (CDN) + Tailwind CSS (CDN) |
| Queue | Sync (dev) / Database or Redis (production) |
| Payments | PalPluss M-Pesa STK Push |

---

## Requirements

- PHP 8.3+
- MySQL 8+
- Composer
- Node.js (optional — assets served via CDN)

---

## Local Setup

```bash
# 1. Clone and install dependencies
git clone <repo-url> next-trade
cd next-trade
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Set up database credentials in .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Run migrations and seeders
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=DemoSeeder          # optional demo data

# 5. Create the first super admin
php artisan admin:make admin@yoursite.com

# 6. Create storage symlink
php artisan storage:link

# 7. Start dev server
php artisan serve
```

---

## Credentials (after seeding)

### Super Admin
| Field | Value |
|-------|-------|
| Email | Created via `php artisan admin:make` |
| Password | Set during the command |

### Demo Users (DemoSeeder)
All passwords: `password123`

| Name | Email |
|------|-------|
| Alice Wambui | alice@demo.nexttrade.dev |
| Bob Otieno | bob@demo.nexttrade.dev |
| Carol Mutua | carol@demo.nexttrade.dev |
| David Kamau | david@demo.nexttrade.dev |
| Eve Njeri | eve@demo.nexttrade.dev |

---

## Key Environment Variables

```dotenv
# App
APP_URL=https://yourdomain.com

# Database
DB_HOST=127.0.0.1
DB_DATABASE=next_trade
DB_USERNAME=root
DB_PASSWORD=secret

# PalPluss M-Pesa Integration
PALPLUSS_BASE_URL=https://api.palpluss.com
PALPLUSS_BASIC_AUTH=           # Base64 encoded credentials
PALPLUSS_CHANNEL_ID=           # Your M-Pesa channel ID
PALPLUSS_SHORTCODE=            # Your paybill/till number
PALPLUSS_SECRET=               # PalPluss API secret
PALPLUSS_STK_CALLBACK_URL=     # Full URL for STK push callback

# Currency
USD_KES_RATE=130

# Queue (production)
QUEUE_CONNECTION=database
```

---

## Artisan Commands

```bash
# Health check — verifies config, DB, storage, env vars
php artisan nexttrade:health

# Cleanup stale pending deposits and old price ticks
php artisan nexttrade:cleanup-pending
php artisan nexttrade:cleanup-pending --dry-run   # preview only

# Reconcile wallet balances against transaction sums
php artisan nexttrade:reconcile
php artisan nexttrade:reconcile --fix             # auto-correct mismatches

# Create a super admin account
php artisan admin:make email@example.com
```

---

## Scheduler

Add to server crontab for scheduled cleanup:

```cron
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs:
- `nexttrade:cleanup-pending` — runs daily

---

## Queue Workers (production)

```bash
php artisan queue:work --queue=default --tries=3 --timeout=60
```

Jobs dispatched:
- `SendNotificationJob` — async user notification delivery
- `SettleTradeJob` — trade settlement after expiry
- `ProcessBotEarningJob` — daily bot ROI crediting

---

## Admin Panel

Access at `/admin` (requires `is_admin = true` on the user record).

| Section | URL |
|---------|-----|
| Dashboard | `/admin` |
| Users | `/admin/users` |
| Deposits | `/admin/deposits` |
| Withdrawals | `/admin/withdrawals` |
| Trades | `/admin/trades` |
| Bot Plans | `/admin/bot-plans` |
| Roles | `/admin/roles` |
| Permissions | `/admin/permissions` |
| Admins | `/admin/admins` |
| Audit Logs | `/admin/audit-logs` |
| System Settings | `/admin/system-settings` |
| System Health | `/admin/system-health` |

### CSV Exports

All major data tables support CSV export from their admin pages:

```
GET /admin/export/users
GET /admin/export/deposits
GET /admin/export/withdrawals
GET /admin/export/trades
GET /admin/export/bots
GET /admin/export/transactions
GET /admin/export/audit-logs
```

---

## Security Notes

- Super Admin bypasses all permission checks
- System roles and permissions cannot be deleted
- Admins cannot ban themselves or demote themselves if sole Super Admin
- Financial records are never hard-deleted
- Wallet credits use row-level locking to prevent duplicate crediting
- `credited_at` is the idempotency key for deposit processing
- Withdrawals lock the user's balance immediately on request
- Demo wallet cannot be used for withdrawals
- PalPluss credentials are server-side only — never exposed to the frontend
- All sensitive admin actions are logged to `admin_logs`
- Rate limiting applied to login, register, deposits, withdrawals, trades, and bot investments

---

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| Login | 5/min per email+IP |
| Register | 3/min per IP |
| Deposit | 5/min per user |
| Deposit refresh | 6/min per user |
| Trade | 20/min per user |
| Bot invest | 10/min per user |
| Withdrawal | 3/min per user |
| Admin actions | 30/min per admin |

---

## License

Private — all rights reserved.
