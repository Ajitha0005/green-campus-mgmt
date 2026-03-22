CREATE DATABASE IF NOT EXISTS green_campus;
USE green_campus;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','staff', 'student') DEFAULT 'staff'
);

INSERT IGNORE INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@example.com', '$2y$10$y58fEDw2L.pPj.7FGEQ/qO.Jz9XFvBcwv.P0lV9.C21.G15d6Vz2m', 'admin');

CREATE TABLE IF NOT EXISTS energy_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month VARCHAR(20),
    electricity_units INT,
    water_usage INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS waste (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    dry_waste FLOAT,
    wet_waste FLOAT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plantation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tree_name VARCHAR(100),
    location VARCHAR(100),
    date_planted DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    event_date DATE,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id)
);

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO events (title, description, event_date, location) VALUES 
('Tree Plantation Drive', 'Join us for a campus-wide tree plantation event to increase our green cover and biodiversity.', '2026-04-15', 'Main Playground'),
('Eco-Surroundings Workshop', 'A session on managing and improving the greenery in our immediate surroundings.', '2026-04-20', 'Seminar Hall B'),
('Campus Clean-up Day', 'Working together to manage our campus surroundings and keep them pristine.', '2026-04-21', 'Campus Entrance');
