<?php
/**
 * Premium Upgrade Page
 * public/upgrade.php
 *
 * Full-page payment screen styled EXACTLY like the login page
 * Users land here when clicking a premium feature
 */

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/premium_check.php';

// If already premium, redirect to dashboard
if (isPremiumUser($_SESSION['user_id'] ?? 0)) {
    header('Location: dashboard.php');
    exit;
}

// If not logged in, redirect to login
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login_redirect'] = 'upgrade.php';
    header('Location: login.php');
    exit;
}

$page_title = 'Upgrade to Premium';
require_once __DIR__ . '/../includes/header_public.php';
?>

<!-- Compact Card Styles for Upgrade Page -->
<style>
.upgrade-page .glass-auth-card {
  max-height: calc(100vh - 120px) !important;
  overflow-y: auto !important;
  padding: 18px 24px !important; /* Reduced from default */
}

.upgrade-page .glass-auth-card h2 {
  margin: 4px 0 10px !important; /* Tighter spacing */
  font-size: 1.4rem !important; /* Slightly smaller */
}

.upgrade-page .logo-center {
  width: 48px !important;
  height: 48px !important;
  margin-bottom: 6px !important; /* Reduced */
}

.upgrade-page .auth-form {
  gap: 10px !important; /* Override default spacing */
}

.upgrade-page .input-group {
  margin-bottom: 0 !important; /* Remove bottom margin, use gap instead */
}

.upgrade-page .meta {
  margin-top: 12px !important; /* Reduced from default */
  font-size: 13px !important;
}

/* Smooth scrolling */
.upgrade-page .glass-auth-card::-webkit-scrollbar {
  width: 6px;
}

.upgrade-page .glass-auth-card::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.02);
  border-radius: 10px;
}

.upgrade-page .glass-auth-card::-webkit-scrollbar-thumb {
  background: rgba(124,77,255,0.3);
  border-radius: 10px;
}

.upgrade-page .glass-auth-card::-webkit-scrollbar-thumb:hover {
  background: rgba(124,77,255,0.5);
}
</style>

<div class="auth-page upgrade-page">
  <div class="auth-wrap">
    <div class="glass-auth-card">

      <!-- Logo (same as login) -->
      <img src="assets/images/logo.jpg" alt="ÉtudeSync Premium" class="logo-center" loading="lazy" />

      <!-- Title (same h2 style as login) -->
      <h2>Unlock Premium</h2>

      <!-- Flash messages (same pattern as login) -->
      <?php
      if (!empty($_SESSION['error'])) {
          echo '<div class="form-error flash-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
          unset($_SESSION['error']);
      } elseif (!empty($_SESSION['success'])) {
          echo '<div class="form-ok flash-message">' . htmlspecialchars($_SESSION['success']) . '</div>';
          unset($_SESSION['success']);
      }
      ?>

      <!-- Payment Form (same structure as login form) -->
      <form id="upgradeForm" method="POST" action="api/premium/process_upgrade.php" class="auth-form" novalidate style="gap: 10px;">

        <!-- Price Display (REDUCED PADDING) -->
        <div class="input-group" style="display: block; text-align: center; margin-bottom: 12px; padding: 12px 14px; background: rgba(124,77,255,0.08); border-radius: 10px; border: 1px solid rgba(124,77,255,0.15);">
          <div style="font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 2px;">₹399</div>
          <div style="color: rgba(255,255,255,0.75); font-size: 13px;">per month</div>
        </div>

        <!-- Features List (COMPRESSED) -->
        <div style="margin-bottom: 14px; padding: 10px 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.06);">
          <div style="font-size: 10px; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-bottom: 6px; font-weight: 700; letter-spacing: 0.8px;">What you get:</div>
          <div style="font-size: 12px; color: rgba(255,255,255,0.8); line-height: 1.5;">
            ✓ QuizForge - Unlimited quizzes<br>
            ✓ InfoVault - Premium storage<br>
            ✓ Advanced analytics<br>
            ✓ Priority support<br>
            ✓ Ad-free experience
          </div>
        </div>

        <!-- Card Name (same input-group as login) -->
        <div class="input-group">
          <label for="cardName">Full Name</label>
          <input
            id="cardName"
            type="text"
            name="cardName"
            required
            placeholder="John Doe"
            value="<?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES) ?>"
          />
        </div>

        <!-- Card Number -->
        <div class="input-group">
          <label for="cardNumber">Card Number (Any 16 digits)</label>
          <input
            id="cardNumber"
            type="text"
            name="cardNumber"
            required
            maxlength="19"
            placeholder="4111 1111 1111 1111"
          />
        </div>

        <!-- Expiry & CVV (side by side) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="input-group">
            <label for="cardExpiry">Expiry</label>
            <input
              id="cardExpiry"
              type="text"
              name="cardExpiry"
              required
              maxlength="5"
              placeholder="12/25"
            />
          </div>
          <div class="input-group">
            <label for="cardCVV">CVV</label>
            <input
              id="cardCVV"
              type="text"
              name="cardCVV"
              required
              maxlength="3"
              placeholder="123"
            />
          </div>
        </div>

        <!-- Demo Notice (COMPRESSED) -->
        <div style="margin-top: 12px; padding: 8px 10px; background: rgba(71,215,211,0.08); border-radius: 8px; border-left: 3px solid var(--accent2); font-size: 11px; color: rgba(255,255,255,0.7); line-height: 1.4;">
          <strong>💡 Demo Mode:</strong> This is a dummy payment gateway. No real charges will be made.
        </div>

        <!-- Submit Button (same class as login) -->
        <button type="submit" class="btn btn-login" id="upgradeBtn">
          Unlock Premium
        </button>
      </form>

      <!-- Back Link (same meta style as login) -->
      <div class="meta">
        <a href="dashboard.php">← Back to Dashboard</a>
      </div>

    </div>
  </div>
