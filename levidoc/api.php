<?php
/**
 * Levidoc Agency API - SQLite Backend
 * Single file API for client portal, admin panel, and landing page.
 * Uses PHP sessions for authentication.
 */

// Enable CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:7600'); // Adjust to your frontend origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session for user authentication
session_start();

// Database path (adjust as needed)
define('DB_PATH', __DIR__ . '/levidoc.sqlite');

// Initialize database tables
function initDatabase() {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'client',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Projects (quotations) table
    $db->exec("CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        budget TEXT,
        tech_stack TEXT,
        status TEXT DEFAULT 'pending',
        admin_notes TEXT,
        published_url TEXT,
        app_url TEXT,
        created_at DATE DEFAULT CURRENT_DATE,
        updated_at DATE DEFAULT CURRENT_DATE,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");
    
    // Schema Migrations
    try { $db->exec("ALTER TABLE projects ADD COLUMN published_url TEXT"); } catch (Exception $e) {}
    try { $db->exec("ALTER TABLE projects ADD COLUMN app_url TEXT"); } catch (Exception $e) {}
    
    // Messages from landing page contact form
    $db->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        message TEXT NOT NULL,
        budget TEXT,
        source TEXT DEFAULT 'landing',
        read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Invoices (for Yoco payment demo, optional)
    $db->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        client_name TEXT NOT NULL,
        amount REAL NOT NULL,
        status TEXT DEFAULT 'unpaid',
        yoco_link TEXT,
        due_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default admin if not exists
    $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
            ->execute(['System Administrator', 'admin@levidoc.com', $hashed]);
    }
    
    // Insert demo data if no projects exist
    $stmt = $db->prepare("SELECT COUNT(*) FROM projects");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Get demo client user IDs
        $client1 = null;
        $client2 = null;
        $stmt = $db->prepare("SELECT id FROM users WHERE email = 'emma@example.com' LIMIT 1");
        $stmt->execute();
        $client1 = $stmt->fetchColumn();
        if (!$client1) {
            $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')")
                ->execute(['Emma Retail', 'emma@example.com', password_hash('pass123', PASSWORD_DEFAULT)]);
            $client1 = $db->lastInsertId();
        }
        $stmt = $db->prepare("SELECT id FROM users WHERE email = 'michael@brands.com' LIMIT 1");
        $stmt->execute();
        $client2 = $stmt->fetchColumn();
        if (!$client2) {
            $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')")
                ->execute(['Michael Brands', 'michael@brands.com', password_hash('pass123', PASSWORD_DEFAULT)]);
            $client2 = $db->lastInsertId();
        }
        
        $projects = [
            [$client1, 'Luxury Fashion Store', 'Full headless e-commerce with Shopify + React frontend', '$8,200', 'Shopify, React, Tailwind', 'approved', 'Ready for construction phase', '2026-04-10', '2026-04-15'],
            [$client1, 'Organic Food Marketplace', 'Multi-vendor platform with subscription', '$12,500', 'WooCommerce, Vue, Stripe', 'pending', 'Awaiting admin review', '2026-05-01', '2026-05-01'],
            [$client2, 'Electronics Outlet', 'High-volume catalog with AI search', '$15,200', 'Magento, Node.js', 'construction', 'Building custom theme', '2026-03-20', '2026-05-10']
        ];
        $insert = $db->prepare("INSERT INTO projects (user_id, title, description, budget, tech_stack, status, admin_notes, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($projects as $p) {
            $insert->execute($p);
        }
    }
    
    // Insert demo invoices if empty
    $stmt = $db->prepare("SELECT COUNT(*) FROM invoices");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $db->prepare("INSERT INTO invoices (client_name, amount, status, yoco_link, due_date) VALUES (?, ?, ?, ?, ?)")
            ->execute(['Apex Retail Group', 850, 'paid', 'https://pay.yoco.com/levidoc-demo', '2026-05-01']);
        $db->prepare("INSERT INTO invoices (client_name, amount, status, yoco_link, due_date) VALUES (?, ?, ?, ?, ?)")
            ->execute(['Apex Retail Group', 850, 'unpaid', 'https://pay.yoco.com/levidoc-demo', '2026-06-01']);
        $db->prepare("INSERT INTO invoices (client_name, amount, status, yoco_link, due_date) VALUES (?, ?, ?, ?, ?)")
            ->execute(['Quantum Logic', 1600, 'unpaid', 'https://pay.yoco.com/levidoc-demo', '2026-05-28']);
    }
}

