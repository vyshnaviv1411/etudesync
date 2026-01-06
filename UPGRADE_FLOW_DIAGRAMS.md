# Premium Upgrade Flow - Visual Diagrams

## 🎯 User Journey

```
┌─────────────────┐
│   User Login    │
│   (dashboard)   │
└────────┬────────┘
         │
         ▼
    ┌─────────────────────┐
    │  Dashboard Page     │
    │  - FocusFlow ✓      │
    │  - CollabSphere ✓   │
    │  - QuizForge 🔒     │
    │  - InfoVault 🔒     │
    └────────┬────────────┘
             │ (click locked card)
             ▼
    ┌──────────────────────┐
    │  Upgrade Page        │
    │  (/upgrade.php)      │
    │  - Payment form      │
    │  - Features list     │
    │  - Price display     │
    └────────┬─────────────┘
             │ (fill form)
             ▼
    ┌──────────────────────┐
    │  Processing...       │
    │  (2-3 sec delay)     │
    └────────┬─────────────┘
             │
             ▼
    ┌──────────────────────┐
    │  ✓ Premium Activated!│
    │  (success state)     │
    └────────┬─────────────┘
             │ (1 sec, then auto)
             ▼
    ┌──────────────────────┐
    │  Dashboard Page      │
    │  - FocusFlow ✓       │
    │  - CollabSphere ✓    │
    │  - QuizForge ✨      │
    │  - InfoVault ✨      │
    └──────────────────────┘
```

---

## 🔌 Technical Architecture

### Frontend Flow

```
upgrade.php (Page)
    │
    ├─→ form#upgradeForm (HTML5 form)
    │
    ├─→ JavaScript Event Listener
    │   │
    │   ├─ Prevent default form submission
    │   │
    │   ├─ Disable button + show "Processing..."
    │   │
    │   ├─ Wait 2-3 seconds (simulated)
    │   │
    │   └─ POST to process_upgrade.php
    │
    └─→ Response Handler
        │
        ├─ If success:
        │  ├─ Show "✓ Premium Activated!"
        │  ├─ Change button color to green
        │  └─ Redirect to dashboard.php (1 sec)
        │
        └─ If error:
           ├─ Show error message
           ├─ Re-enable button
           └─ Keep user on upgrade page
```

### Backend Flow

```
POST /api/premium/process_upgrade.php

1. VALIDATE PHASE
   ├─ Check session exists
   ├─ Check user ID in session
   ├─ Check not already premium
   └─ Return 401/403 on failure

2. FETCH PHASE
   ├─ Get Pro Plan from database
   └─ Handle missing plan error

3. CREATE PHASE
   ├─ Create payment_order record
   ├─ Create user_subscription record
   ├─ Update users.is_premium = 1
   └─ Handle DB errors

4. RESPONSE PHASE
   └─ Return JSON
      ├─ success: true/false
      ├─ message: description
      ├─ subscription_id: number
      └─ redirect: dashboard.php
```

---

## 📊 Database Schema Flow

```
PAYMENT FLOW:

┌─────────────────────────┐
│  users table            │
│  id | is_premium | ...  │
│  1  | 0          |      │ ← Free user
└─────────────────────────┘
         │
         │ upgrade.php
         │ (form submission)
         ▼
┌──────────────────────────────┐
│ process_upgrade.php          │
│ (handles payment logic)       │
└──────────────────────────────┘
         │
         ├─────────────────┬─────────────┬──────────────┐
         │                 │             │              │
         ▼                 ▼             ▼              ▼
┌──────────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────┐
│ subscription_    │ │ payment_     │ │ users        │ │ Response │
│ plans table      │ │ orders table │ │ table        │ │ JSON     │
│                  │ │              │ │              │ │          │
│ Fetched:         │ │ INSERT       │ │ UPDATE       │ │ {        │
│ - id (Pro Plan)  │ │ - order_id   │ │ is_premium=1 │ │ success: │
│ - price          │ │ - payment_id │ │              │ │   true   │
│ - features       │ │ - status     │ │              │ │ }        │
└──────────────────┘ └──────────────┘ └──────────────┘ └──────────┘

RESULT:

┌─────────────────────────┐
│  users table            │
│  id | is_premium | ...  │
│  1  | 1          |      │ ← Premium user!
└─────────────────────────┘
```

---

## 🔄 State Transitions

### User State Machine

```
                    ┌──────────────┐
                    │  Logged Out  │
                    └──────┬───────┘
                           │ Login
                           ▼
                    ┌──────────────┐
        ┌───────────→│  Free User   │←─────────┐
        │            └──────┬───────┘          │
        │                   │                  │
        │                   │ Click upgrade    │
        │                   │                  │
        │                   ▼                  │
        │            ┌──────────────┐          │
        │            │ Upgrade Form │          │
        │            └──────┬───────┘          │
        │                   │                  │
        │      ┌────────────┼────────────┐     │
        │      │            │            │     │
        │      ▼            ▼            ▼     │
        │   Cancel       Submit        Error   │
        │      │            │            │     │
        │      └────────────┼────────────┘     │
        │                   │                  │
        │                   ▼                  │
        │            ┌──────────────┐          │
        └────────────│Premium User  │          │
                     └──────┬───────┘          │
                            │                  │
                            │ Try upgrade      │
                            │ again (redirect) │
                            └──────────────────┘
```

