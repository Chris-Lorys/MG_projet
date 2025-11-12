-- Same schema as previous version (see earlier message); kept here for completeness.
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('client','demenageur','admin') NOT NULL DEFAULT 'client',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS moves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  date_start DATETIME NOT NULL,
  city_from VARCHAR(120) NOT NULL,
  city_to VARCHAR(120) NOT NULL,
  housing_from VARCHAR(200) DEFAULT '',
  housing_to VARCHAR(200) DEFAULT '',
  volume_m3 INT NOT NULL DEFAULT 1,
  needed INT NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_moves_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS move_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  move_id INT NOT NULL,
  path VARCHAR(255) NOT NULL,
  CONSTRAINT fk_img_move FOREIGN KEY (move_id) REFERENCES moves(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS offers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  move_id INT NOT NULL,
  mover_id INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  status ENUM('pending','accepted','refused') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_offer_move FOREIGN KEY (move_id) REFERENCES moves(id) ON DELETE CASCADE,
  CONSTRAINT fk_offer_mover FOREIGN KEY (mover_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
