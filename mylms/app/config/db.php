<?php
// config/db.php - SQLite version
function resolveDatabaseFilePath() {
    $preferred = __DIR__ . '/../maths_mastery.db';
    $preferredDir = dirname($preferred);

    if (is_file($preferred) && is_writable($preferred)) {
        return realpath($preferred) ?: $preferred;
    }

    if (!is_file($preferred) && is_dir($preferredDir) && is_writable($preferredDir)) {
        @touch($preferred);
        if (is_file($preferred) && is_writable($preferred)) {
            return realpath($preferred) ?: $preferred;
        }
    }

    $fallback = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'maths_mastery_' . substr(md5(__DIR__), 0, 12) . '.db';
    if (is_file($preferred) && !is_file($fallback)) {
        @copy($preferred, $fallback);
    }
    if (!is_file($fallback)) {
        @touch($fallback);
    }
    return realpath($fallback) ?: $fallback;
}

$db_file = resolveDatabaseFilePath();

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign keys (SQLite supports them, but we keep optional)
    $pdo->exec("PRAGMA foreign_keys = ON;");
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create tables automatically if not exist (optional, can also run install.php)
function initDatabase($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'student',
            remember_token TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT DEFAULT 'General',
            file_type TEXT CHECK(file_type IN ('pdf','link')) NOT NULL,
            file_path TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            total REAL NOT NULL,
            status TEXT DEFAULT 'pending'
        );
        CREATE TABLE IF NOT EXISTS live_classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tutor_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            start_at DATETIME NOT NULL,
            end_at DATETIME NOT NULL,
            meet_link TEXT DEFAULT NULL,
            status TEXT DEFAULT 'published',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS user_product_access (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, product_id)
        );
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT UNIQUE NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Keep older local databases aligned with the current checkout flow.
    $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $hasRememberToken = false;
    foreach ($columns as $column) {
        if (($column['name'] ?? '') === 'remember_token') {
            $hasRememberToken = true;
            break;
        }
    }
    if (!$hasRememberToken) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token TEXT");
    }

    $columns = $pdo->query("PRAGMA table_info(order_items)")->fetchAll(PDO::FETCH_ASSOC);
    $hasCreatedAt = false;
    foreach ($columns as $column) {
        if (($column['name'] ?? '') === 'created_at') {
            $hasCreatedAt = true;
            break;
        }
    }
    if (!$hasCreatedAt) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    }

    $orderColumns = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_ASSOC);
    $orderColumnNames = array_map(static function ($column) {
        return $column['name'] ?? '';
    }, $orderColumns);
    if (!in_array('payment_method', $orderColumnNames, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method TEXT DEFAULT 'yoco'");
    }
    if (!in_array('payment_status', $orderColumnNames, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status TEXT DEFAULT 'paid'");
    }
    if (!in_array('payment_reference', $orderColumnNames, true)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_reference TEXT DEFAULT NULL");
    }

    $liveClassInfo = $pdo->query("PRAGMA table_info(live_classes)")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($liveClassInfo)) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS live_classes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tutor_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT,
                start_at DATETIME NOT NULL,
                end_at DATETIME NOT NULL,
                meet_link TEXT DEFAULT NULL,
                status TEXT DEFAULT 'published',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // Seed demo accounts if they do not already exist.
    $seedUsers = [
        ['Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'],
        ['Student Demo', 'student@example.com', '$2y$10$9ed7RPo1tQgx.L9BnrrPT.dstcRfA5dvKvevfDBLFGRw0OuSu4dRe', 'student'],
        ['Collaborator Demo', 'collaborator@example.com', password_hash('collab123', PASSWORD_DEFAULT), 'collaborator'],
        ['Tutor Demo', 'tutor@example.com', password_hash('tutor123', PASSWORD_DEFAULT), 'tutor'],
    ];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $insertUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    foreach ($seedUsers as $user) {
        $stmt->execute([$user[1]]);
        if ((int)$stmt->fetchColumn() === 0) {
            $insertUser->execute($user);
        }
    }

    $seedProducts = [
        ['Ultimate Algebra Cheat Sheet', 'Every formula you need for finals – digital PDF.', 95.00, 'Reference', 'pdf', 'uploads/products/algebra_cheat_sheet.pdf', 1],
        ['Calculus Limits & Derivatives', '2-page quick reference for calculus students.', 45.00, 'Reference', 'pdf', 'uploads/products/calculus_cheat_sheet.pdf', 1],
        ['Complete Geometry Workbook', '100 geometry proofs with step-by-step solutions.', 250.00, 'Workbook', 'pdf', 'uploads/products/geometry_workbook.pdf', 1],
        ['TI-84 Calculator Guide (PDF)', 'Master your calculator for exams.', 0.00, 'Free', 'pdf', 'uploads/products/ti84_guide.pdf', 1],
    ];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE title = ?");
    $insertProduct = $pdo->prepare("INSERT INTO products (title, description, price, category, file_type, file_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($seedProducts as $product) {
        $stmt->execute([$product[0]]);
        if ((int)$stmt->fetchColumn() === 0) {
            $insertProduct->execute($product);
        }
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM live_classes");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE lower(email) = lower(?) LIMIT 1");
        $stmt->execute(['tutor@example.com']);
        $tutorId = (int) $stmt->fetchColumn();
        if ($tutorId > 0) {
            $insertClass = $pdo->prepare("INSERT INTO live_classes (tutor_id, title, description, start_at, end_at, meet_link, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertClass->execute([
                $tutorId,
                'Algebra Live Class',
                'Weekly live support session for algebra learners.',
                date('Y-m-d H:i:s', strtotime('tomorrow 15:00')),
                date('Y-m-d H:i:s', strtotime('tomorrow 16:00')),
                '',
                'published',
            ]);
        }
    }

    $tableInfo = $pdo->query("PRAGMA table_info(password_reset_tokens)")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($tableInfo)) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT UNIQUE NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}
initDatabase($pdo);
?>