// Helper: Get DB connection
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

// Helper: require authentication (user must be logged in)
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
        exit;
    }
}

// Helper: require admin role
function requireAdmin() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required.']);
        exit;
    }
}

// Initialize database on first run
initDatabase();

// Parse request action
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Routing
try {
    switch ($action) {
        // REGISTER
        case 'register':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            if (!$name || !$email || !$password) {
                throw new Exception('Name, email and password required');
            }
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered');
            }
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')")
                ->execute([$name, $email, $hashed]);
            echo json_encode(['success' => true, 'message' => 'Registration successful. Please login.']);
            break;
            
        // LOGIN
        case 'login':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            if (!$email || !$password) {
                throw new Exception('Email and password required');
            }
            $db = getDB();
            $stmt = $db->prepare("SELECT id, name, email, role, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception('Invalid credentials');
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
            break;
            
        // LOGOUT
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;
            
        // UPDATE PROFILE
        case 'update_profile':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            requireAuth();
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            if (!$name || !$email) throw new Exception('Name and email required');
            $db = getDB();
            // Check email not used by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) throw new Exception('Email already taken');
            $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")
                ->execute([$name, $email, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            echo json_encode(['success' => true, 'message' => 'Profile updated']);
            break;
            
        // CLIENT DATA (dashboard)
        case 'client_data':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            requireAuth();
            if ($_SESSION['role'] !== 'client') {
                throw new Exception('This endpoint is for client accounts');
            }
            $userId = $_SESSION['user_id'];
            $db = getDB();
            // Get user details
            $stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Get subscription (from projects? For demo we derive from first approved project)
            $subName = 'Starter Plan';
            $subPrice = 0;
            $stmt = $db->prepare("SELECT budget, status FROM projects WHERE user_id = ? AND status != 'pending' LIMIT 1");
            $stmt->execute([$userId]);
            if ($row = $stmt->fetch()) {
                $subName = $row['budget'] ? 'Custom Plan - ' . $row['budget'] : 'Growth Plan';
                $subPrice = 850; // demo fixed
            }
            // Get websites (projects)
            $stmt = $db->prepare("SELECT id, title as domain, status, tech_stack as type FROM projects WHERE user_id = ?");
            $stmt->execute([$userId]);
            $websites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Get invoices (demo: all invoices with client name matching user's name)
            $stmt = $db->prepare("SELECT id, amount, status, yoco_link, due_date FROM invoices WHERE client_name = ?");
            $stmt->execute([$user['name']]);
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'subscription_name' => $subName,
                    'subscription_price' => $subPrice
                ],
                'websites' => $websites,
                'invoices' => $invoices
            ]);
            break;
            
        // ADMIN DASHBOARD
        case 'admin_dashboard':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            requireAdmin();
            $db = getDB();
            // Metrics
            $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
            $totalSites = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            // Users list with project counts
            $usersStmt = $db->query("SELECT id, name, email FROM users WHERE role = 'client'");
            $users = [];
            while ($u = $usersStmt->fetch(PDO::FETCH_ASSOC)) {
                $projCount = $db->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?")->execute([$u['id']]);
                $stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
                $stmt->execute([$u['id']]);
                $u['total_domains'] = $stmt->fetchColumn();
                // get subscription name from first project
                $subStmt = $db->prepare("SELECT budget, tech_stack FROM projects WHERE user_id = ? LIMIT 1");
                $subStmt->execute([$u['id']]);
                $sub = $subStmt->fetch(PDO::FETCH_ASSOC);
                $u['subscription_name'] = $sub ? ($sub['budget'] ?: 'Basic Plan') : 'No plan';
                $u['subscription_price'] = 850; // demo
                $users[] = $u;
            }
            // Invoices
            $invoices = $db->query("SELECT id, client_name, amount, status, yoco_link, due_date FROM invoices")->fetchAll(PDO::FETCH_ASSOC);
            
            $projectsStmt = $db->query("SELECT p.*, u.name as clientName, u.email as clientEmail FROM projects p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
            $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'metrics' => ['total_users' => $totalUsers, 'total_sites' => $totalSites],
                'users' => $users,
                'invoices' => $invoices,
                'projects' => $projects
            ]);
            break;
            
        // UPDATE PROJECT STATUS (Admin)
        case 'update_project_status':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            requireAdmin();
            $projectId = $input['project_id'] ?? null;
            $newStatus = $input['status'] ?? null;
            $adminNotes = $input['admin_notes'] ?? null;
            $publishedUrl = $input['published_url'] ?? null;
            $appUrl = $input['app_url'] ?? null;
            if (!$projectId || !in_array($newStatus, ['pending', 'approved', 'construction'])) {
                throw new Exception('Invalid project ID or status');
            }
            $db = getDB();
            $update = "UPDATE projects SET status = ?, updated_at = DATE('now')";
            $params = [$newStatus];
            if ($adminNotes !== null) {
                $update .= ", admin_notes = ?";
                $params[] = $adminNotes;
            }
            if ($publishedUrl !== null) {
                $update .= ", published_url = ?";
                $params[] = $publishedUrl;
            }
            if ($appUrl !== null) {
                $update .= ", app_url = ?";
                $params[] = $appUrl;
            }
            $update .= " WHERE id = ?";
            $params[] = $projectId;
            $stmt = $db->prepare($update);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Project updated']);
            break;
            
        // ADD NEW PROJECT (Client creates quote)
        case 'add_project':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            requireAuth();
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $budget = trim($input['budget'] ?? '');
            $techStack = trim($input['techStack'] ?? '');
            if (!$title || !$description) throw new Exception('Title and description required');
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO projects (user_id, title, description, budget, tech_stack, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'pending', DATE('now'), DATE('now'))");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $budget, $techStack]);
            echo json_encode(['success' => true, 'message' => 'Quote submitted successfully']);
            break;
            
        // GET PROJECTS FOR CLIENT
        case 'client_projects':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            requireAuth();
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'projects' => $projects]);
            break;
            
        // SUBMIT CONTACT MESSAGE (from landing page)
        case 'contact_message':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $message = trim($input['message'] ?? '');
            $budget = trim($input['budget'] ?? '');
            if (!$name || !$email || !$message) throw new Exception('Name, email and message required');
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO messages (name, email, message, budget, source, created_at) VALUES (?, ?, ?, ?, 'landing', DATETIME('now'))");
            $stmt->execute([$name, $email, $message, $budget]);
            echo json_encode(['success' => true, 'message' => 'Message sent. We will contact you soon.']);
            break;
            
        // ADMIN GET MESSAGES
        case 'get_messages':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            requireAdmin();
            $db = getDB();
            $stmt = $db->query("SELECT * FROM messages ORDER BY created_at DESC");
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        // TOGGLE PAYMENT STATUS (for invoice demo)
        case 'toggle_payment_status':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            requireAdmin();
            $invoiceId = $input['invoice_id'] ?? null;
            $currentStatus = $input['current_status'] ?? '';
            if (!$invoiceId) throw new Exception('Invoice ID required');
            $newStatus = ($currentStatus === 'paid') ? 'unpaid' : 'paid';
            $db = getDB();
            $db->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$newStatus, $invoiceId]);
            echo json_encode(['success' => true, 'new_status' => $newStatus]);
            break;
            
        // RENEW YOCO LINK (demo)
        case 'renew_yoco_link':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            requireAdmin();
            $invoiceId = $input['invoice_id'] ?? null;
            if (!$invoiceId) throw new Exception('Invoice ID required');
            $newLink = 'https://pay.yoco.com/levidoc-renew/' . uniqid();
            $db = getDB();
            $db->prepare("UPDATE invoices SET yoco_link = ? WHERE id = ?")->execute([$newLink, $invoiceId]);
            echo json_encode(['success' => true, 'new_link' => $newLink]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}