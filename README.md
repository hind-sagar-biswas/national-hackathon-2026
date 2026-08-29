# MaMoney — High-Integrity Double-Entry Banking & Digital Wallet Ledger

An enterprise-grade, high-throughput digital banking engine, digital wallet, and cryptographically verified double-entry accounting platform developed for **National Hackathon 2026 PSTU**.

Built on **Laravel 13**, **Laravel Octane (FrankenPHP)**, **PostgreSQL**, **Redis**, **Vue 3**, **Inertia.js v2**, **Tailwind CSS v4**, **daisyUI**, and **PrimeVue**.

---

## 🌟 Core Architecture & Financial Capabilities

### 1. Immutable Double-Entry Ledger Engine
- **Zero-Sum Mathematical Integrity**: Every financial operation generates balanced debit and credit `LedgerEntry` records against double-entry principles.
- **System Accounts Ecosystem**:
  - `system:reserves` — Total platform backing liquidity.
  - `system:fee_revenue` — Accumulated service fees and processing revenue.
  - `system:settlement` — Inter-bank clearing and gateway settlement.
  - `system:platform_equity` — Platform capital and net equity.
- **Continuous Reconciliation**: Automated mathematical verification audits and daily balance sheet snapshot rollups (`ReconciliationService`) detecting discrepancies, unbacked liability, or imbalance.
- **Pessimistic Concurrency**: Row-level database locks (`lockForUpdate`) prevent race conditions, double-spending, and concurrent balance overdrafts.
- **Strict Monetary Precision**: All balances, transfers, and calculations are executed in integer Paisa/Cents to eliminate floating-point rounding errors.

### 2. Multi-Mode Shared Expense & Bill Splitting
A collaborative invoice & expense distribution engine supporting 4 mathematical splitting algorithms with automated escrow holds:
- **`EQUAL`**: Splits the invoice equally across all participants including the initiator.
- **`EXACT`**: Custom exact BDT allocation per participant with automatic residual balancing.
- **`PERCENTAGE`**: Proportional distribution with strict 100% sum validation.
- **`SHARES`**: Weighted ratio allocations (e.g., 1 share vs 2 shares).
- **Escrow Hold Workflow**:
  1. Initiator creates split; invitations with real-time notifications are dispatched.
  2. Participants accept $\rightarrow$ system places a temporary **`Hold`** on their available balance.
  3. When all participants accept, the transaction executes atomically, settling all holds into a single completed transaction.
  4. If declined or expired, all reserved holds are released back to participant wallets.

### 3. Peer-to-Peer (P2P) Micro-Loans
- **Borrower-Initiated Flow**: Users submit P2P loan requests to prospective lenders with repayment due dates and notes.
- **Lender Approval & Disbursement**: Lender authorization triggers atomic wallet disbursement with ledger verification.
- **Debt Tracking & Repayment**: Real-time tracking of principal vs outstanding debt, supporting partial and full repayments.
- **Debt Forgiveness / Waiver**: Lenders can waive remaining debt balances with official accounting write-offs.

### 4. Direct Money Requests & Invoicing
- Instant P2P money requests with optional expiration timers and pre-holds.
- Support for standard payment requests and loan disbursement requests.
- Single-click approval, decline, and requester cancellation workflows.

### 5. Multi-Channel Deposit Clearing
- Support for **bKash**, **Nagad**, **Bank Wire**, and **Cash Deposit**.
- Automated credit workflows or compliance review queue for manual administrative approval.
- Complete deposit audit trail with reference tracking.

### 6. Intelligent Risk Evaluation & Security Holds
- **`RiskEvaluationService`**: Rules engine checking for transaction velocity, high-value transfers, and sudden balance depletion.
- **Stepped-Up OTP Challenges**: High-risk operations require 6-digit one-time password verification (`OtpService`) before ledger execution.
- **Compliance Holds**: Suspicious or high-risk transfers trigger temporary escrow holds (`HoldService`) requiring admin compliance review.

### 7. Administrative Oversight & Compliance Portal
- **Financial Health Dashboard**: Real-time breakdown of user liabilities, platform equity, active escrow holds, and system reserves.
- **Deposit Approvals Queue**: Verify customer deposit slips with provider transaction IDs.
- **Risk Holds Management**: Review triggered risk rules and release or liquidate reserved balances.
- **Mathematical Audit & Reconciliation**: One-click zero-sum audit execution across all accounts with checkpoint logging.
- **Global Transaction Explorer**: Complete audit log of all system-wide debit/credit ledger lines with printable digital receipts.
- **User Directory & Account Suspension**: Role-based access control (RBAC) and user account status toggles.

---

## 🛠️ Technology Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend Framework** | Laravel 13 running on PHP 8.5 with Constructor Property Promotion |
| **App Server** | Laravel Octane with FrankenPHP worker mode |
| **Database** | PostgreSQL with connection pooling |
| **Cache & Queues** | Redis (Valkey) |
| **Realtime WebSockets** | Laravel Reverb + Laravel Echo |
| **Frontend Framework** | Vue 3 (Composition API, `<script setup>`) + Inertia.js v2 |
| **UI & Styling** | Tailwind CSS v4 + daisyUI v5 + PrimeVue v4 + Lucide Icons |
| **Route Generation** | Laravel Wayfinder (Typed TypeScript/JS actions & routes) |
| **Authorization** | Spatie Laravel Permission (Enums: `Role`, `Permission`) |
| **Code Formatting** | Laravel Pint (PSR-12 / Laravel Preset) |

