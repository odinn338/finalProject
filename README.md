<div align="center">

# 💳 DebtMate

### A Professional Debt & Installment Management Platform

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

**A secure, admin-governed financial platform for managing personal debts, structured installment plans, and external transfer reconciliation — built with a Service-Pattern architecture and Laravel Policies for granular access control.**

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Core Features](#core-features)
3. [Architecture](#architecture)
4. [Security Model](#security-model)
5. [Manual Admin Approval for Installments](#manual-admin-approval-for-installments)
6. [Proof of Payment for External Transfers](#proof-of-payment-for-external-transfers)
7. [Tech Stack](#tech-stack)
8. [Prerequisites](#prerequisites)
9. [Installation](#installation)
10. [Configuration](#configuration)
11. [Running the Application](#running-the-application)
12. [Testing](#testing)
13. [Project Structure](#project-structure)
14. [Contributing](#contributing)

---

## Overview

DebtMate is a full-stack fintech web application built on **Laravel 12** that enables users to track debts, create structured installment schedules, and manage external bank transfers — all under a secure, admin-governed workflow. The platform is designed around the principle that financial operations require human oversight: no installment is settled and no external payment is confirmed without an authorized administrator reviewing and approving the submitted evidence.

The application uses a **Service-Pattern architecture**, cleanly separating business logic from controller concerns, and employs **Laravel Policies** to enforce that users can only view and act on their own financial records.

---

## Core Features

**Debt Management**
Track and categorize personal or business debts with full history of outstanding balances and payment timelines.

**Installment Scheduling**
Generate structured installment plans from a debt. Each installment moves through a defined lifecycle (`pending` → `submitted` → `approved` / `rejected`) governed exclusively by admin action.

**External Transfer Reconciliation**
When a user pays a debt externally (e.g., a bank transfer), they upload a receipt image as Proof of Payment. The admin reviews the image and manually confirms or denies the settlement — preventing fraudulent or erroneous self-reporting.

**PDF Report Generation**
Export debt summaries and transaction histories as PDF documents using `barryvdh/laravel-dompdf`.

**Excel Export**
Export financial records to spreadsheets via `maatwebsite/excel` for offline analysis and auditing.

**Role-Based Access**
Admins have full operational control over the approval pipeline. Regular users interact only with their own data, enforced at the policy layer.

---

## Architecture

DebtMate follows a **Service-Pattern architecture**. Controllers remain thin — they handle HTTP concerns (validation, request/response) and delegate all business logic to dedicated Service classes.

```
app/
├── Http/
│   └── Controllers/         # Thin controllers — HTTP only
├── Services/                # Business logic layer
│   ├── InstallmentService.php
│   ├── TransferService.php
│   └── DebtService.php
├── Models/                  # Eloquent models with casts & relationships
├── Policies/                # Laravel Policies — per-model authorization
└── Providers/               # Service and AuthServiceProvider
```

This separation makes business rules testable in isolation, keeps controllers readable, and allows the same service to be reused across web and CLI contexts (e.g., Artisan commands or queue jobs).

---

## Security Model

Data access is enforced at the **Policy layer** using **Laravel Policies** registered in `AuthServiceProvider`. Every sensitive route goes through a `$this->authorize()` call that invokes the corresponding policy method before any data is returned or mutated.

```php
// Example: Users can only view their own debts
public function view(User $user, Debt $debt): bool
{
    return $user->id === $debt->user_id;
}
```

This means even if a user crafts a direct URL to another user's installment or receipt, the policy rejects the request at the authorization layer — not just at the query level. Admin-only operations (approvals, rejections) are further guarded by role checks within the same policy methods.

---

## Manual Admin Approval for Installments

One of the core governance features of DebtMate is that **installment payments are never automatically confirmed**. When a user marks an installment as paid, the system places it in a `submitted` state and notifies the admin queue for review.

### Lifecycle

```
[User Action]         [Admin Action]
  pending   ──submit──▶  submitted  ──approve──▶  approved
                                    ──reject───▶  rejected
```

### How It Works

1. **User submits payment.** The user indicates they have made a payment for a scheduled installment. The `InstallmentService` transitions the status from `pending` to `submitted` and logs the submission timestamp.

2. **Admin review queue.** The admin dashboard surfaces all `submitted` installments with user details, debt context, and the claimed payment amount. No installment can bypass this queue.

3. **Admin approves or rejects.** Upon review, the admin explicitly calls the approve or reject action. The `InstallmentService` handles the state transition, updates the outstanding debt balance if approved, and records the admin's decision with a timestamp.

4. **User is notified.** The status change is immediately reflected in the user's installment dashboard.

This design prevents users from self-reporting payments without administrator verification, making the system auditable and tamper-resistant.

---

## Proof of Payment for External Transfers

For debts settled through external channels (bank transfers, wire payments, etc.), DebtMate implements a **Proof of Payment (PoP) system** that requires users to upload a receipt image before a transfer can be considered for confirmation.

### How It Works

1. **User initiates an external transfer.** The user records the transfer in the system and uploads a receipt image (JPEG, PNG, or PDF scan) via the transfer form. The file is stored in Laravel's `storage/app/private` disk under a scoped directory per user/transfer.

2. **Receipt stored securely.** The `TransferService` validates the file type, generates a unique filename, and persists the path to the database. The file is **not** publicly accessible by default.

3. **Admin reviews the receipt.** The admin can view the uploaded receipt image directly within the admin panel. Because uploaded images live under `storage/`, the public symlink created by `php artisan storage:link` is **required** for images to render correctly in the browser (see [Prerequisites](#prerequisites)).

4. **Admin confirms or denies.** After verifying the receipt against the claimed transfer amount, the admin either confirms the payment (settling the debt) or denies it (returning the transfer to a pending state with an optional reason).

5. **Immutable audit trail.** Every admin decision — approvals, rejections, and denial reasons — is recorded against the transfer record, creating a full audit history.

This system ensures that no external payment is accepted on the user's word alone. Physical evidence must be attached, and a human administrator must sign off before any balance is updated.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2 |
| Framework | Laravel 12 |
| Frontend | Blade + Tailwind CSS 4 |
| Build Tool | Vite 7 |
| Database | MySQL |
| PDF Generation | barryvdh/laravel-dompdf 3.x |
| Excel Export | maatwebsite/excel 1.x |
| Queue Driver | Database |
| Session Driver | Database |
| Testing | PestPHP 3 + PHPUnit 11 |
| Dev Tools | Laravel Sail, Laravel Pint, Laravel Pail |

---

## Prerequisites

Before installing, ensure your environment has the following:

- **PHP 8.2+** with the `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, and `xml` extensions enabled
- **Composer 2.x**
- **Node.js 20+** and **npm**
- **MySQL 8.x** (or a compatible fork such as MariaDB 10.6+)

### ⚠️ Critical: Storage Symlink for Receipt Images

This application stores user-uploaded Proof of Payment receipt images in the `storage/` directory. For these images to be viewable in the admin panel and by users, you **must** create a public symlink after installation:

```bash
php artisan storage:link
```

Without this step, all receipt images will return 404 errors and the admin approval workflow for external transfers will not function correctly.

---

## Installation

**1. Clone the repository**

```bash
git clone https://github.com/odinn338/finalProject.git
cd finalProject
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Install Node dependencies**

```bash
npm install
```

**4. Set up your environment file**

```bash
cp .env.example .env
php artisan key:generate
```

**5. Configure your database**

Open `.env` and update the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_debt_mate
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**6. Run migrations and seed the database**

```bash
php artisan migrate --seed
```

**7. Create the storage symlink** *(required for receipt image display)*

```bash
php artisan storage:link
```

**8. Build frontend assets**

```bash
npm run build
```

---

## Configuration

Key environment variables to review in your `.env` file:

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application display name | `Laravel` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_DEBUG` | Enable debug mode | `true` |
| `DB_DATABASE` | MySQL database name | `project_debt_mate` |
| `QUEUE_CONNECTION` | Queue driver for async jobs | `database` |
| `SESSION_DRIVER` | Session storage driver | `database` |
| `FILESYSTEM_DISK` | Default disk for file storage | `local` |
| `MAIL_MAILER` | Mail driver (set to `smtp` for production) | `log` |

For production deployments, set `APP_ENV=production`, `APP_DEBUG=false`, and configure a real mail driver for admin notifications.

---

## Running the Application

**Development mode** (starts Laravel server, queue worker, and Vite dev server concurrently):

```bash
composer run dev
```

**Or start services individually:**

```bash
php artisan serve          # Web server on http://localhost:8000
php artisan queue:listen   # Process queued jobs (notifications, etc.)
npm run dev                # Vite hot-module replacement
```

**Production build:**

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Testing

This project uses **PestPHP 3** for all tests. Feature tests cover the full HTTP lifecycle including authorization policy enforcement.

**Run the full test suite:**

```bash
php artisan test --compact
```

**Run a specific test or filter:**

```bash
php artisan test --compact --filter=InstallmentApprovalTest
```

**Run only unit tests:**

```bash
php artisan test --compact --testsuite=Unit
```

Tests are located in `tests/Feature/` and `tests/Unit/`. Do not delete existing tests without explicit review — they serve as the specification for the approval and authorization workflows.

---

## Project Structure

```
finalProject/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Thin HTTP controllers
│   │   └── Requests/           # Form request validation
│   ├── Models/                 # Eloquent models (User, Debt, Installment, Transfer)
│   ├── Policies/               # Laravel Policies — per-user data authorization
│   ├── Services/               # Business logic (InstallmentService, TransferService, etc.)
│   └── Providers/              # AuthServiceProvider, AppServiceProvider
├── bootstrap/
│   └── app.php                 # Middleware, routing, and exception configuration (Laravel 12)
├── database/
│   ├── migrations/             # Schema migrations
│   ├── factories/              # Model factories for testing
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/                  # Blade templates
│   └── css/, js/               # Frontend assets (compiled by Vite)
├── routes/
│   ├── web.php                 # Web routes
│   └── console.php             # Artisan console routes
├── storage/
│   └── app/                    # User-uploaded files (receipt images, exports)
├── tests/
│   ├── Feature/                # HTTP and integration tests
│   └── Unit/                   # Unit tests for services and models
├── .env.example                # Environment variable reference
├── composer.json               # PHP dependencies
└── package.json                # Node dependencies
```

---

## Contributing

1. Fork the repository and create a feature branch from `main`.
2. Follow the existing code conventions — check sibling files for structure and naming patterns.
3. Write Pest feature tests for any new business logic. Tests are non-negotiable for the approval and authorization subsystems.
4. Run `vendor/bin/pint` to auto-format PHP files before committing.
5. Open a pull request with a clear description of the change and its rationale.

---

<div align="center">

Built with ❤️ using [Laravel](https://laravel.com) · Deployable on [Laravel Cloud](https://cloud.laravel.com)

</div>
