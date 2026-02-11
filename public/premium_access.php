<?php
// public/premium_access.php
session_start();
require_once __DIR__ . '/../includes/db.php';
$disable_dashboard_bg = true;
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Student';
$user_email = $_SESSION['user_email'] ?? '';
$premium_message = $_SESSION['premium_message'] ?? 'Unlock premium features to access AccessArena and InfoVault.';
unset($_SESSION['premium_message']); // Clear message after reading

$page_title = 'Premium Checkout';

// Include global header
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<div class="premium-bg"></div>
<!-- Mark body for dashboard page styling -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.body.classList.add('dashboard-page');
});
</script>

<!-- Premium Payment Page Styles -->
<style>
    /* Premium Page Background */
.premium-bg {
    position: fixed;   /* 🔥 MUST BE FIXED */
    inset: 0;
    z-index: -1;

    background-image: url('assets/images/infovault_bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    pointer-events: none;
}


/* Soft vignette (edges darker, center clear) */
.premium-bg::before {
    content: '';
    position: absolute;
    inset: 0;
}

.premium-bg::after {
    backdrop-filter: blur(0.7px) brightness(0.5) saturate(1.05);
}


/* Light blur + brightness so image looks natural */

    /* Premium Checkout Page - Glassmorphism Payment Gateway */
    :root {
        --accent1: #7c4dff;
        --accent2: #47d7d3;
        --glass-bg: rgba(255, 255, 255, 0.12);
        --glass-border: rgba(255, 255, 255, 0.28);
        --input-bg: rgba(255, 255, 255, 0.08);
        --input-border: rgba(255, 255, 255, 0.2);
    }
    

    /* Override main-content for centered layout */
    .premium-payment-wrapper {
        min-height: calc(100vh - 180px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    /* Main Payment Container */
    .payment-container {
        width: 100%;
        max-width: 1100px;   /* restores premium width */
    gap: 32px;
         transform: scale(1.02);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        z-index: 10;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Glassmorphic Card */
   .glass-card {
    padding: 40px;

    background: rgba(255, 255, 255, 0.16);

    backdrop-filter: blur(26px) saturate(170%);
    -webkit-backdrop-filter: blur(26px) saturate(170%);

    border: 1px solid rgba(255, 255, 255, 0.35);

    box-shadow:
        0 35px 90px rgba(0, 0, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.45);

    border-radius: 26px;
}




 .glass-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        130deg,
        rgba(255,255,255,0.35),
        rgba(255,255,255,0.12) 40%,
        rgba(255,255,255,0.04) 65%
    );
    pointer-events: none;
}



    /* Order Summary Section */
    .order-summary {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--accent1), var(--accent2));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .summary-header h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .plan-details {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .plan-name {
        font-family: 'Poppins', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 8px;
        background: linear-gradient(90deg, var(--accent1), var(--accent2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .plan-price {
        font-size: 2.5rem;
        font-weight: 800;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 4px;
    }

    .plan-price span {
        font-size: 1rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.7);
    }

    .plan-billing {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.875rem;
        margin-bottom: 20px;
    }

    .plan-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .plan-features li {
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9375rem;
    }

    .plan-features li:last-child {
        border-bottom: none;
    }

    .plan-features li::before {
        content: '✓';
        color: var(--accent2);
        font-weight: 700;
        font-size: 1.125rem;
    }

    .price-breakdown {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 0.9375rem;
    }

    .price-row.total {
        padding-top: 12px;
        margin-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        font-weight: 700;
        font-size: 1.125rem;
    }

    /* Payment Form Section */
    .payment-form {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .form-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .lock-icon {
        width: 40px;
        height: 40px;
        background: rgba(124, 77, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .form-header h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .form-header p {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.7);
        margin: 4px 0 0 0;
    }

    /* Payment Method Selector */
    .payment-methods {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .payment-method {
        position: relative;
    }

    .payment-method input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 0.9375rem;
    }

    .payment-method input:checked + label {
        background: rgba(124, 77, 255, 0.15);
        border-color: var(--accent1);
        box-shadow: 0 0 0 3px rgba(124, 77, 255, 0.2);
    }

    .payment-method label:hover {
        border-color: rgba(255, 255, 255, 0.3);
    }

    /* Form Fields */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 0.875rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
    }

    .form-input {
        width: 100%;
        padding: 14px 16px;
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        border-radius: 10px;
        color: #fff;
        font-size: 0.9375rem;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--accent1);
        background: rgba(255, 255, 255, 0.12);
        box-shadow: 0 0 0 3px rgba(124, 77, 255, 0.15);
    }

    .form-input::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    /* Card Input Styling */
    #card-number {
        letter-spacing: 2px;
    }

    /* UPI Fields (hidden by default) */
    .upi-fields {
        display: none;
    }

    .upi-fields.active {
        display: block;
    }

    /* Submit Button */
    .submit-button {
        width: 100%;
        padding: 16px;
        background: linear-gradient(90deg, var(--accent1), var(--accent2));
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 1rem;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 8px 20px rgba(124, 77, 255, 0.3);
        margin-top: 8px;
    }

    .submit-button:hover {
        box-shadow: 0 12px 30px rgba(124, 77, 255, 0.4);
        transform: translateY(-2px);
    }

    .submit-button:active {
        transform: translateY(0);
    }

    /* Back Link */
    .back-link {
        text-align: center;
        margin-top: 16px;
    }

    .back-link a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 0.9375rem;
        transition: color 0.2s ease;
    }

    .back-link a:hover {
        color: white;
    }

    /* Security Badge */
    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        background: rgba(71, 215, 211, 0.1);
        border-radius: 10px;
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.8);
    }

    /* Error Toast */
    .error-toast {
        position: fixed;
        top: 24px;
        right: 24px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(238, 90, 82, 0.4);
        z-index: 10000;
        font-weight: 600;
        max-width: 400px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    .error-toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .payment-container {
            grid-template-columns: 1fr;
        }

        .glass-card {
            padding: 24px;
        }

        .plan-price {
            font-size: 2rem;
        }

        .payment-methods {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 560px) {
        .premium-payment-wrapper {
            padding: 20px 12px;
        }

        .glass-card {
            padding: 20px;
        }

        .summary-header h2,
        .form-header h2 {
            font-size: 1.125rem;
        }

        .plan-price {
            font-size: 1.75rem;
        }
    }
</style>

<!-- Premium Payment Content -->
<div class="premium-payment-wrapper">
    <div class="payment-container">
        <!-- Order Summary -->
        <div class="glass-card order-summary">
            <div class="summary-header">
                <div class="summary-icon">📦</div>
                <div>
                    <h2>Order Summary</h2>
                </div>
            </div>

            <div class="plan-details">
                <div class="plan-name">ÉtudeSync Premium</div>
                <div class="plan-price">₹299 <span>/ month</span></div>
                <div class="plan-billing">Billed monthly • Cancel anytime</div>

                <ul class="plan-features">
                    <li>AssessArena - Unlimited quiz creation</li>
                    <li>InfoVault - Premium note storage</li>
                    <li>Advanced analytics & insights</li>
                    <li>Priority support</li>
                    <li>Ad-free experience</li>
                    <li>Early access to new features</li>
                </ul>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Subscription</span>
                        <span>₹299</span>
                    </div>
                    <div class="price-row">
                        <span>GST (18%)</span>
                        <span>₹100</span>
                    </div>
                    <div class="price-row total">
                        <span>Total</span>
                        <span>₹399</span>
                    </div>
                </div>
            </div>

            <div class="security-badge">
                <span>🔒</span>
                <span>Secured by 256-bit SSL encryption</span>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="glass-card payment-form">
            <div class="form-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="lock-icon">🔐</div>
                        <h2>Secure Checkout</h2>
                    </div>
                    <p>Complete payment to unlock premium features</p>
                </div>
            </div>

<form id="payment-form" onsubmit="handlePayment(event)">
                <!-- Payment Method Selection -->
                <div class="payment-methods">
                    <div class="payment-method">
                        <input type="radio" id="card-method" name="payment_method" value="card" checked>
                        <label for="card-method">
                            💳 Card
                        </label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="upi-method" name="payment_method" value="upi" >
                        <label for="upi-method">
                            📱 UPI
                        </label>
                    </div>
                </div>

                <!-- Card Payment Fields -->
                <div class="card-fields">
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text"
                               id="card-number"
                               class="form-input"
                               placeholder="1234 5678 9012 3456"
                               maxlength="19"
                               autocomplete="cc-number">
                    </div>

                    <div class="form-group">
                        <label for="card-name">Cardholder Name</label>
                        <input type="text"
                               id="card-name"
                               class="form-input"
                               placeholder="Name on card"
                               autocomplete="cc-name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="card-expiry">Expiry Date</label>
                            <input type="text"
                                   id="card-expiry"
                                   class="form-input"
                                   placeholder="MM/YY"
                                   maxlength="5"
                                   autocomplete="cc-exp">
                        </div>
                        <div class="form-group">
                            <label for="card-cvv">CVV</label>
                            <input type="text"
                                   id="card-cvv"
                                   class="form-input"
                                   placeholder="123"
                                   maxlength="3"
                                   autocomplete="cc-csc">
                        </div>
                    </div>
                </div>

                <!-- UPI Payment Fields -->
                <div class="upi-fields">
                    <div class="form-group">
                        <label for="upi-id">UPI ID</label>
                        <input type="text"
                               id="upi-id"
                               class="form-input"
                               placeholder="yourname@upi">
                    </div>
                </div>

                <!-- Submit Button -->
               <button type="submit" class="submit-button" id="pay-btn" >
    Pay ₹399 & Unlock Premium
</button>


                <div class="back-link">
                    <a href="dashboard.php">← Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Error Toast (Hidden) -->
<div id="error-toast" class="error-toast"></div>

<script>
    // Payment method toggle
    const cardMethod = document.getElementById('card-method');
    const upiMethod = document.getElementById('upi-method');
    const cardFields = document.querySelector('.card-fields');
    const upiFields = document.querySelector('.upi-fields');

    cardMethod.addEventListener('change', () => {
        cardFields.style.display = 'block';
        upiFields.classList.remove('active');
    });

    upiMethod.addEventListener('change', () => {
        cardFields.style.display = 'none';
        upiFields.classList.add('active');
    });

    // Card number formatting
    const cardNumberInput = document.getElementById('card-number');
    cardNumberInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });

    // Expiry date formatting
    const expiryInput = document.getElementById('card-expiry');
    expiryInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
    });

    // CVV numeric only
    const cvvInput = document.getElementById('card-cvv');
    cvvInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Handle Payment Submission (Dummy - Always Fails)
