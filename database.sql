-- XXL’s Salon Management System
-- Import this file using phpMyAdmin. It creates the database and starter service menu.
CREATE DATABASE IF NOT EXISTS xxls_salon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xxls_salon;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','customer','staff') NOT NULL DEFAULT 'customer',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  staff_id INT UNSIGNED DEFAULT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  notes TEXT,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','approved','completed','declined','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_appointment_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_appointment_service FOREIGN KEY (service_id) REFERENCES services(id),
  CONSTRAINT fk_appointment_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_appointment_schedule (appointment_date, appointment_time, status),
  INDEX idx_appointment_customer (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT UNSIGNED NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cash','gcash','card','other') NOT NULL DEFAULT 'cash',
  payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  paid_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id)
) ENGINE=InnoDB;

INSERT INTO services (name,description,price,duration_minutes) VALUES
('Haircut & Styling','A fresh cut and a polished finish for your everyday look.',250.00,45),
('Hair Coloring','Refresh your style with a beautiful new hair color.',1200.00,150),
('Hair Treatment','Nourishing care to help your hair feel soft and healthy.',650.00,90),
('Manicure','Nail shaping, cuticle care, and polish.',200.00,45),
('Pedicure','Relaxing foot care with nail grooming and polish.',250.00,60),
('Hair Rebond','Sleek, smooth hair with a salon rebonding service.',2500.00,240);
