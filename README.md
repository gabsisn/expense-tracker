# Expense Tracker

A deliberately small Laravel 13 + Vue 3 app for DevOps practice.

## Run it locally

```bash
composer setup          # install, .env, key, migrate, npm build
php artisan db:seed     # 40 sample expenses
composer dev            # web server + queue worker + logs + vite
```

Open http://localhost:8000.

## What's in it

| Piece | Where | Notes |
|---|---|---|
| `expenses` table (date + amount) | `database/migrations/*_create_expenses_table.php` | `spent_on` (date), `amount` (decimal 10,2) |
| Page listing rows | `GET /` | Server-rendered rows, then Vue takes over. `curl /` shows data. |
| JSON API | `GET/POST /api/expenses`, `DELETE /api/expenses/{id}` | |
| Health check | `GET /up` | Built-in Laravel health route |
| Queued job | `app/Jobs/SendExpenseConfirmation.php` | Dispatched on every new expense; mails via `MAIL_MAILER=log` |
| Scheduled task | `expenses:weekly-summary` in `routes/console.php` | Mondays 06:00; writes per-week totals to the log |
| File upload | `receipt` field on `POST /api/expenses` | Stored on the default disk (`FILESYSTEM_DISK`) under `receipts/` |
| Column to rename | `expenses.description` | For the expand-and-contract exercise |

## Handy commands

```bash
php artisan queue:work                      # process the confirmation emails
php artisan schedule:work                   # run the scheduler in the foreground
php artisan schedule:run                    # what cron calls every minute
php artisan expenses:weekly-summary --weeks=4
php artisan test

# add an expense from the shell
curl -F spent_on=2026-10-19 -F amount=4.20 -F description=Coffee \
     -F receipt=@receipt.jpg -H 'Accept: application/json' \
     http://localhost:8000/api/expenses

# zero-downtime check loop
while true; do curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8000/; sleep 0.5; done
```

## Swapping things out

- **Database:** set `DB_CONNECTION` (sqlite by default; mysql/pgsql work unchanged).
- **Queue:** `QUEUE_CONNECTION=database` by default; switch to `redis` etc.
- **Receipts storage:** change `FILESYSTEM_DISK` to a cloud disk (e.g. an Azure Blob adapter) — the code only uses the default disk.