### Button State Flow

```
┌──────────────────┐
│ Unlock Premium   │ ← Initial state
└────────┬─────────┘
         │ Click
         ▼
┌──────────────────┐
│ Processing...    │ ← Loading state (disabled)
│ (2-3 seconds)    │
└────────┬─────────┘
         │
    ┌────┴─────┐
    │           │
    ▼           ▼
┌──────────────┐ ┌──────────────┐
│✓ Premium     │ │ Unlock       │ ← Error state (re-enabled)
│ Activated!   │ │ Premium      │ + error message
│ (green bg)   │ └──────────────┘
└──────────────┘
    │
    │ (1 second)
    ▼
Redirect to dashboard
```

---

## 🎨 Design Hierarchy

```
upgrade.php (Page Container)
│
├─ .auth-page (Full-height background)
│  │  - Gradient background
│  │  - Backdrop blur
│  │
│  └─ .auth-wrap (Centering container)
│     │
│     └─ .glass-auth-card (Main card)
│        │
│        ├─ Logo image
│        │  └─ .logo-center
│        │
│        ├─ h2 "Unlock Premium"
│        │  └─ Typography: Poppins bold
│        │
│        ├─ Description paragraph
│        │  └─ Typography: Inter regular
│        │
│        ├─ Features list box
│        │  ├─ Background: rgba(124,77,255,0.1)
│        │  ├─ Border: rgba(124,77,255,0.2)
│        │  └─ List items with ✓
│        │
│        ├─ form#upgradeForm
│        │  │
│        │  ├─ Form group (name)
│        │  ├─ Form group (card number)
│        │  ├─ Form group (expiry + CVV)
│        │  ├─ Demo notice
│        │  │
│        │  └─ button.btn-login
│        │     └─ Linear gradient: #2D5BFF → #47d7d3
│        │
│        └─ Meta section
│           └─ Back to Dashboard link
```

---

## 🔐 Security Flow

```
Request arrives at /upgrade.php

1. SESSION CHECK
   │
   ├─ session_start() active?
   │  └─ Yes → Continue
   │  └─ No → Start session
   │
   └─ $_SESSION['user_id'] exists?
      ├─ Yes → Continue
      └─ No → Redirect to login.php

2. PREMIUM CHECK
   │
   └─ isPremiumUser($user_id)?
      ├─ Yes → Redirect to dashboard
      └─ No → Show upgrade form

3. FORM SUBMISSION
   │
   └─ POST to process_upgrade.php
      │
      ├─ Session validation
      ├─ Authentication check
      ├─ Prepared statements (no SQL injection)
      ├─ User ID from session (no tampering)
      └─ HTML escape output (no XSS)
```

---

## 📈 Data Flow Diagram

```
Client Browser                    Server

1. User clicks locked card
   │
   └─→ GET /upgrade.php
       │
       ├─ Check session ✓
       ├─ Check premium ✗
       │
       └─→ Return HTML form

2. User fills form
   │
   └─→ Fetch request
       (POST to process_upgrade.php)
       │
       ├─ Validate session ✓
       ├─ Validate not premium ✓
       ├─ Get Pro Plan ✓
       ├─ Create payment_order ✓
       ├─ Create subscription ✓
       ├─ Update is_premium ✓
       │
       └─→ Return JSON
           {success: true}

3. JavaScript processes response
   │
   ├─ Show success message
   ├─ Change button color
   │
   └─→ Redirect to dashboard.php
       │
       └─ GET /dashboard.php
           │
           ├─ Check session ✓
           ├─ Check premium ✓ (now premium!)
           │
           └─→ Return dashboard with unlocked cards
```

---

## 🎬 Timeline: Payment Processing

```
Timeline (seconds)

0.0s: User clicks "Unlock Premium"
      │
      ├─ Button disabled
      ├─ Button text = "Processing..."
      │
      ▼

0.1s: JavaScript event triggered
      │
      ├─ Prevent form submission
      ├─ Create FormData
      │
      ▼

0.5s: Simulated delay starts
      │
      ├─ await new Promise(resolve =>
      │    setTimeout(resolve, 2000 + Math.random() * 1000))
      │
      ▼

2.0-3.0s: POST request sent
      │
      ├─ /api/premium/process_upgrade.php
      ├─ Database operations
      ├─ Response returned
      │
      ▼

3.0s+: Response processed
      │
      ├─ Check success flag
      │
      ├─ If success:
      │  ├─ Button text = "✓ Premium Activated!"
      │  ├─ Button background = green
      │  ├─ Wait 1 second
      │  └─ window.location.href = 'dashboard.php'
      │
      └─ If error:
         ├─ Show alert(error)
         ├─ Restore button state
         └─ User can retry

3.5s: Button shows success (1 more second)
      │
      ▼

4.5s: Auto-redirect to dashboard
      │
      ▼

5.0s: Dashboard loads
      │
      ├─ Session valid
      ├─ is_premium = 1 ✓
      │
      └─ Premium cards now unlock!
```

