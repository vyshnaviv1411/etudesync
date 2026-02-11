-- ===============================
-- MOOD TRACKER
-- ===============================

CREATE TABLE mood_tracker (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  mood_date DATE NOT NULL,
  mood_value VARCHAR(20) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  UNIQUE KEY unique_user_date (user_id, mood_date),
  KEY idx_user_date (user_id, mood_date),
  CONSTRAINT mood_tracker_ibfk_1
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ===============================
-- POMODORO SESSIONS
-- ===============================

CREATE TABLE pomodoro_sessions (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  duration_minutes INT(11) NOT NULL,
  completed TINYINT(1) DEFAULT 0,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  completed_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_user_completed (user_id, completed),
  KEY idx_completed_at (completed_at),
  CONSTRAINT pomodoro_sessions_ibfk_1
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- ===============================
-- STUDY PLANS
-- ===============================

CREATE TABLE study_plans (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  day_of_week TINYINT(4) NOT NULL COMMENT '0=Sunday, 6=Saturday',
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  subject VARCHAR(100) DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY idx_user_day (user_id, day_of_week),
  CONSTRAINT study_plans_ibfk_1
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- ===============================
-- TODOS
-- ===============================

CREATE TABLE todos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  priority ENUM('low','medium','high') DEFAULT 'medium',
  status ENUM('pending','in_progress','completed') DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  completed_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_user_status (user_id, status),
  KEY idx_due_date (due_date),
  CONSTRAINT todos_ibfk_1
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
