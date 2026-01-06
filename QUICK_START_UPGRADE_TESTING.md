# Quick Start: Testing the Premium Upgrade Page

## 🚀 5-Minute Setup

### Step 1: Ensure Database Tables Exist

Open phpMyAdmin or MySQL CLI and run:

```sql
SOURCE sql/subscription_schema.sql;
```

Or if you already created the schema, verify it exists:

```sql
SHOW TABLES LIKE '%subscription%';
```

Expected output:

```
subscription_plans
user_subscriptions
payment_orders
```

### Step 2: Check Pro Plan Exists

```sql
SELECT * FROM subscription_plans WHERE name = 'Pro Plan';
```

Expected: 1 row with plan_id, price 4.99, etc.

If not found, insert it:

```sql
INSERT INTO subscription_plans (name, description, price, billing_cycle, features, is_active)
VALUES (
  'Pro Plan',
  'Unlock all premium features',
  4.99,
  'monthly',
  '["QuizForge", "InfoVault", "Analytics"]',
  1
);
```

### Step 3: Create Test User (Optional)

If you don't have a test user:

```sql
INSERT INTO users (username, email, password, is_premium)
VALUES ('testuser', 'test@example.com', SHA2('password123', 256), 0);
```

### Step 4: Start Your Server

```bash
cd c:\xampp\htdocs\etudesync\etudesync
# If using XAMPP: ensure Apache + MySQL are running
# Or use: php -S localhost:8000
```

---

## ✅ Test Scenario 1: Basic Upgrade

### Steps

1. **Open browser:** `http://localhost/etudesync/public/login.php`
2. **Login** with a non-premium user
3. **Click dashboard** card link (should redirect to dashboard)
4. **Click locked card** (QuizForge 🔒 or InfoVault 🔒)
5. **Should see upgrade page** with:
   - Logo at top
   - "Unlock Premium" heading
   - Features list
   - Payment form
   - Blue button "Unlock Premium"
6. **Fill form:**
   - Name: `John Doe`
   - Card: `4111 1111 1111 1111`
   - Expiry: `12/25`
   - CVV: `123`
7. **Click "Unlock Premium"**
8. **See "Processing..."** (2-3 seconds)
9. **See "✓ Premium Activated!"** (button turns green)
10. **Auto-redirect** to dashboard
11. **Verify unlock:** QuizForge should now show ✨ Premium

### Expected Flow

```
Dashboard (locked cards)
  → Click QuizForge 🔒
  → /upgrade.php page
  → Fill form
  → Click button
  → Processing...
  → Success!
  → Redirect to dashboard
  → See QuizForge ✨ Premium
```

---

## ✅ Test Scenario 2: Already Premium User

### Setup

Mark test user as premium:

```sql
UPDATE users SET is_premium = 1 WHERE username = 'testuser';
```

### Steps

1. **Login** as premium user
2. **Dashboard shows:** Both QuizForge ✨ and InfoVault ✨ unlocked
3. **Try direct access:** Go to `http://localhost/etudesync/public/upgrade.php`
4. **Should redirect** automatically back to dashboard (because already premium)

### Expected Flow

```
/upgrade.php (while premium)
  → Redirect to /dashboard.php
```

---

## ✅ Test Scenario 3: Unauthenticated Access

### Steps

1. **Logout** (if logged in)
2. **Try direct access:** `http://localhost/etudesync/public/upgrade.php`
3. **Should redirect** to `/login.php`
4. **Login with credentials**
5. **Should redirect back** to `/upgrade.php`

### Expected Flow

```
/upgrade.php (while logged out)
  → Redirect to /login.php
  → After login
  → Redirect to /upgrade.php
```

---

## 🧪 Advanced Testing

### Test 4: Verify Database Changes

After successful upgrade, run:

```sql
-- Check user is marked premium
SELECT id, username, is_premium FROM users WHERE id = 1;
-- Should show: is_premium = 1

-- Check subscription created
SELECT * FROM user_subscriptions WHERE user_id = 1;
-- Should show: status = 'active'

-- Check payment order
SELECT * FROM payment_orders WHERE user_id = 1;
-- Should show: status = 'success'
```

### Test 5: Form Validation

Try submitting with incomplete form:

1. **Clear name field** and click submit
2. **Should not submit** (HTML5 validation)
3. **Fill name** and clear card number
4. **Should not submit** (HTML5 validation)

### Test 6: Mobile Responsive

1. **Open upgrade.php** in browser
2. **Press F12** to open DevTools
3. **Click "Toggle device toolbar"** (mobile icon)
4. **Test sizes:**
   - iPhone (375px) - should look good
   - iPad (768px) - should look good
   - Desktop (1920px) - should look good

