# Premium Payment System - Architecture Diagram

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER INTERFACE (Frontend)                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Dashboard                        Premium Modal                   │
│  ┌──────────────────┐             ┌──────────────────┐            │
│  │ Free Cards       │             │  Plan Details    │            │
│  │ - CollabSphere   │             │  - $4.99/month   │            │
│  │ - FocusFlow      │  ──click──> │  - Features list │            │
│  │ - MindPlay       │  (locked)   │                  │            │
│  │                  │             │  Payment Form    │            │
│  │ Premium Cards    │             │  - Card #        │            │
│  │ 🔒 QuizForge     │             │  - Expiry        │            │
│  │ 🔒 InfoVault     │             │  - CVV           │            │
│  └──────────────────┘             │  ┌──────────────┐ │            │
│                                   │  │  Pay Now     │ │            │
│                                   │  │  Processing  │ │            │
│                                   │  │  (2-3 sec)   │ │            │
│                                   │  └──────────────┘ │            │
│                                   │                  │            │
│                                   │  ✓ Success       │            │
│                                   │  Order ID shown  │            │
│                                   └──────────────────┘            │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                          ↕ (JavaScript)
                  assets/js/premium.js
┌─────────────────────────────────────────────────────────────────┐
│                    API LAYER (Backend)                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  POST /api/premium/initiate_payment.php                          │
│  ├─ Verify user authenticated                                   │
│  ├─ Get plan details (subscription_plans)                       │
│  ├─ Check no active subscription exists                         │
│  ├─ Create subscription record (user_subscriptions)             │
│  ├─ Generate order_id & payment_id                              │
│  └─ Create payment order (payment_orders)                        │
│     ↓ Return: { order_id, payment_id, amount }                  │
│                                                                   │
│  POST /api/premium/confirm_payment.php                           │
│  ├─ Verify order exists & is pending                            │
│  ├─ Update payment status → 'success'                           │
│  ├─ Update users.is_premium → 1                                 │
│  └─ Return success response                                     │
│     ↓ Frontend reloads page                                      │
│                                                                   │
│  GET /api/premium/get_plans.php                                  │
│  └─ Return available plans from database                        │
│     ↓ Frontend populates modal                                   │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
                          ↕ (SQL Queries)
┌─────────────────────────────────────────────────────────────────┐
│                   DATABASE LAYER (MySQL)                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  subscription_plans                                              │
│  ├─ id: INT PRIMARY KEY                                         │
│  ├─ name: VARCHAR (e.g., "Pro Plan")                            │
│  ├─ price: DECIMAL (e.g., 4.99)                                 │
│  ├─ billing_cycle: ENUM (monthly, yearly)                       │
│  └─ features: JSON                                              │
│                                                                   │
│  user_subscriptions                                              │
│  ├─ id: INT PRIMARY KEY                                         │
│  ├─ user_id: INT → users(id)                                    │
│  ├─ plan_id: INT → subscription_plans(id)                       │
│  ├─ status: ENUM (active, cancelled, expired)                   │
│  ├─ start_date: TIMESTAMP                                       │
│  ├─ end_date: TIMESTAMP (expires after 1 month)                 │
│  └─ renewal_date: TIMESTAMP                                     │
│                                                                   │
│  payment_orders (AUDIT TRAIL)                                    │
│  ├─ id: INT PRIMARY KEY                                         │
│  ├─ user_id: INT → users(id)                                    │
│  ├─ subscription_id: INT → user_subscriptions(id)               │
│  ├─ amount: DECIMAL                                             │
│  ├─ order_id: VARCHAR (dummy: ORD-xyz-timestamp)                │
│  ├─ payment_id: VARCHAR (dummy: PAY-abc-timestamp)              │
│  ├─ status: ENUM (pending, success, failed)                     │
│  ├─ created_at: TIMESTAMP                                       │
│  └─ completed_at: TIMESTAMP                                     │
│                                                                   │
│  users (EXISTING - MODIFIED)                                     │
│  └─ is_premium: TINYINT(1) → updated to 1 on payment success    │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Payment Flow Sequence

