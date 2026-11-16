-- Run this only if your existing database does not yet support staff roles/assignments.
USE xxls_salon;
ALTER TABLE users MODIFY role ENUM('admin','customer','staff') NOT NULL DEFAULT 'customer';
ALTER TABLE appointments ADD COLUMN staff_id INT UNSIGNED NULL AFTER service_id;
ALTER TABLE appointments ADD INDEX idx_appointment_staff (staff_id);
ALTER TABLE appointments ADD CONSTRAINT fk_appointment_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL;
