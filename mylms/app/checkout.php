<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}
if (isAdmin()) {
    redirect('admin/dashboard.php');
}

// Check if cart is empty
$cartItems = getCartItems();
if (empty($cartItems)) {
    set_flash('error', 'Your cart is empty.');
    redirect('store.php');
}

$cartTotal = getCartTotal();
$userId = $_SESSION['user_id'];

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('checkout.php');
    }
    
    // Simple validation for required fields
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'simulated';
    
    if (empty($fullName) || empty($email) || empty($address) || empty($city)) {
        set_flash('error', 'Please fill in all required fields.');
        redirect('checkout.php');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
        redirect('checkout.php');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, order_date) VALUES (?, ?, 'completed', datetime('now'))");
        $stmt->execute([$userId, $cartTotal]);
        $orderId = $pdo->lastInsertId();
        
        // Insert order items and grant product access
        foreach ($cartItems as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $price = $product['price'];
            
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, created_at) VALUES (?, ?, ?, datetime('now'))");
            $stmt->execute([$orderId, $product['id'], $price]);
            
            // Grant access to user (if not already granted – but checkout ensures new)
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO user_product_access (user_id, product_id, purchase_date) VALUES (?, ?, datetime('now'))");
            $stmt->execute([$userId, $product['id']]);
        }
        
        $pdo->commit();
        
        // Clear cart
        clearCart();
        
        // Set success message
        set_flash('success', 'Order placed successfully! You can now access your purchased resources.');
        redirect('my-courses.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Checkout failed: ' . $e->getMessage());
        redirect('checkout.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 900: '#312e81' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <nav class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="cart.php" class="flex items-center justify-center w-8 h-8 rounded bg-slate-100 text-slate-500 hover:text-brand-600 transition-colors" title="Back to Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </div>
            <span class="text-sm font-bold text-slate-600">Secure Checkout</span>
        </div>
    </nav>

    <main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-6">Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Billing Form -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 md:p-8">
                    <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Billing Details</h2>
                    <form method="POST" action="" id="checkout-form">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name *</label>
                                <input type="text" name="full_name" value="<?= h($_SESSION['user_name']) ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address *</label>
                                <input type="email" name="email" value="<?php
                                    $user = getUserById($userId);
                                    echo h($user['email'] ?? '');
                                ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Street Address *</label>
                            <input type="text" name="address" placeholder="House number and street name" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">City / Town *</label>
                                <input type="text" name="city" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Postal Code</label>
                                <input type="text" name="postal_code" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600">
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600">
                                <option value="simulated">Simulated Payment (Demo)</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-2">This is a demo store. No actual payment will be processed.</p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Your Order</h2>
                    <div class="space-y-3 mb-4 max-h-80 overflow-y-auto">
                        <?php foreach ($cartItems as $item): 
                            $product = $item['product'];
                            $quantity = $item['quantity'];
                            $subtotal = $item['subtotal'];
                        ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600"><?= h($product['title']) ?> x<?= $quantity ?></span>
                                <span class="font-semibold">R <?= number_format($subtotal, 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t border-slate-200 pt-4 mb-6">
                        <div class="flex justify-between text-base font-bold">
                            <span>Total</span>
                            <span class="text-brand-600 text-xl">R <?= number_format($cartTotal, 2) ?></span>
                        </div>
                    </div>
                    <button type="submit" form="checkout-form" class="w-full py-3 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">
                        Place Order
                    </button>
                    <p class="text-xs text-center text-slate-500 mt-4">By placing your order, you agree to our terms and conditions.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>