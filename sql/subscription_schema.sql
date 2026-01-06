-- ============================================
-- PREMIUM SUBSCRIPTION TABLES
-- ============================================
USE etudesync;

-- Subscription Plans
CREATE TABLE IF NOT EXISTS subscription_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
  features JSON,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Subscriptions
CREATE TABLE IF NOT EXISTS user_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  plan_id INT NOT NULL,
  status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
  start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  end_date TIMESTAMP NULL,
  renewal_date TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_active_subscription (user_id, status),
  UNIQUE KEY unique_active_subscription (user_id, plan_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dummy Payment Orders
CREATE TABLE IF NOT EXISTS payment_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subscription_id INT,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  order_id VARCHAR(50) UNIQUE NOT NULL,
  payment_id VARCHAR(50) UNIQUE NOT NULL,
  status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
  payment_method VARCHAR(50) DEFAULT 'dummy_card',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id),
  INDEX idx_user (user_id),
  INDEX idx_order_id (order_id),
  INDEX idx_payment_id (payment_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Plan (Updated to INR pricing)
INSERT IGNORE INTO subscription_plans (name, description, price, billing_cycle, features, is_active)
VALUES (
  'Pro Plan',
  'Unlock all premium features for enhanced productivity',
  399.00,
  'monthly',
  '["QuizForge - Create and attempt unlimited quizzes", "InfoVault - Premium note storage and organization", "Advanced analytics and progress tracking", "Priority support", "Ad-free experience"]',
  1
);