---

## 🎯 Checklist: What Should Work

- [ ] Non-premium user sees locked cards
- [ ] Clicking locked card navigates to /upgrade.php
- [ ] Upgrade page matches login.php design
- [ ] Payment form accepts input
- [ ] Button shows loading state
- [ ] Form submits to backend
- [ ] User marked premium in database
- [ ] Subscription created in database
- [ ] Payment order recorded
- [ ] Success message appears
- [ ] Redirect to dashboard works
- [ ] Dashboard now shows unlocked cards
- [ ] Premium user can't access /upgrade.php
- [ ] Logged-out user redirects to login

---

## 🐛 Troubleshooting

### Problem: "404 Not Found" on /upgrade.php

**Solution:**

- Verify file exists: `c:\xampp\htdocs\etudesync\etudesync\public\upgrade.php`
- Check Apache is serving from correct directory
- Try full URL: `http://localhost/etudesync/public/upgrade.php`

### Problem: Locked cards don't link to upgrade.php

**Solution:**

- Open `public/dashboard.php`
- Check line ~90-100 has `href="upgrade.php"`
- If it shows `href="#"`, the file wasn't updated
- Save and refresh browser (clear cache: Ctrl+Shift+Delete)

### Problem: Payment form submits but nothing happens

**Solution:**

- Check browser console (F12 → Console)
- Look for JavaScript errors
- Verify `process_upgrade.php` is being called (Network tab)
- Check file permissions on `public/api/premium/process_upgrade.php`

### Problem: "User is already premium" error

**Solution:**

- This is expected if you're retesting with same user
- Reset the user: `UPDATE users SET is_premium = 0 WHERE id = 1;`
- Or create new test user

### Problem: Database error "Table doesn't exist"

**Solution:**

- Run: `SOURCE sql/subscription_schema.sql;`
- Verify: `SHOW TABLES LIKE '%subscription%';`
- If still missing, check MySQL error log

### Problem: "Redirect to dashboard" doesn't work

**Solution:**

- Check browser console for errors
- Verify JavaScript is enabled
- Check `dashboard.php` exists and is accessible
- Try manual redirect: `window.location.href = 'dashboard.php';`

---

## 📊 Database State After Upgrade

### Before Upgrade

```
users table:
  id | username | is_premium
  1  | john     | 0

subscription_plans table:
  id | name | price
  1  | Pro Plan | 4.99
```

### After Upgrade

```
users table:
  id | username | is_premium
  1  | john     | 1  ← Changed!

user_subscriptions table:
  id | user_id | plan_id | status
  1  | 1       | 1       | active  ← New!

payment_orders table:
  id | user_id | amount | status
  1  | 1       | 4.99   | success  ← New!
```

---

## ✨ Test User Credentials

For quick testing:

**Existing User (if available):**

```
Username: [your existing user]
Password: [your existing password]
```

**Create Test User:**

```sql
-- Create free user
INSERT INTO users (username, email, password)
VALUES ('premium_tester', 'tester@test.com', SHA2('test123', 256));

-- Later, upgrade them:
-- (Go through upgrade flow, or manually):
UPDATE users SET is_premium = 1 WHERE username = 'premium_tester';
```

---

## 🎉 Success Indicators

When everything works:

✅ Free user sees locked cards  
✅ Clicking locked card shows /upgrade.php  
✅ Payment form is visible and styled  
✅ Form submission works  
✅ Success message appears  
✅ Dashboard shows unlocked cards  
✅ Database records created  
✅ Can't re-upgrade (already premium)

**🎊 You're done! Premium upgrade system is working perfectly!**

---

## 📚 Next Steps

Once testing is complete:

1. **Test with real users** - Have friends try the flow
2. **Gather feedback** - Ask about UX/design
3. **Consider mobile** - Test on actual devices
4. **Plan migration** - If integrating real payment gateway:
   - Check out UPGRADE_PAGE_GUIDE.md "Migration Path" section
   - Research Stripe/PayPal integration
   - Plan implementation timeline

---

## 💡 Demo Payment Info

**This is DUMMY payment. No real charges occur.**

Test cards (works with any expiry/CVV):

- Visa: `4111 1111 1111 1111`
- Mastercard: `5555 5555 5555 4444`
- Amex: `3782 822463 10005`

---

**Questions?** Check:

- `UPGRADE_PAGE_GUIDE.md` - Full documentation
- `UPGRADE_IMPLEMENTATION_CHECKLIST.md` - Technical checklist
- `MODAL_VS_PAGE_DESIGN.md` - Design changes explanation
