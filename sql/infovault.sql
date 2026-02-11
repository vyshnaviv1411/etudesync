-- ===============================
-- FLASHCARD SETS (CREATE FIRST)
-- ===============================

CREATE TABLE flashcard_sets (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  title VARCHAR(200) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- ===============================
-- FLASHCARDS
-- ===============================

CREATE TABLE flashcards (
  id INT(11) NOT NULL AUTO_INCREMENT,
  set_id INT(11) NOT NULL,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY set_id (set_id),
  CONSTRAINT flashcards_ibfk_1
    FOREIGN KEY (set_id)
    REFERENCES flashcard_sets(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- ===============================
-- INFOVAULT FILES
-- ===============================

CREATE TABLE infovault_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(512) NOT NULL,
  mime_type VARCHAR(100),
  size_bytes BIGINT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_infovault_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- ===============================
-- MINDMAPS
-- ===============================

CREATE TABLE mindmaps (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_mindmaps_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- ===============================
-- MINDMAP NODES
-- ===============================

CREATE TABLE mindmap_nodes (
  id INT(11) NOT NULL AUTO_INCREMENT,
  mindmap_id INT(11) NOT NULL,
  parent_id INT(11) DEFAULT NULL,
  text VARCHAR(255) NOT NULL,
  x INT(11) NOT NULL DEFAULT 0,
  y INT(11) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id),
  KEY mindmap_id (mindmap_id),
  KEY parent_id (parent_id),
  CONSTRAINT fk_nodes_mindmap
    FOREIGN KEY (mindmap_id)
    REFERENCES mindmaps(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_nodes_parent
    FOREIGN KEY (parent_id)
    REFERENCES mindmap_nodes(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
