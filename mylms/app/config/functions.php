<?php
/**
 * Functions file for Fun Maths Mastery (SQLite version)
 * Includes session management, authentication helpers, and data retrieval functions.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged-in user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to a given URL and exit
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get all products purchased by a user
 * @param int $userId
 * @return array
 */
function getUserPurchasedProducts($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, upa.purchase_date 
        FROM user_product_access upa
        JOIN products p ON upa.product_id = p.id
        WHERE upa.user_id = ?
        ORDER BY upa.purchase_date DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if a user has purchased a specific product
 * @param int $userId
 * @param int $productId
 * @return bool
 */
function hasPurchased($userId, $productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM user_product_access WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return $stmt->fetchColumn() !== false;
}

/**
 * Get cart count from session
 * @return int
 */
function getCartCount() {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

/**
 * Add a product to cart
 * @param int $productId
 * @param int $quantity
 */
function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $productId = (int)$productId;
    $quantity = (int)$quantity;
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

/**
 * Remove a product from cart
 * @param int $productId
 */
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

/**
 * Clear the entire cart
 */
function clearCart() {
    unset($_SESSION['cart']);
}

/**
 * Get cart items with product details
 * @return array
 */
function getCartItems() {
    global $pdo;
    $items = [];
    if (empty($_SESSION['cart'])) return $items;
    
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $items[] = [
            'product' => $product,
            'quantity' => $qty,
            'subtotal' => $product['price'] * $qty
        ];
    }
    return $items;
}

/**
 * Calculate cart total
 * @return float
 */
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

/**
 * Sanitize output for HTML safety
 * @param string $string
 * @return string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token and store in session
 * @return string
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display flash message and clear it
 * @param string $type (success, error, info)
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get user by ID
 * @param int $userId
 * @return array|false
 */
function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update user profile
 * @param int $userId
 * @param string $name
 * @param string $email
 * @return bool
 */
function updateUserProfile($userId, $name, $email) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $userId]);
}

/**
 * Change user password
 * @param int $userId
 * @param string $newPassword
 * @return bool
 */
function updateUserPassword($userId, $newPassword) {
    global $pdo;
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed, $userId]);
}

/**
 * Get user by email address.
 * @param string $email
 * @return array|false
 */
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a password reset token and return the raw token.
 * The raw token should be presented once to the user; the hash is stored.
 *
 * @param int $userId
 * @param int $minutesValid
 * @return string
 */
function createPasswordResetToken($userId, $minutesValid = 30) {
    global $pdo;

    $rawToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + ($minutesValid * 60));
    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $rawToken, $expiresAt]);

    return $rawToken;
}

/**
 * Resolve a password reset token to a user ID if it is valid.
 *
 * @param string $token
 * @return array|false
 */
function getPasswordResetToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT prt.*, u.email, u.name
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.id
        WHERE prt.token = ? AND prt.used_at IS NULL AND prt.expires_at > datetime('now')
        LIMIT 1
    ");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Mark a password reset token as used.
 *
 * @param string $token
 * @return bool
 */
function markPasswordResetTokenUsed($token) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used_at = datetime('now') WHERE token = ?");
    return $stmt->execute([$token]);
}

/**
 * Get all products (active only by default)
 * @param bool $includeInactive
 * @return array
 */
