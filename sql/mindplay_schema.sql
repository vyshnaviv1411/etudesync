CREATE TABLE IF NOT EXISTS mood_tracker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mood_date DATE NOT NULL,
    mood_value VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_date (user_id, mood_date),
    INDEX idx_user_date (user_id, mood_date),
    CONSTRAINT fk_mood_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry_date DATE NOT NULL,
    content TEXT,
    theme_color VARCHAR(20) DEFAULT 'default',
    is_submitted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_entry_date (user_id, entry_date),
    INDEX idx_user_date (user_id, entry_date),
    CONSTRAINT fk_journal_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type VARCHAR(30) NOT NULL,
    session_date DATE NOT NULL,
    session_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score INT NOT NULL DEFAULT 0,
    duration INT NOT NULL DEFAULT 0,
    metadata JSON,
    INDEX idx_user_game_date (user_id, game_type, session_date),
    INDEX idx_user_game (user_id, game_type),
    CONSTRAINT fk_game_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS game_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type VARCHAR(30) NOT NULL,
    total_plays INT DEFAULT 0,
    best_score INT DEFAULT 0,
    total_wins INT DEFAULT 0,
    total_losses INT DEFAULT 0,
    total_draws INT DEFAULT 0,
    current_streak INT DEFAULT 0,
    best_streak INT DEFAULT 0,
    additional_stats JSON,
    last_played TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_game (user_id, game_type),
    INDEX idx_user_game_stats (user_id, game_type),
    CONSTRAINT fk_game_stats_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE INDEX idx_mood_date_range
    ON mood_tracker(user_id, mood_date DESC);

CREATE INDEX idx_journal_date_range
    ON journal_entries(user_id, entry_date DESC);

CREATE INDEX idx_game_session_date_range
    ON game_sessions(user_id, session_date DESC, game_type);

CREATE INDEX idx_game_stats_leaderboard
    ON game_statistics(game_type, best_score DESC);