---

## 📐 Domain Architecture & Services

```
app/
├── Enums/
│   ├── AccountType.php             # System vs User account categorization
│   ├── BillSplitMode.php           # EQUAL, EXACT, PERCENTAGE, SHARES
│   ├── BillSplitStatus.php         # PENDING, COMPLETED, CANCELLED, FAILED
│   ├── DepositProvider.php         # BKASH, NAGAD, BANK, CASH
│   ├── HoldStatus.php              # ACTIVE, RELEASED, SETTLED
│   ├── LoanStatus.php              # PENDING, ACTIVE, PARTIAL, SETTLED, WAIVED
│   ├── MoneyRequestType.php        # STANDARD, LOAN
│   ├── Permission.php              # Granular RBAC permissions
│   ├── Role.php                    # ADMIN, USER
│   └── TransactionType.php         # TRANSFER, DEPOSIT, BILL_SPLIT, LOAN, etc.
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                  # Admin Deposits, Holds, Reconciliation, Transactions
│   │   ├── BillSplitController.php # Multi-mode split creation, accept, reject, destroy
│   │   ├── DashboardController.php # Balance sheet, wallet analytics, quick actions
│   │   ├── DepositController.php   # User deposit requests
│   │   ├── LoanController.php      # P2P loan requests, repayments, and waivers
│   │   ├── MoneyRequestController.php # Direct payment requests
│   │   ├── TransactionController.php # Receipts and transaction search
│   │   └── TransferController.php  # P2P transfers with OTP and risk checks
│   ├── Requests/                   # Validated Form Requests with Idempotency
│   └── Resources/                  # Eloquent API / Inertia JSON Resources
├── Models/
│   ├── Account.php                 # Core wallet & system ledger account
│   ├── BillSplit.php               # Multi-participant bill split entity
│   ├── BillSplitParticipant.php    # Individual split participant share & hold
│   ├── DepositRequest.php          # Deposit tracking with gateway references
│   ├── Hold.php                    # Escrow & compliance balance holds
│   ├── LedgerEntry.php             # Immutable debit/credit record
│   ├── Loan.php                    # P2P loan contract
│   ├── LoanRepayment.php           # Installment payment tracking
│   ├── MoneyRequest.php            # Direct invoicing entity
│   ├── ReconciliationCheckpoint.php# Mathematical audit snapshots
│   ├── Transaction.php             # High-level financial transaction
│   └── User.php                    # User identity & wallet relationship
└── Services/
    ├── Auth/
    │   └── OtpService.php          # Secure 6-digit challenge code delivery
    ├── Banking/
    │   ├── BillSplitService.php    # 4-mode split math & hold orchestration
    │   ├── DepositService.php      # Deposit crediting & approvals
    │   ├── HoldService.php         # Balance hold reservation & release
    │   ├── LoanService.php         # P2P loan lifecycle & debt settlements
    │   ├── MoneyRequestService.php # Direct payment processing
    │   ├── ReconciliationService.php # Zero-sum GL audit & ledger rollups
    │   └── TransferService.php     # Atomic transfer execution with risk checks
    └── Risk/
        ├── RiskEvaluationService.php # Dynamic anomaly & velocity evaluation
        └── Rules/                  # HighValue, Velocity, DropRate rules
```

---

## 🚀 Getting Started

### Prerequisites
- **PHP 8.3+** with extensions (`pdo_pgsql`, `redis`, `bcmath`, `intl`)
- **Composer**
- **Node.js 20+** and **npm**
- **PostgreSQL** & **Redis**

### Quick Setup

```bash
# 1. Clone repository
git clone https://github.com/hind-sagar-biswas/national-hackathon-2026.git
cd national-hackathon-2026

# 2. Automated bootstrap (install dependencies, generate keys, migrate & seed, build assets)
composer run setup
```

### Manual Setup

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan wayfinder:generate --with-form --no-interaction
php artisan storage:link
npm run build
```

### Starting the Development Environment

```bash
composer run dev
```
This automatically boots:
- FrankenPHP / Octane Web Server
- Queue Worker
- Laravel Pail log stream
- Vite Hot-Module-Replacement (HMR)

---

## 🔑 Default Seeded Credentials

| Account | Email | Password | Role |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `super@test.com` | `password` | `admin` |
| **Standard User 1** | `user1@test.com` | `password` | `user` |
| **Standard User 2** | `user2@test.com` | `password` | `user` |

---

## 🧪 Testing & Code Quality

### Code Formatter
```bash
# Run Pint to ensure compliance with PSR-12 and Laravel guidelines
vendor/bin/pint
```

### Route Generation
```bash
# Regenerate typed Wayfinder routes after making controller or route edits
php artisan wayfinder:generate --with-form --no-interaction
```

---

## 📜 License
This project is open-sourced software licensed under the [MIT License](LICENSE).