```
User                   Frontend              Backend              Database
│                        │                      │                    │
├─ Click Locked Card ──→ │                      │                    │
│                        │                      │                    │
│                        │─ Load Modal ────────→│ GET /get_plans.php │
│                        │                      │                    │
│                        │←─ Plan Details ──────│←───────────────────┤
│                        │                      │                    │
│   (User fills form)    │                      │                    │
│                        │                      │                    │
├─ Click Pay Now ──────→ │─ Validate Form      │                    │
│                        │─ Show Spinner       │                    │
│                        │                      │                    │
│                        ├─ POST initiate_payment.php               │
│                        │                      │─ Create sub ──────→│
│                        │                      │─ Create order ────→│
│                        │←─ { order_id, ... }──│←────────────────────│
│                        │                      │                    │
│                        │   (2-3 sec wait)    │                    │
│                        │                      │                    │
│                        ├─ POST confirm_payment.php                │
│                        │                      │─ Verify order ────→│
│                        │                      │─ Update status ───→│
│                        │                      │─ Set is_premium ──→│
│                        │←─ { success } ───────│←────────────────────│
│                        │                      │                    │
│                        │─ Show Success       │                    │
│                        │─ Display Order ID   │                    │
│                        │                      │                    │
├─ Click Continue ─────→ │─ Reload Page        │                    │
│                        │                      │                    │
│ (Dashboard reloads)    │─ Check is_premium ──→│ isPremiumUser() ───→│
│ ✨ Premium Cards       │←─ Show ✨ Badges ────│ SELECT ... ────────→│
│ Now Clickable!         │                      │                    │
│                        │                      │                    │
```

---

## 📦 Component Dependencies

```
premium.js (Frontend)
├── Requires: premium.css (styling)
├── API Calls:
│   ├── /api/premium/get_plans.php
│   ├── /api/premium/initiate_payment.php
│   └── /api/premium/confirm_payment.php
└── DOM Elements:
    ├── .module-card.locked (triggers)
    ├── .premium-modal (container)
    └── Various input fields

dashboard.php
├── Requires: premium_check.php (isPremiumUser())
├── Includes: premium.css (from header_dashboard.php)
├── Includes: premium.js (from footer.php)
└── Conditional Logic:
    └── if ($userIsPremium) → show ✨ | else → show 🔒

premium_check.php
├── Requires: db.php (PDO connection)
└── Exports Functions:
    ├── isPremiumUser()
    ├── getUserSubscription()
    ├── getAvailablePlans()
    ├── requirePremium()
    └── getPremiumFeatures()

API Endpoints
├── initiate_payment.php
│   ├── Requires: db.php, session
│   └── Database: SELECT from subscription_plans
│                 INSERT into user_subscriptions
│                 INSERT into payment_orders
│
├── confirm_payment.php
│   ├── Requires: db.php, session
│   └── Database: SELECT from payment_orders
│                 UPDATE payment_orders
│                 UPDATE users
│
└── get_plans.php
    ├── Requires: db.php
    └── Database: SELECT from subscription_plans
```

---

## 🎯 State Machine (Payment Modal)

```
┌──────────────┐
│   CLOSED     │  Initial state
└──────┬───────┘
       │
       └─ Click locked card ──→ ┌──────────────┐
                                 │   OPENING    │  Load plan details
                                 └──────┬───────┘
                                        │
                      ┌─────────────────┘
                      │
                      └─→ ┌──────────────┐
                         │   FORM       │  Ready to accept input
                         └──────┬───────┘
                                │
                                ├─ Click Cancel ──→ CLOSED
                                │
                                ├─ Click Close ───→ CLOSED
                                │
                                └─ Click Pay ────→ ┌──────────────┐
                                                    │ PROCESSING   │  2-3 sec wait
                                                    └──────┬───────┘
                                                           │
                                        ┌──────────────────┼──────────────────┐
                                        │                  │                  │
                                   Success           Network Error       Invalid Input
                                        │                  │                  │
                                        │                  │                  │
                                        └─→ ┌─────────┐    └─→ ┌──────────┐   │
                                           │ SUCCESS │       │ ERROR    │   │
                                           └─────┬───┘       └───┬──────┘   │
                                                 │               │         │
                                                 │               └─ Retry  │
                                                 │                    │   │
                                                 │                    └───┘
                                                 │
                                                 └─ Click Continue ──→ ┌──────┐
                                                                        │RELOAD│
                                                                        └──────┘
                                                                           │
                                                                           └─→ CLOSED
                                                                              (with ✨)
```

---

## 💾 Data Flow

### On Payment Initiation

```javascript
// Frontend sends
{
  plan: "Pro Plan"
}

// Backend creates & responds
{
  success: true,
  payment: {
    order_id: "ORD-abc123-1704556800",
    payment_id: "PAY-def456-1704556800",
    amount: 4.99,
    plan: "Pro Plan",
    subscription_id: 1
  }
}

// Frontend stores in variable for later
this.currentPayment = {...}

// Database state after:
user_subscriptions: NEW RECORD { user_id: 1, status: "active" }
payment_orders:     NEW RECORD { status: "pending", order_id: "...", payment_id: "..." }
users:              UNCHANGED
```

