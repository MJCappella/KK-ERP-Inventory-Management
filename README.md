# KK Wholesalers — Monolithic ERP & Inventory Management System

A robust, multi-branch, multi-store Wholesale & Retail Inventory Management System built for **KK Wholesalers** using **Laravel 13 & Blade**, backed by a concurrency-safe double-entry stock audit ledger and Role-Based Access Control (RBAC).

---

## Table of Contents

1. [Key Features](#key-features)
2. [System Architecture & Design Decisions](#system-architecture--design-decisions)
3. [Branch & Store Organizational Structure](#branch--store-organizational-structure)
4. [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
5. [Docker Setup Instructions](#docker-setup-instructions)
6. [Local Environment Setup (Alternative)](#local-environment-setup-alternative)
7. [Pre-Configured Demo Accounts](#pre-configured-demo-accounts)
8. [Monitoring & Observability (Grafana + Loki)](#monitoring--observability-grafana--loki)
9. [Automated Test Suite](#automated-test-suite)
10. [Assumptions Made](#assumptions-made)
11. [Known Limitations](#known-limitations)

---

## Key Features

- **Point of Sale (POS) Checkout:** Interactive real-time product grid with live stock availability verification, dynamic multi-item cart, instant subtotal/tax/discount calculations, sequential invoice number generation (`INV-YYYYMMDD-XXXX`), and printable invoices.
- **Inter-Store Stock Transfers:** Store-to-store transfer dispatches with origin deduction, destination addition, validation that source and destination stores differ, and printable dispatch transfer notes (`TRF-XXXXXXXX`).
- **Inbound Stock Receiving:** Receipt of supplier deliveries and purchase order items into store inventories.
- **Physical Count Adjustments:** Manual stock count reconciliation (write-offs, breakage, count variances) with mandatory audit justification notes.
- **Double-Entry Stock Movements Ledger:** Chronological, immutable audit trail recording every inventory change with before/after balances, user attribution, movement type badges (`INBOUND`, `SALE`, `TRANSFER_OUT`, `TRANSFER_IN`, `ADJUSTMENT`), and transaction references.
- **Executive Management Dashboard:** High-level KPIs (Total Sales Revenue, Stock Valuation at retail and cost, In-Stock Units, Low-Stock Alerts, Store Performance breakdown, Top-Selling SKUs leaderboard).
- **Physics-Based Sonner Toasts:** Modern, non-blocking stacked notifications for feedback, alerts, validation errors, and workflow confirmations.
- **Corporate Enterprise Aesthetic:** Modern UI inspired directly by the royal/cyan blue active state indicators, uppercase metadata tags, rounded cards, responsive data tables).

---

## System Architecture & Design Decisions

### 1. Concurrency-Safe Double-Entry Stock Ledger
To avoid discrepancies when concurrent sales, receipts, or transfers occur simultaneously:
- **Cached Store Balances (`store_stocks`):** Real-time aggregate quantities are indexed per `(store_id, product_id)` with a unique constraint for high-speed $O(1)$ stock checks.
- **Pessimistic Row Locking (`lockForUpdate()`):** Every balance change acquires an exclusive database row lock inside a `DB::transaction()`.
- **Immutable Ledger (`stock_movements`):** A permanent historical log entry is generated for every inventory event containing:
  - `store_id`, `product_id`, `user_id`
  - `type` (`inbound`, `sale`, `transfer_out`, `transfer_in`, `adjustment`)
  - `quantity` (signed integer: negative for deductions, positive for additions)
  - `balance_after` (resulting balance at the moment of mutation)
  - `reference_type` and `reference_id` (polymorphic links to `Sale` or `Transfer`)

### 2. Multi-Store Scoping & Authorization
- **Domain Policies & Query Scopes:** Eloquent models provide `.accessibleBy($user)` scopes ensuring data isolation at the database query level.
- **Route-Level Role Middleware:** Administrative operations (e.g., user account creation, product definition) are protected by `role:admin` middleware.

---

## Branch & Store Organizational Structure

The application is seeded with the exact operational setup described in the trial specifications:

```
KK Wholesalers Network
├── Branch 1 — Nairobi
│   └── Superior Center [1 Store]
│
└── Branch 2 — Kisumu
    ├── Mega City [Store 1]
    └── Lake Side Distributors [Store 2]
```

---

## Role-Based Access Control (RBAC)

| Role | Scope & Permissions | Access Restrictions |
|---|---|---|
| **Administrator** | Full read/write access across all branches, stores, products, users, reports, sales, and transfers. | None. Unrestricted global access. |
| **Branch Manager** | Scoped to their assigned branch. Read/write access across all stores under their branch. Can initiate branch-level transfers and view branch sales metrics. | Cannot edit other branches or manage user accounts. |
| **Store Manager** | Scoped strictly to their assigned store outlet (`auth()->user()->store_id`). Can record POS sales, receive stock, request transfers, and view store inventory. | Cannot view or transact on other stores. |

---

## Docker Setup Instructions

The repository includes a complete Docker configuration featuring PHP 8.4 FPM, Nginx, MySQL 8.0, phpMyAdmin, Grafana, Loki, and Promtail.

### 1. Prerequisites
- Docker Engine & Docker Compose (v2.0+) installed and running.

### 2. Launch Containers
```bash
# 1. Clone or navigate to project directory
cd /path/to/KK-ERP

# 2. Copy Docker environment file
cp .env.example .env

# 3. Build and launch containers in background
docker compose up -d --build
```

### 3. Initialize Database & Seed Sample Data
```bash
# Run migrations and seed branches, stores, products, users, and transactions
docker compose exec app php artisan migrate:fresh --seed
```

### 4. Access the Application
- **Web Application:** [http://localhost:8000](http://localhost:8000)
- **phpMyAdmin (Database GUI):** [http://localhost:8080](http://localhost:8080)
  - Server: `db`
  - Username: `kk_user`
  - Password: `secret`
- **Grafana Monitoring & Observability:** [http://localhost:3000](http://localhost:3000)
  - Username: `admin`
  - Password: `admin`
  - Pre-configured dashboard: **KK Wholesalers ERP — Observability Dashboard**
- **Loki Log Ingestion Service:** [http://localhost:3100](http://localhost:3100)
- **MySQL Direct Port:** `localhost:3306`

---

## Local Environment Setup (Alternative)

If running directly on a machine with PHP 8.2+ and Composer installed:

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Configure your database in .env (MySQL or SQLite)
# Run migrations & seeders
php artisan migrate:fresh --seed

# 4. Start local development server
php artisan serve --port=8000
```

---

## Pre-Configured Demo Accounts

All seeded accounts share the default password: **`password123`**

| Role | Name | Email | Assigned Scope |
|---|---|---|---|
| **Administrator** | System Administrator | `admin@kkwholesalers.com` | Global (All Branches & Stores) |
| **Branch 1 Manager** | James Mwangi | `branch1manager@kkwholesalers.com` | Branch 1 (Superior Center) |
| **Branch 2 Manager** | Susan Karanja | `branch2manager@kkwholesalers.com` | Branch 2 (Mega City & Lake Side Distributors) |
| **Store 1 Manager** | Peter Ochieng | `store1manager@kkwholesalers.com` | Store 1 (Superior Center) |
| **Store 2 Manager** | Amina Salim | `store2manager@kkwholesalers.com` | Store 2 (Mega City) |
| **Store 3 Manager** | David Kiprono | `store3manager@kkwholesalers.com` | Store 3 (Lake Side Distributors) |

---

## Monitoring & Observability (Grafana + Loki)

KK-ERP is instrumented with aggressive, multi-channel structured logging and an automated **Loki + Promtail + Grafana** telemetry stack.

### Architecture Overview

```
[ Laravel App ]
       │ Writes multi-channel daily logs
       ▼
 [ storage/logs/ ]
   ├── audit-YYYY-MM-DD.log       (Stock mutations, sales, transfers, count adjustments)
   ├── security-YYYY-MM-DD.log    (Logins, failed attempts, logouts, persona switches, 403s)
   ├── operations-YYYY-MM-DD.log  (Product, store, branch, and user CRUD)
   ├── queries-YYYY-MM-DD.log     (Slow database queries > 100ms)
   └── laravel-YYYY-MM-DD.log     (Uncaught exceptions, HTTP request/response metrics)
       │
       ▼ (read-only mount)
 [ Promtail Log Collector ]
       │ - Multiline parsing (preserves stack traces)
       │ - Dynamic channel extraction from filenames
       │ - Regex extraction of severity levels & timestamps
       ▼ (HTTP push)
 [ Loki 3.0 Storage ]
       │ Indexed TSDB store with 14-day retention
       ▼
 [ Grafana ]
       Auto-provisions Loki datasource & "KK Wholesalers ERP — Observability Dashboard"
```

### Pre-Configured Dashboard Features

Navigate to [http://localhost:3000](http://localhost:3000) (User: `admin` / Password: `admin`):

1. **Error & Health Counters:** Real-time stat panels displaying total application errors, security incidents, stock ledger movements, and slow database queries.
2. **Log Events by Severity Level:** Stacked time-series graph categorizing log volume into `ERROR`, `WARNING`, `INFO`, and `DEBUG`.
3. **Log Rate by Channel:** Real-time throughput (lines/sec) across `audit`, `security`, `operations`, `queries`, and `laravel`.
4. **Dedicated Security Log Stream:** Live feed tracking user authentications, credential failures, unauthorized role access (`403 Forbidden`), and persona switches.
5. **Stock Ledger & POS Audit Stream:** Live feed tracking inventory events with product IDs, store IDs, user attribution, and quantity deltas.
6. **Unified Live Explorer:** Interactive filterable log stream supporting channel selection, severity selection, and full-text keyword search.

### Useful LogQL Query Examples

- **All Application Errors:**
  ```logql
  {job="kk_erp", level="ERROR"}
  ```
- **Failed Login Attempts:**
  ```logql
  {job="kk_erp", channel="security"} |= "Failed login attempt"
  ```
- **Slow Database Queries (>100ms):**
  ```logql
  {job="kk_erp", channel="queries"}
  ```
- **Stock Movement Audit Trail for a Specific Product:**
  ```logql
  {job="kk_erp", channel="audit"} |= "product_id"
  ```

---

## Automated Test Suite

The application includes comprehensive unit and end-to-end feature tests covering:
- Concurrency-safe inventory deductions and insufficient stock handling (`InventoryServiceTest`).
- Store-to-store stock transfers, balance integrity, and movement pair generation (`TransferServiceTest`).
- POS checkout calculations, taxes, discounts, and line items persistence (`SaleServiceTest`).
- Role-based scoping and authorization boundaries (`RbacScopingTest`).
- End-to-end HTTP request workflows (`E2EWorkflowTest`).

### Running Tests

**Inside Docker:**
```bash
docker compose exec app php artisan test
```

**Locally:**
```bash
php artisan test
```
---

## Assumptions Made

1. **Direct Transfers Execution:** Transfers execute immediately from origin to destination upon submission (instant transit). A status field (`completed`, `pending`, `cancelled`) is retained to accommodate multi-step transit dispatch/acceptance if required.
2. **Stock Availability Validation:** Quantities cannot drop below zero. Any operation that would result in negative inventory throws an exception and halts the database transaction.
3. **Single Currency:** Transactions and valuations operate in Kenya Shillings (`KES`). Multi-currency support is omitted for simplicity.
4. **Point of Sale Invoicing:** Invoices are generated sequentially upon successful transaction completion and can be viewed or printed anytime.

---

## Known Limitations

1. **Perishable Goods Batch / Expiry Tracking:** The current schema handles SKU-level unit quantities. Tracking specific batch numbers or expiration dates (FIFO / FEFO) is outside the scope of this trial monolith.
2. **External Payment Gateway Handshakes:** The POS supports selecting payment methods (Cash, M-Pesa, Card, Bank Transfer, Credit) with reference logging, but does not integrate real-time external payment provider webhooks (e.g. Daraja STK Push).
3. **Offline Sync (PWA):** The POS requires an active network connection to verify and reserve stock under pessimistic database locking.

---