</div>

<!-- Payment Processing Script -->
<script>
(function() {
  const form = document.getElementById('upgradeForm');
  const btn = document.getElementById('upgradeBtn');

  // Simple card number formatting
  const cardInput = document.getElementById('cardNumber');
  cardInput.addEventListener('input', function(e) {
    let val = e.target.value.replace(/\s/g, '');
    let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
    e.target.value = formatted;
  });

  // Expiry formatting (MM/YY)
  const expiryInput = document.getElementById('cardExpiry');
  expiryInput.addEventListener('input', function(e) {
    let val = e.target.value.replace(/\D/g, '');
    if (val.length >= 2) {
      val = val.slice(0,2) + '/' + val.slice(2,4);
    }
    e.target.value = val;
  });

  // CVV - numbers only
  const cvvInput = document.getElementById('cardCVV');
  cvvInput.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
  });

  // Form submission with dummy payment simulation
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const originalText = btn.textContent;
    const originalBg = btn.style.background;

    // Show loading state (same as login)
    btn.disabled = true;
    btn.textContent = 'Processing Payment...';
    btn.style.opacity = '0.7';

    try {
      // Simulate realistic payment processing delay (2-3 seconds)
      await new Promise(resolve => setTimeout(resolve, 2000 + Math.random() * 1000));

      // Submit to backend
      const formData = new FormData(form);
      const response = await fetch('api/premium/process_upgrade.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        // Show success state
        btn.textContent = '✓ Premium Activated!';
        btn.style.background = 'linear-gradient(90deg, #10B981, #059669)';
        btn.style.opacity = '1';

        // Redirect to dashboard after brief delay
        setTimeout(() => {
          window.location.href = 'dashboard.php';
        }, 1200);
      } else {
        // Show error
        throw new Error(data.error || 'Payment failed');
      }
    } catch (error) {
      console.error('Payment error:', error);

      // Show error in flash message area
      const errorDiv = document.createElement('div');
      errorDiv.className = 'form-error flash-message';
      errorDiv.textContent = error.message || 'Network error. Please try again.';
      form.insertBefore(errorDiv, form.firstChild);

      // Reset button
      btn.disabled = false;
      btn.textContent = originalText;
      btn.style.background = originalBg;
      btn.style.opacity = '1';

      // Remove error after 5 seconds
      setTimeout(() => errorDiv.remove(), 5000);
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
