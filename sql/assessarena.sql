CREATE TABLE accessarena_quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  creator_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  quiz_code VARCHAR(10) UNIQUE DEFAULT NULL,
  total_questions INT DEFAULT 0,
  time_limit INT DEFAULT NULL,
  status ENUM('draft','published','ended') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP NULL,

  CONSTRAINT fk_quiz_creator
    FOREIGN KEY (creator_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE accessarena_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) DEFAULT NULL,
  option_d VARCHAR(255) DEFAULT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,

  CONSTRAINT fk_question_quiz
    FOREIGN KEY (quiz_id) REFERENCES accessarena_quizzes(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE accessarena_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  user_id INT NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed TINYINT(1) DEFAULT 0,
  score INT DEFAULT 0,

  UNIQUE KEY uq_quiz_user (quiz_id, user_id),

  CONSTRAINT fk_participant_quiz
    FOREIGN KEY (quiz_id) REFERENCES accessarena_quizzes(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_participant_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `accessarena_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participant_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_answer_participant` (`participant_id`),
  KEY `fk_answer_question` (`question_id`),
  CONSTRAINT `fk_answer_participant` FOREIGN KEY (`participant_id`) REFERENCES `accessarena_participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_answer_question` FOREIGN KEY (`question_id`) REFERENCES `accessarena_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

CREATE TABLE accessarena_attempts_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  total_questions INT NOT NULL,
  attempted INT NOT NULL,
  correct INT NOT NULL,
  wrong INT NOT NULL,
  score_percentage DECIMAL(5,2),

  CONSTRAINT fk_summary_participant
    FOREIGN KEY (participant_id)
    REFERENCES accessarena_participants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_id` varchar(36) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `duration_seconds` int(11) NOT NULL,
  `started_at` datetime NOT NULL,
  `submitted_at` datetime NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `is_valid` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `attempt_id` (`attempt_id`),
  KEY `idx_quiz` (`quiz_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_score` (`quiz_id`,`score`,`duration_seconds`),
  KEY `idx_attempt_id` (`attempt_id`),
  CONSTRAINT `attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci