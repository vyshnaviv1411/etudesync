# Pricing Update: USD → INR

## Changes Made

**Currency changed from USD to INR:**
- Old: $4.99/month
- New: ₹399/month

---

## Files Updated

### 1. **Frontend Display** (`public/upgrade.php`)
- Price display changed from `$4.99` to `₹399`
- All styling and formatting maintained

### 2. **Backend Processing** (`api/premium/process_upgrade.php`)
- Payment amount: `4.99` → `399`
- Currency code: `USD` → `INR`

### 3. **Database Seed** (`sql/subscription_schema.sql`)
- Plan price: `4.99` → `399.00`
- Updated seed data for `subscription_plans` table

---

## Database Update (Run this if plan already exists)

If you've already seeded the database with the old price, run this SQL to update:

```sql
USE etudesync;

-- Update existing Pro Plan to INR pricing
UPDATE subscription_plans
SET price = 399.00
WHERE name = 'Pro Plan';

-- Verify update
SELECT name, price, billing_cycle FROM subscription_plans WHERE name = 'Pro Plan';
```

**Expected result:**
```
name      | price  | billing_cycle
----------|--------|---------------
Pro Plan  | 399.00 | monthly
```

---

## Testing Checklist

After updating:

- [ ] Navigate to `/upgrade.php`
- [ ] Verify price shows **₹399** (not $4.99)
- [ ] Complete dummy payment
- [ ] Check `payment_orders` table - amount should be `399.00`, currency `INR`
- [ ] Verify premium unlocks correctly

---

## Currency Symbol

**INR Symbol:** ₹ (Rupee sign)
- Unicode: U+20B9
- HTML Entity: `&#8377;`
- Used in: `upgrade.php` line 59

---

## Future Real Gateway Integration

When integrating real payment gateway (Razorpay, PayU, etc.):

### Razorpay (Recommended for India)
```php
$order = $api->order->create([
    'amount' => 39900,  // Amount in paise (₹399 = 39900 paise)
    'currency' => 'INR',
    'receipt' => 'order_' . $user_id
]);
```

### PayU India
```php
$params = [
    'amount' => '399.00',
    'productinfo' => 'Pro Plan',
    'currency' => 'INR'
];
```

### Stripe (International)
```php
$intent = \Stripe\PaymentIntent::create([
    'amount' => 39900,  // Amount in paise
    'currency' => 'inr'
]);
```

---

## Notes

- All prices are monthly recurring
- Dummy payment accepts any card details
- Real gateway will validate payment method for INR
- Consider adding GST (18%) if required by law: ₹399 + ₹71.82 GST = ₹470.82

---

**Updated:** Jan 6, 2025
