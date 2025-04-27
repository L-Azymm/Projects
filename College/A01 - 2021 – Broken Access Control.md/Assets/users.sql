CREATE DATABASE IF NOT EXISTS app;
USE app;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'user'  -- user or admin
);

INSERT INTO users (username, password, role) VALUES
('userA', 'password123', 'user'),
('userB', 'password123', 'user'),
('admin', 'admin123', 'admin');