### On Payment Confirmation

```javascript
// Frontend sends
{
  order_id: "ORD-abc123-1704556800",
  payment_id: "PAY-def456-1704556800"
}

// Backend verifies & responds
{
  success: true,
  message: "Payment successful! Premium activated.",
  subscription_id: 1
}

// Database state after:
user_subscriptions: UNCHANGED (already active)
payment_orders:     UPDATED { status: "success", completed_at: NOW() }
users:              UPDATED { is_premium: 1 }
```

---

## 🔐 Security Layers

```
Input Validation
├─ Frontend: Form.checkValidity()
├─ Backend: Prepared statements (SQL injection)
└─ Backend: Session verification ($_SESSION['user_id'])

Business Logic
├─ Check user authenticated
├─ Check no active subscription exists
├─ Check order exists & is pending before confirming
└─ Verify subscription record in database

Data Integrity
├─ Use PDO prepared statements (all queries)
├─ Validate user_id matches session
├─ Verify order belongs to authenticated user
└─ Check subscription end_date before granting access

Audit Trail
├─ payment_orders table logs all transactions
├─ Includes timestamps (created_at, completed_at)
└─ Can identify fraud patterns

(For production with real gateway)
├─ Enable HTTPS (required for PCI compliance)
├─ Verify webhook signatures
├─ Never log card details
├─ Use tokenization for payment info
└─ Follow PCI DSS standards
```

---

## 📊 Data Relationships

```
┌──────────────┐
│    users     │
├──────────────┤
│ id (PK)      │
│ email        │────┐
│ is_premium   │    │
│ created_at   │    │
└──────────────┘    │
                    │ (1:N)
                    │
       ┌────────────┼────────────┐
       │            │            │
       │            ▼            │
       │  ┌──────────────────┐   │
       │  │user_subscriptions│   │
       │  ├──────────────────┤   │
       │  │ id (PK)          │   │
       │  │ user_id (FK) ────┘   │
       │  │ plan_id ──────┐      │
       │  │ status        │      │
       │  │ start_date    │      │
       │  │ end_date      │      │
       │  └──────────────┬┘      │
       │                │        │
       │                │ (N:1)  │
       │                │        │
       │                ▼        │
       │      ┌──────────────────┤
       │      │subscription_plans│
       │      ├──────────────────┤
       │      │ id (PK)          │
       │      │ name             │
       │      │ price            │
       │      │ features (JSON)  │
       │      └──────────────────┘
       │
       └─────────────────────┐
                             │ (1:N)
                             │
                             ▼
                 ┌──────────────────────┐
                 │   payment_orders     │
                 ├──────────────────────┤
                 │ id (PK)              │
                 │ user_id (FK) ────────┘
                 │ subscription_id (FK) ──┐
                 │ order_id             │  (N:1)
                 │ payment_id           │
                 │ status               │
                 │ created_at           │
                 │ completed_at         │
                 └──────────────────────┘
```

---

## 🚀 Deployment Topology

### Development

```
Local Machine
├─ Apache/PHP (XAMPP)
├─ MySQL Database
├─ Dummy Payment (in-process)
└─ No external APIs
```

### Production (Current - Dummy)

```
Web Server
├─ PHP Application
├─ Same Database Schema
├─ Dummy Payment (still in-process)
└─ No external APIs (safe!)
```

### Production (Future - Real Gateway)

```
Web Server                          Payment Gateway
├─ PHP Application                  ├─ Stripe
├─ Database                         ├─ PayPal
├─ initiate_payment.php ────────────→ Create Session
├─ confirm_payment.php ←─────────────  Webhook Signature
│  (verify webhook)                │─ Tokenize Card
└─ payment_orders table            └─ Charge Account
```

---

## 📈 Scaling Considerations

```
Current Load (Dummy)
├─ No rate limiting needed (demo only)
├─ Database queries are optimized (indexed)
└─ Modal rendering is instant

Scaled Load (Real Gateway)
├─ Add rate limiting to APIs
│  └─ Max 1 payment per user per minute
├─ Add request validation
│  └─ Check plan exists before processing
├─ Add database indexes
│  ├─ user_subscriptions(user_id, status)
│  ├─ payment_orders(user_id, created_at)
│  └─ subscription_plans(is_active)
├─ Cache subscription plans
│  └─ Redis or in-memory cache
└─ Monitor payment gateway
   ├─ Track webhook delivery
   ├─ Retry failed webhooks
   └─ Alert on gateway errors
```