function getAllProducts($includeInactive = false) {
    global $pdo;
    if ($includeInactive) {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    } else {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a single product by ID
 * @param int $id
 * @return array|false
 */
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get recommended products for a user (products not purchased, active, newest first, limit)
 * @param int $userId
 * @param int $limit
 * @return array
 */
function getRecommendedProducts($userId, $limit = 3) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE is_active = 1 
        AND id NOT IN (SELECT product_id FROM user_product_access WHERE user_id = ?)
        ORDER BY id DESC 
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Render the public site header and primary navigation.
 * Used by landing and marketing pages to keep the UI consistent.
 *
 * @param string $pageTitle
 * @param string $description
 * @param string $activePage
 * @param string $canonicalPath
 */
function renderPublicLayoutStart($pageTitle, $description, $activePage = 'home', $canonicalPath = '/') {
    $fullTitle = $pageTitle ? $pageTitle . ' | Fun Maths Mastery' : 'Fun Maths Mastery';
    $canonicalPath = '/' . ltrim($canonicalPath, '/');
    $navItems = [
        'home' => ['label' => 'Home', 'href' => 'index.php'],
        'features' => ['label' => 'Features', 'href' => 'features.php'],
        'curriculum' => ['label' => 'Curriculum', 'href' => 'curriculum.php'],
        'about' => ['label' => 'About', 'href' => 'about.php'],
        'teachers' => ['label' => 'Teachers', 'href' => 'teachers.php'],
        'pricing' => ['label' => 'Pricing', 'href' => 'pricing.php'],
        'testimonials' => ['label' => 'Testimonials', 'href' => 'testimonials.php'],
        'contact' => ['label' => 'Contact', 'href' => 'contact.php'],
        'store' => ['label' => 'Store', 'href' => 'store.php'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($fullTitle) ?></title>
    <meta name="description" content="<?= h($description) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://funmathsmastery.com<?= h($canonicalPath) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#ee9c85',
                            600: '#f07450',
                            700: '#f07450',
                            900: '#e35b35',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased overflow-x-hidden">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-2 shrink-0">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-12 h-12">
            </a>
            <div class="hidden md:flex space-x-6 items-center">
                <?php foreach ($navItems as $key => $item): ?>
                    <?php
                        $isActive = $activePage === $key;
                        $linkClass = $isActive
                            ? 'text-brand-700 font-semibold'
                            : 'text-slate-500 hover:text-brand-600 font-medium';
                    ?>
                    <a href="<?= h($item['href']) ?>" class="<?= $linkClass ?> text-sm transition-colors">
                        <?= h($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
                        Student Login
                    </a>
                <?php endif; ?>
            </div>
            <button id="mobile-menu-btn" class="md:hidden text-slate-500 hover:text-slate-900 focus:outline-none p-2" aria-label="Toggle menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path id="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-slate-200">
            <div class="px-4 pt-2 pb-6 space-y-1 sm:px-3 flex flex-col items-center">
                <?php foreach ($navItems as $key => $item): ?>
                    <?php
                        $isActive = $activePage === $key;
                        $mobileClass = $isActive
                            ? 'text-brand-700 bg-brand-50'
                            : 'text-slate-700 hover:text-brand-600 hover:bg-slate-50';
                    ?>
                    <a href="<?= h($item['href']) ?>" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium <?= $mobileClass ?>">
                        <?= h($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="mt-4 w-full block text-center px-5 py-3 border border-transparent text-base font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="mt-4 w-full block text-center px-5 py-3 border border-transparent text-base font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700">Student Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>
    <?php
}

/**
 * Close the public site layout.
 */
function renderPublicLayoutEnd() {
    ?>
    </main>
    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-12 h-12">
            </div>
            <div class="text-slate-500 text-sm text-center md:text-left">
                &copy; 2026 Fun Maths Mastery. All rights reserved.
            </div>
        </div>
        <div class="text-center pt-6 pb-3 text-slate-500 text-sm">
            Powered By <a href="https://varsitymarket.co.za" class="hover:text-brand-600">Varsity Market</a> Technologies
        </div>
    </footer>
    <script>
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIconPath = document.getElementById('menu-icon-path');

        if (menuBtn && mobileMenu && menuIconPath) {
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                if (mobileMenu.classList.contains('hidden')) {
                    menuIconPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                } else {
                    menuIconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
                }
            });
        }
    </script>
</body>
</html>
    <?php
}

/**
 * Render the admin portal shell.
 *
 * @param string $pageTitle
 * @param string $activePage
 */
function renderAdminLayoutStart($pageTitle, $activePage = 'dashboard') {
    $nav = [
        'dashboard' => 'Dashboard',
        'products' => 'Products',
        'orders' => 'Orders',
        'users' => 'Users',
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#ee9c85',
                            600: '#f07450',
                            700: '#f07450',
                            900: '#e35b35',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <a href="dashboard.php" class="flex items-center gap-2">
                <img src="../assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                <span class="font-bold text-slate-900 hidden sm:inline">Admin Panel</span>
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($_SESSION['user_name'] ?? 'Admin') ?></span>
                <a href="../logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </header>
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 -mb-px">
                <?php foreach ($nav as $key => $label): ?>
                    <?php $isActive = $activePage === $key; ?>
                    <a href="<?= $key ?>.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm <?= $isActive ? 'font-semibold border-b-2 border-brand-600 text-brand-600' : 'font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent' ?>">
                        <?= h($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php
}

function renderAdminLayoutEnd() {
    ?>
    </main>
</body>
</html>
    <?php
}
?>
