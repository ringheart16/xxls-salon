<?php
function db_schema_statements(string $driver): array {
    if ($driver === 'pgsql') {
        return [
            "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                phone VARCHAR(40) DEFAULT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(10) NOT NULL DEFAULT 'customer' CHECK (role IN ('admin','customer','staff')),
                is_active SMALLINT NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS services (
                id SERIAL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                description TEXT,
                price NUMERIC(10,2) NOT NULL DEFAULT 0,
                duration_minutes SMALLINT NOT NULL DEFAULT 60,
                is_active SMALLINT NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS appointments (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                service_id INTEGER NOT NULL REFERENCES services(id),
                staff_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
                appointment_date DATE NOT NULL,
                appointment_time TIME NOT NULL,
                notes TEXT,
                total_amount NUMERIC(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(10) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','completed','declined','cancelled')),
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE INDEX IF NOT EXISTS idx_appointment_schedule ON appointments (appointment_date, appointment_time, status)",
            "CREATE INDEX IF NOT EXISTS idx_appointment_customer ON appointments (user_id)",
            "CREATE TABLE IF NOT EXISTS payments (
                id SERIAL PRIMARY KEY,
                appointment_id INTEGER NOT NULL UNIQUE REFERENCES appointments(id),
                amount NUMERIC(10,2) NOT NULL,
                payment_method VARCHAR(10) NOT NULL DEFAULT 'cash' CHECK (payment_method IN ('cash','gcash','card','other')),
                payment_status VARCHAR(10) NOT NULL DEFAULT 'unpaid' CHECK (payment_status IN ('unpaid','paid','refunded')),
                paid_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
        ];
    }

    return [
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            phone VARCHAR(40) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','customer','staff') NOT NULL DEFAULT 'customer',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS services (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS appointments (
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
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS payments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT UNSIGNED NOT NULL UNIQUE,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cash','gcash','card','other') NOT NULL DEFAULT 'cash',
            payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
            paid_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_payment_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id)
        ) ENGINE=InnoDB",
    ];
}

function db_run_schema(PDO $pdo, string $driver): void {
    foreach (db_schema_statements($driver) as $statement) {
        $pdo->exec($statement);
    }
}

function db_seed_data(PDO $pdo): void {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($count > 0) return;

    $services = [
        ['Haircut & Styling', 'A fresh cut and a polished finish for your everyday look.', 250.00, 45],
        ['Hair Coloring', 'Refresh your style with a beautiful new hair color.', 1200.00, 150],
        ['Hair Treatment', 'Nourishing care to help your hair feel soft and healthy.', 650.00, 90],
        ['Manicure', 'Nail shaping, cuticle care, and polish.', 200.00, 45],
        ['Pedicure', 'Relaxing foot care with nail grooming and polish.', 250.00, 60],
        ['Hair Rebond', 'Sleek, smooth hair with a salon rebonding service.', 2500.00, 240],
    ];

    $insert = $pdo->prepare('INSERT INTO services (name, description, price, duration_minutes) VALUES (?, ?, ?, ?)');
    foreach ($services as $service) {
        $insert->execute($service);
    }
}

function db_bootstrap_admin(PDO $pdo): void {
    $email = strtolower(trim((string)(getenv('ADMIN_EMAIL') ?: '')));
    $password = (string)(getenv('ADMIN_PASSWORD') ?: '');
    $name = trim((string)(getenv('ADMIN_NAME') ?: 'Salon Administrator'));

    if ($email === '' || $password === '') return;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        error_log('ADMIN_EMAIL / ADMIN_PASSWORD are invalid; skipping admin bootstrap.');
        return;
    }

    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($adminCount > 0) return;

    try {
        $statement = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $ex) {
        error_log('Admin bootstrap skipped: ' . $ex->getMessage());
    }
}

function db_bootstrap(PDO $pdo, string $driver): void {
    try {
        $pdo->query('SELECT 1 FROM users LIMIT 1');
    } catch (PDOException $ex) {
        try {
            db_run_schema($pdo, $driver);
        } catch (PDOException $schemaError) {
            try {
                $pdo->query('SELECT 1 FROM users LIMIT 1');
            } catch (PDOException $stillMissing) {
                throw $schemaError;
            }
        }
    }

    db_seed_data($pdo);
    db_bootstrap_admin($pdo);
}
