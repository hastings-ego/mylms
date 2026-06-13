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
?>