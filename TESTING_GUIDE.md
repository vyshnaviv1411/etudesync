# Premium Payment System - Testing Guide

## ✅ Step-by-Step Testing Procedure

### Phase 1: Verification (5 minutes)

#### 1.1 Run Setup Check

```
URL: http://localhost/etudesync/etudesync/public/premium_setup_check.php
```

Expected output:

```
✓ Database Tables - All subscription tables exist
✓ Subscription Plans - 1 active plan(s) found
✓ API: initiate_payment.php - File exists
✓ API: confirm_payment.php - File exists
✓ API: get_plans.php - File exists
✓ Assets: premium.css - File exists (xxx bytes)
✓ Assets: premium.js - File exists (xxx bytes)
✓ Helper: isPremiumUser() - Function available
✓ Helper: getUserSubscription() - Function available
✓ Helper: getAvailablePlans() - Function available
✓ Helper: requirePremium() - Function available
✓ Helper: getPremiumFeatures() - Function available
✓ Premium Functions - All functions execute without errors
```

If any checks fail → see **Troubleshooting** section below.

---

### Phase 2: User Flow Test (10 minutes)

#### 2.1 Access Dashboard

```
URL: http://localhost/etudesync/etudesync/public/dashboard.php
```

Expected state:

- [x] Logged in as admin@example.com
- [x] Dashboard shows 3 free cards (CollabSphere, FocusFlow, MindPlay)
- [x] Dashboard shows 2 locked cards:
  - QuizForge 🔒 Premium
  - InfoVault 🔒 Premium
- [x] Locked cards are **NOT** clickable links (href="#")

#### 2.2 Click Locked Card

```
Action: Click on "QuizForge 🔒 Premium" card
```

Expected behavior:

- [x] Modal slides in from center
- [x] Modal is centered on screen
- [x] Dark overlay appears behind modal
- [x] Modal shows "👑 Upgrade to Premium" header
- [x] Plan details display:
  - Name: "Pro Plan"
  - Price: "$4.99/month"
  - Features list with checkmarks
- [x] Payment form visible with fields:
  - Full Name (default: "Demo User")
  - Card Number
  - Expiry (MM/YY)
  - CVV
- [x] Demo info banner present
- [x] "Pay Now" button visible
- [x] "Cancel" button visible
- [x] Close button (×) visible

#### 2.3 Fill Payment Form

```
Action:
1. Clear Full Name field
2. Enter: "John Doe"
3. Enter Card: "4111111111111111"
4. Enter Expiry: "12/25"
5. Enter CVV: "123"
```

Expected behavior:

- [x] Fields accept input
- [x] No validation errors

#### 2.4 Submit Payment

```
Action: Click "Pay Now" button
```

Expected behavior (sequence):

- [x] Button becomes disabled
- [x] Form content hides
- [x] Processing spinner appears
- [x] "Processing payment..." text shows
- [x] Demo mode notice visible below spinner
- [x] 2-3 second wait (simulated processing)

#### 2.5 Success State

```
After processing completes
```

Expected behavior:

- [x] Spinner disappears
- [x] Success state appears
- [x] Large ✓ checkmark icon visible
- [x] "Payment Successful!" title shown
- [x] Success message text visible
- [x] Order ID displayed (format: ORD-xxxx-timestamp)
- [x] "Continue to Dashboard" button visible

#### 2.6 Reload Dashboard

```
Action: Click "Continue to Dashboard" button
```

Expected behavior:

- [x] Modal closes
- [x] Page reloads
- [x] Premium cards now show:
  - QuizForge ✨ Premium
  - InfoVault ✨ Premium
- [x] Lock badge changed to green ✨ badge
- [x] Premium cards are now clickable links

---

### Phase 3: Database Verification (5 minutes)

#### 3.1 Check Subscription Created

```sql
SELECT * FROM user_subscriptions WHERE user_id = 1;
```

Expected result:

```
id          1
user_id     1
plan_id     1
status      active
start_date  2024-01-05 14:30:00
end_date    2024-02-05 14:30:00
renewal_date NULL
```

#### 3.2 Check Payment Order Created

```sql
SELECT user_id, amount, order_id, status, created_at, completed_at
FROM payment_orders WHERE user_id = 1 ORDER BY created_at DESC LIMIT 1;
```

Expected result:

```
user_id      1
amount       4.99
order_id     ORD-xxx-1704556800
status       success
created_at   2024-01-05 14:30:00
completed_at 2024-01-05 14:32:00
```

#### 3.3 Check User Premium Flag

```sql
SELECT id, email, is_premium FROM users WHERE id = 1;
```

Expected result:

```
id          1
email       admin@example.com
is_premium  1
```

---

### Phase 4: API Testing (10 minutes)

#### 4.1 Test Get Plans Endpoint

```bash
curl -X GET http://localhost/etudesync/etudesync/public/api/premium/get_plans.php
```

