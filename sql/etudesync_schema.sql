-- USERS
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) DEFAULT '',
  `dob` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `bio` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ROOMS
CREATE TABLE rooms (
  room_id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  topic VARCHAR(255) DEFAULT NULL,
  room_code VARCHAR(12) NOT NULL,
  scheduled_time DATETIME DEFAULT NULL,
  host_user_id INT(11) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (room_id),
  UNIQUE KEY room_code (room_code),
  KEY host_user_id (host_user_id),
  CONSTRAINT fk_rooms_host
    FOREIGN KEY (host_user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ROOM PARTICIPANTS
CREATE TABLE room_participants (
  id INT(11) NOT NULL AUTO_INCREMENT,
  room_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY room_id (room_id),
  KEY user_id (user_id),
  CONSTRAINT fk_participants_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_participants_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FILES
CREATE TABLE files (
  file_id INT(11) NOT NULL AUTO_INCREMENT,
  room_id INT(11) NOT NULL,
  user_id INT(11) DEFAULT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(512) NOT NULL,
  mime_type VARCHAR(100) DEFAULT NULL,
  size_bytes BIGINT(20) DEFAULT NULL,
  is_pinned TINYINT(1) DEFAULT 0,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (file_id),
  KEY ix_files_room (room_id),
  KEY ix_files_user (user_id),
  CONSTRAINT fk_files_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_files_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- MESSAGES
CREATE TABLE messages (
  message_id INT(11) NOT NULL AUTO_INCREMENT,
  room_id INT(11) NOT NULL,
  user_id INT(11) DEFAULT NULL,
  message TEXT NOT NULL,
  is_edited TINYINT(1) DEFAULT 0,
  is_pinned TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (message_id),
  KEY ix_msg_room (room_id),
  KEY ix_msg_user (user_id),
  CONSTRAINT fk_msg_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_msg_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- NOTIFICATIONS
CREATE TABLE notifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  body TEXT DEFAULT NULL,
  url VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- POLLS
CREATE TABLE polls (
  poll_id INT(11) NOT NULL AUTO_INCREMENT,
  room_id INT(11) NOT NULL,
  question TEXT NOT NULL,
  option_a VARCHAR(255) DEFAULT NULL,
  option_b VARCHAR(255) DEFAULT NULL,
  option_c VARCHAR(255) DEFAULT NULL,
  option_d VARCHAR(255) DEFAULT NULL,
  allow_multiple TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (poll_id),
  KEY ix_polls_room (room_id),
  CONSTRAINT fk_polls_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- POLL VOTES
CREATE TABLE poll_votes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  poll_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  selected_option VARCHAR(10) NOT NULL,
  voted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY ix_votes_poll (poll_id),
  KEY ix_votes_user (user_id),
  CONSTRAINT fk_votes_poll
    FOREIGN KEY (poll_id) REFERENCES polls(poll_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_votes_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- WHITEBOARD
CREATE TABLE whiteboard_data (
  id INT(11) NOT NULL AUTO_INCREMENT,
  room_id INT(11) NOT NULL,
  data LONGTEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
    ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY ix_wb_room (room_id),
  CONSTRAINT fk_wb_room
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--infovalut_files 
CREATE TABLE infovault_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(512) NOT NULL,
  mime_type VARCHAR(100),
  size_bytes BIGINT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- room_files
CREATE TABLE room_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  infovault_file_id INT NOT NULL,
  shared_by INT NOT NULL,
  shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (infovault_file_id) REFERENCES infovault_files(id) ON DELETE CASCADE,
  FOREIGN KEY (shared_by) REFERENCES users(id) ON DELETE CASCADE
);

-- forget password tokens
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