---

## 🌐 Page Load Waterfall

### upgrade.php Load Sequence

```
1. Browser: GET /upgrade.php
2. Server: Check session + premium status
3. Server: Include header_public.php
4. Server: Render HTML (auth page structure)
5. Server: Include footer.php
6. Server: Return full HTML
7. Browser: Parse HTML
8. Browser: Load inline script
9. Browser: Attach event listener to form
10. Browser: Display page (ready for user input)
```

### process_upgrade.php Call Sequence

```
1. Browser: FormData collected
2. Browser: POST request sent
3. Browser: Awaiting 2-3 second delay
4. Server: Receive POST request
5. Server: Validate session
6. Server: Get Pro Plan from DB
7. Server: Insert payment_order (DB)
8. Server: Insert user_subscription (DB)
9. Server: Update users.is_premium (DB)
10. Server: Return JSON response
11. Browser: Parse JSON
12. Browser: Check success flag
13. Browser: Update UI state
14. Browser: Redirect or show error
```

---

## 📱 Responsive Breakpoints

```
Mobile (375px)          Tablet (768px)       Desktop (1920px)

┌──────────────┐       ┌──────────────────┐  ┌──────────────────┐
│ Logo         │       │      Logo        │  │      Logo        │
│              │       │                  │  │                  │
│ Heading      │       │    Heading       │  │    Heading       │
│              │       │                  │  │                  │
│ Description  │       │  Description     │  │  Description     │
│              │       │                  │  │                  │
│ Features     │       │    Features      │  │    Features      │
│              │       │                  │  │                  │
│ Form         │       │     Form         │  │     Form         │
│ - Name       │       │     - Name       │  │     - Name       │
│ - Card       │       │     - Card       │  │     - Card       │
│ - Expiry     │       │     - Expiry CVV │  │     - Expiry CVV │
│ - CVV        │       │                  │  │                  │
│              │       │                  │  │                  │
│ Button       │       │    Button        │  │    Button        │
│              │       │                  │  │                  │
│ Back Link    │       │   Back Link      │  │   Back Link      │
└──────────────┘       └──────────────────┘  └──────────────────┘

All versions use same CSS classes from style.css
Card adjusts width: 100% mobile, 420px tablet+
```

---

## 🎯 Success Criteria

For the payment flow to be complete:

```
✓ User navigates from dashboard → upgrade.php
  └─ Verified: href="upgrade.php" in dashboard.php

✓ Upgrade page renders with login-style design
  └─ Verified: Uses .auth-page, .glass-auth-card classes

✓ Form accepts card details
  └─ Verified: <input> elements for card, expiry, CVV

✓ Form submission triggers loading state
  └─ Verified: JavaScript addEventListener + button.disabled

✓ Backend processes payment
  └─ Verified: process_upgrade.php creates records

✓ User marked as premium
  └─ Verified: is_premium = 1 in users table

✓ Success message appears
  └─ Verified: button text changes + color changes

✓ Redirect to dashboard
  └─ Verified: window.location.href = 'dashboard.php'

✓ Premium cards now unlocked
  └─ Verified: Dashboard shows ✨ Premium on cards

✓ Already-premium users can't access upgrade
  └─ Verified: isPremiumUser() check redirects
```

---

## 📊 System Integration Map

```
┌─────────────────────────────────────────────────────────┐
│           ÉtudeSync System (etudesync)                 │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────┐          ┌──────────────┐           │
│  │ login.php    │          │ dashboard.php│           │
│  │ (public)     │          │ (public)     │           │
│  └──────┬───────┘          └──────┬───────┘           │
│         │                         │                    │
│         │ (authenticated)         │ (free user)       │
│         │                         │                    │
│         └─────────────────┬───────┘                   │
│                           │ (click locked)            │
│                           ▼                           │
│                    ┌──────────────┐                   │
│                    │ upgrade.php  │                   │
│                    │ (NEW - public)                   │
│                    └──────┬───────┘                   │
│                           │                           │
│                           │ (submit form)             │
│                           ▼                           │
│         ┌─────────────────────────────────┐           │
│         │  /api/premium/                  │           │
│         │  process_upgrade.php            │           │
│         │  (NEW - backend)                │           │
│         └──────────┬──────────────────────┘           │
│                    │                                  │
│          ┌─────────┴────────┐                         │
│          │                  │                         │
│          ▼                  ▼                         │
│    ┌──────────┐      ┌────────────┐                  │
│    │ Database │      │ includes/  │                  │
│    │ Tables   │      │ premium_   │                  │
│    │          │      │ check.php  │                  │
│    │ -users   │      │ (helpers)  │                  │
│    │ -payment │      └────────────┘                  │
│    │ -subs    │                                      │
│    └──────────┘                                      │
│                                                      │
└─────────────────────────────────────────────────────┘
```

---

**These diagrams show the complete flow of the premium upgrade system from user interaction through database updates.**