Expected response:

```json
{
  "success": true,
  "plans": [
    {
      "id": 1,
      "name": "Pro Plan",
      "description": "Unlock all premium features...",
      "price": 4.99,
      "billing_cycle": "monthly",
      "features": "[\"QuizForge...\"]"
    }
  ]
}
```

#### 4.2 Test Initiate Payment Endpoint

```bash
curl -X POST http://localhost/etudesync/etudesync/public/api/premium/initiate_payment.php \
  -H "Content-Type: application/json" \
  -d '{"plan": "Pro Plan"}'
```

Expected response:

```json
{
  "success": true,
  "payment": {
    "order_id": "ORD-...",
    "payment_id": "PAY-...",
    "amount": 4.99,
    "plan": "Pro Plan",
    "currency": "USD",
    "subscription_id": X
  }
}
```

#### 4.3 Test Confirm Payment Endpoint

```bash
curl -X POST http://localhost/etudesync/etudesync/public/api/premium/confirm_payment.php \
  -H "Content-Type: application/json" \
  -d '{"order_id": "ORD-...", "payment_id": "PAY-..."}'
```

Expected response:

```json
{
  "success": true,
  "message": "Payment successful! Premium activated.",
  "subscription_id": X,
  "order_id": "ORD-...",
  "payment_id": "PAY-..."
}
```

---

### Phase 5: Edge Cases & Error Handling (10 minutes)

#### 5.1 User Already Has Premium

```
Setup: User is already premium (is_premium = 1)
Action: Click locked card again
Expected: Error message appears in modal
```

Try to trigger with:

```sql
UPDATE users SET is_premium = 1 WHERE id = 1;
```

Then click card → Error state should appear.

#### 5.2 Modal Close Mechanisms

Test all 3 close methods work:

- [x] Click × (close button)
- [x] Click "Cancel" button
- [x] Click dark overlay

Each should close modal and show form in initial state.

#### 5.3 Form Validation

Test invalid inputs:

- [x] Submit with empty Full Name → shows validation error
- [x] Submit with invalid card number → shows validation error
- [x] Submit with invalid expiry → shows validation error
- [x] Submit with empty CVV → shows validation error

#### 5.4 Multiple Payments

Test repeated payments:

- [x] After first successful payment, user is premium
- [x] Click locked card again
- [x] Modal opens but shows error (already has premium)
- [x] Error message is clear and helpful

---

### Phase 6: Browser Compatibility (5 minutes)

Test in:

- [x] Chrome (latest)
- [x] Firefox (latest)
- [x] Safari (if available)
- [x] Edge (if available)

Check:

- [x] Modal displays correctly
- [x] Animation is smooth
- [x] Form is responsive
- [x] Buttons are clickable
- [x] Success animation works

---

### Phase 7: Responsive Design (5 minutes)

#### Desktop (1920x1080)

- [x] Modal is 480px wide, centered
- [x] All text readable
- [x] Buttons at proper size

#### Tablet (768x1024)

- [x] Modal is 90vw width
- [x] Text still readable
- [x] Buttons properly sized

#### Mobile (375x667)

- [x] Modal full width (90vw)
- [x] Form elements stack properly
- [x] Buttons are full width
- [x] Can scroll if needed

---

## 🐛 Troubleshooting

### Setup Check Fails

#### Issue: Database Tables Not Found

```
Error: Database Tables - Missing: subscription_plans, ...
```

**Solution**:

```bash
# Run the schema file
mysql -u root etudesync < sql/subscription_schema.sql
```

#### Issue: API Files Not Found

```
Error: API: initiate_payment.php - File missing
```

**Solution**:

1. Check files exist in: `public/api/premium/`
2. Create directory if missing: `mkdir -p public/api/premium`
3. Ensure files have .php extension

#### Issue: Helper Functions Not Found

```
Error: Helper: isPremiumUser() - Function not found
```

**Solution**:

1. Check `includes/premium_check.php` exists
2. Check it's properly included in dashboard.php
3. Verify function names match exactly

---

### Modal Won't Open

#### Issue: Click Locked Card, Nothing Happens

**Debug**:

1. Open DevTools (F12) → Console
2. Type: `window.premiumModal`
3. Should show PremiumPaymentModal instance

If undefined:

- Check `premium.js` loaded (Network tab)
- Check no JavaScript errors (Console tab)
- Check `includes/footer.php` includes premium.js

#### Issue: Modal Opens But Empty

**Debug**:

1. Check Network tab → look for API calls to `/api/premium/get_plans.php`
2. If missing → premium.js not loading plan data
3. Check API endpoint URL is correct

---

### Payment Doesn't Process

#### Issue: Spinner Shows Then Nothing

**Debug**:

1. Open DevTools → Network tab
2. Click "Pay Now"
3. Should see two POST requests:
   - `/api/premium/initiate_payment.php`
   - `/api/premium/confirm_payment.php`