function handlePayment(event) {
    event.preventDefault();

    const method = document.querySelector('input[name="payment_method"]:checked').value;

    const number = document.getElementById('card-number').value.replace(/\s/g, '');
    const name   = document.getElementById('card-name').value.trim();
    const expiry = document.getElementById('card-expiry').value.trim();
    const cvv    = document.getElementById('card-cvv').value.trim();
    const upi    = document.getElementById('upi-id').value.trim();

    // ======================
    // CARD VALIDATION
    // ======================
    if (method === "card") {

        if (!number || !name || !expiry || !cvv) {
            alert("Please enter all card details before clicking Pay.");
            return;
        }

        let errors = [];

        if (!/^\d{16}$/.test(number)) {
            errors.push("Card number must be exactly 16 digits");
        }

        if (name.length < 3) {
            errors.push("Cardholder name must be at least 3 characters");
        }

        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            errors.push("Expiry format must be MM/YY");
        } else {
            const [month] = expiry.split("/");
            const monthNum = parseInt(month);
            if (monthNum < 1 || monthNum > 12) {
                errors.push("Expiry month must be between 01 and 12");
            }
        }

        if (!/^\d{3}$/.test(cvv)) {
            errors.push("CVV must be exactly 3 digits");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }
    }

    // ======================
    // UPI VALIDATION
    // ======================
    if (method === "upi") {

        if (!upi) {
            alert("Please enter your UPI ID before clicking Pay.");
            return;
        }

        const upiRegex = /^[a-zA-Z0-9._-]{2,256}@[a-zA-Z]{2,64}$/;

        if (!upiRegex.test(upi)) {
            alert("Invalid UPI ID (example: name@upi)");
            return;
        }
    }

    // ======================
    // SEND DATA TO SERVER
    // ======================
    const formData = new FormData();
    formData.append("payment_method", method);

    if (method === "card") {
        formData.append("cardNumber", number);
        formData.append("cardName", name);
        formData.append("cardExpiry", expiry);
        formData.append("cardCVV", cvv);
    } else {
        formData.append("upiId", upi);
    }

    fetch('api/premium/process_upgrade.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("🎉 Premium Activated Successfully!");
            window.location.href = "dashboard.php";
        } else {
            alert(data.error || "Payment failed");
        }
    })
    .catch(() => {
        alert("Server error. Try again.");
    });
}








</script>

<?php
// Include global footer
require_once __DIR__ . '/../includes/footer.php';
?>
