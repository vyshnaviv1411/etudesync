-- MindPlay Feature Database Schema
-- User well-being & productivity module with 4 sub-features:
-- 1. Mood Tracker (daily emoji mood selection)
-- 2. Journal (daily reflection with auto-save)
-- 3. Games (5 productivity games: Sudoku, XO, Memory Match, Quick Math, Word Unscramble)
-- 4. Reports (insights dashboard)

-- =====================================================
-- 1. MOOD TRACKER
-- =====================================================
-- Stores daily mood entries (one per user per day)
CREATE TABLE IF NOT EXISTS mood_tracker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mood_date DATE NOT NULL,
    -- Mood values: happy, sad, neutral, excited, anxious, calm, energetic, tired
    mood_value VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY unique_user_date (user_id, mood_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, mood_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. JOURNAL
-- =====================================================
-- Stores daily journal entries with auto-save functionality
CREATE TABLE IF NOT EXISTS journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry_date DATE NOT NULL,
    content TEXT,
    -- Theme/color choice for journal (nullable - uses default if not set)
    theme_color VARCHAR(20) DEFAULT 'default',
    -- Submitted flag: true = locked for day, false = still editable
    is_submitted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY unique_user_entry_date (user_id, entry_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, entry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. GAME SESSIONS
-- =====================================================
-- Stores individual game play sessions for all games
CREATE TABLE IF NOT EXISTS game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    -- Game types: sudoku, xo, memory_match, quick_math, word_unscramble
    game_type VARCHAR(30) NOT NULL,
    session_date DATE NOT NULL,
    session_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Score/result (interpretation varies by game)
    -- Sudoku: completion time in seconds (or 0 if incomplete)
    -- XO: 1=win, 0=loss, -1=draw
    -- Memory Match: number of attempts
    -- Quick Math: number of correct answers
    -- Word Unscramble: number of words solved
    score INT NOT NULL DEFAULT 0,

    -- Duration in seconds (time taken to complete game)
    duration INT NOT NULL DEFAULT 0,

    -- Additional game-specific data stored as JSON
    -- Sudoku: {"difficulty": "easy|medium|hard", "completed": true|false}
    -- XO: {"result": "win|loss|draw", "moves": 5}
    -- Memory Match: {"grid_size": "4x4|6x6", "best_time": 45}
    -- Quick Math: {"total_questions": 10, "correct": 8, "accuracy": 80}
    -- Word Unscramble: {"difficulty": "easy|medium|hard", "words_solved": 5, "total_words": 10}
    metadata JSON,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_game_date (user_id, game_type, session_date),
    INDEX idx_user_game (user_id, game_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. GAME STATISTICS
-- =====================================================
-- Aggregated statistics per user per game type (for quick reporting)
CREATE TABLE IF NOT EXISTS game_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type VARCHAR(30) NOT NULL,

    -- Total plays across all time
    total_plays INT DEFAULT 0,

    -- Best scores
    -- Sudoku: best completion time in seconds
    -- XO: win streak
    -- Memory Match: fewest attempts
    -- Quick Math: highest accuracy percentage
    -- Word Unscramble: most words solved in one session
    best_score INT DEFAULT 0,

    -- Win/Loss tracking (primarily for XO)
    total_wins INT DEFAULT 0,
    total_losses INT DEFAULT 0,
    total_draws INT DEFAULT 0,
    current_streak INT DEFAULT 0,
    best_streak INT DEFAULT 0,

    -- Additional stats as JSON
    -- Sudoku: {"easy_completions": 5, "medium_completions": 3, "hard_completions": 1}
    -- Memory Match: {"avg_attempts": 12, "fastest_time": 30}
    -- Quick Math: {"total_correct": 150, "total_questions": 200, "avg_accuracy": 75}
    -- Word Unscramble: {"total_words_solved": 45, "avg_time_per_word": 8}
    additional_stats JSON,

    last_played TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY unique_user_game (user_id, game_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_game_stats (user_id, game_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================
-- Additional indexes for common queries

-- Fast lookup for today's mood/journal
-- (Already covered by unique indexes above)

-- Fast lookup for date range queries (reports)
CREATE INDEX idx_mood_date_range ON mood_tracker(user_id, mood_date DESC);
CREATE INDEX idx_journal_date_range ON journal_entries(user_id, entry_date DESC);
CREATE INDEX idx_game_session_date_range ON game_sessions(user_id, session_date DESC, game_type);

-- Fast lookup for leaderboards (best scores across users)
CREATE INDEX idx_game_stats_leaderboard ON game_statistics(game_type, best_score DESC);

-- =====================================================
-- SAMPLE DATA (For Testing)
-- =====================================================
-- Uncomment to insert sample data for testing

-- INSERT INTO mood_tracker (user_id, mood_date, mood_value) VALUES
-- (1, CURDATE(), 'happy'),
-- (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'calm'),
-- (1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'energetic');

-- INSERT INTO journal_entries (user_id, entry_date, content, is_submitted) VALUES
-- (1, CURDATE(), 'Today was a productive day...', 0),
-- (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Completed all my tasks...', 1);

-- =====================================================
-- CLEANUP (For Development Only - DO NOT RUN IN PRODUCTION)
-- =====================================================
-- DROP TABLE IF EXISTS game_statistics;
-- DROP TABLE IF EXISTS game_sessions;
-- DROP TABLE IF EXISTS journal_entries;
-- DROP TABLE IF EXISTS mood_tracker;