4. Check response status (should be 200)
5. Check response JSON (should have "success": true)

#### Issue: Form Validation Error

**Solution**:

- Ensure all fields are filled
- Card number should be 16 digits
- Expiry format: MM/YY (e.g., 12/25)
- CVV should be 3 digits

---

### Premium Cards Don't Unlock

#### Issue: After Payment, Cards Still Show 🔒

**Solution**:

1. Manually refresh page (Ctrl+F5 hard refresh)
2. Check database:

   ```sql
   SELECT is_premium FROM users WHERE id = 1;
   ```

   Should be 1, not 0

3. If still 0, check payment_orders:

   ```sql
   SELECT status FROM payment_orders WHERE user_id = 1 ORDER BY id DESC LIMIT 1;
   ```

   Should be 'success', not 'pending'

4. If 'pending', check confirm_payment.php error logs

---

### Form Not Submitting

#### Issue: "Pay Now" Button Doesn't Work

**Debug**:

1. Check form validity in DevTools Console:

   ```javascript
   document.getElementById("paymentForm").checkValidity();
   ```

   Returns true/false

2. If false, find which field is invalid:
   ```javascript
   document.getElementById("paymentForm").reportValidity();
   ```

---

### JavaScript Errors

#### Issue: Console Shows Errors

**Common errors**:

```
Uncaught SyntaxError: Unexpected token
```

→ Check premium.js syntax (check line numbers in error)

```
Cannot read property 'classList' of null
```

→ DOM elements don't exist yet. Check modal HTML is in page.

```
Fetch failed (CORS)
```

→ API endpoints might be returning 404. Check URL path.

---

## 📋 Manual Test Checklist

```
PRE-TEST SETUP
☐ Database is running (MySQL)
☐ Apache/PHP is running (XAMPP)
☐ Logged in as admin@example.com
☐ Browser DevTools open (F12)

VERIFICATION PHASE
☐ Setup check page loads: premium_setup_check.php
☐ All checks show ✓ (green)

USER FLOW PHASE
☐ Dashboard loads with 2 locked cards
☐ Click locked card → modal opens
☐ Modal shows plan details
☐ Form fields are editable
☐ Fill form with dummy data
☐ Click "Pay Now" → spinner shows
☐ After 2-3 sec → success state
☐ Click "Continue" → page reloads
☐ Premium cards now show ✨ badges
☐ Premium cards are clickable links

DATABASE PHASE
☐ user_subscriptions has 1 record
☐ payment_orders has 1 record with status='success'
☐ users.is_premium = 1

API PHASE
☐ GET /api/premium/get_plans.php → returns plans
☐ POST /api/premium/initiate_payment.php → returns payment details
☐ POST /api/premium/confirm_payment.php → returns success

EDGE CASES
☐ Already premium error message works
☐ Modal close button works
☐ Cancel button works
☐ Overlay click closes modal
☐ Form validates before submit
☐ Repeated payment blocked

RESPONSIVE
☐ Desktop layout correct
☐ Tablet layout correct
☐ Mobile layout correct
☐ All text readable
☐ Buttons accessible

BROWSER COMPAT
☐ Chrome works
☐ Firefox works
☐ Safari works
☐ Edge works
```

---

## 📊 Performance Metrics (Baseline)

Expected performance:

- Modal open time: <100ms
- Form submit time: 2-3 seconds (intentional)
- Page reload time: <1 second
- Database query time: <50ms (per query)

To measure:

```javascript
// In Console
performance.measure("payment-flow");
performance.getEntriesByType("measure");
```

---

## 🔍 Debugging Commands

### Check If Session Exists

```javascript
// In Console
console.log(document.cookie);
```

### Check If Modal JS Loaded

```javascript
console.log(window.premiumModal);
```

### Trigger Payment Manually

```javascript
// Call the payment function directly
window.premiumModal.processPayment();
```

### Check Database Connection

```bash
mysql -u root -e "SELECT 1;"
```

### View Recent Payments

```sql
SELECT * FROM payment_orders ORDER BY created_at DESC LIMIT 5;
```

### Clear Premium Status (Reset for Testing)

```sql
UPDATE users SET is_premium = 0 WHERE id = 1;
DELETE FROM user_subscriptions WHERE user_id = 1;
DELETE FROM payment_orders WHERE user_id = 1;
```

---

## ✅ Sign-Off Checklist

Once all tests pass, check:

- [x] No JavaScript errors
- [x] No PHP errors (check server logs)
- [x] No database errors (check error logs)
- [x] Modal animation smooth
- [x] Success animation displays correctly
- [x] Database records created correctly
- [x] Premium features unlock correctly
- [x] All browsers tested
- [x] Mobile layout correct
- [x] Edge cases handled

**Status**: Ready for production ✅
