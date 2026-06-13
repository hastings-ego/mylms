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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token. Please refresh the page.');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_quantity') {
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            if ($quantity > 0) {
                // Update cart quantity (simple: remove and re-add, or directly manipulate session)
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] = $quantity;
                    set_flash('success', 'Cart updated.');
                }
            } else {
                removeFromCart($productId);
                set_flash('info', 'Item removed.');
            }
            redirect('cart.php');
        } elseif ($action === 'remove_item') {
            $productId = (int)$_POST['product_id'];
            removeFromCart($productId);
            set_flash('info', 'Item removed from cart.');
            redirect('cart.php');
        } elseif ($action === 'clear_cart') {
            clearCart();
            set_flash('info', 'Cart cleared.');
            redirect('cart.php');
        }
    }
}

// Get cart items
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Fun Maths Mastery</title>
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

    <!-- Store Navigation -->
    <nav class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4 md:gap-6">
                <a href="store.php" class="flex items-center justify-center w-8 h-8 rounded bg-slate-100 text-slate-500 hover:text-white hover:bg-brand-600 transition-colors" title="Continue Shopping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </div>
            <a href="cart.php" class="flex items-center gap-2 text-brand-600 bg-brand-50 px-3 py-1.5 rounded-lg border border-brand-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="font-bold text-sm bg-brand-100 text-brand-700 rounded-full px-2 py-0.5"><?= $cartCount ?></span>
            </a>
        </div>
    </nav>

    <main class="flex-1 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <a href="store.php" class="text-sm font-bold text-brand-600 hover:text-brand-800 mb-6 inline-flex items-center gap-2 bg-brand-50 px-3 py-1.5 rounded">
            &larr; Keep Shopping
        </a>

        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-8">Your Cart.</h1>

        <!-- Flash Messages -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : ($flash['type'] === 'info' ? 'bg-blue-50 border border-blue-200 text-blue-800' : 'bg-red-50 border border-red-200 text-red-800') ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="text-center py-16 border-2 border-dashed border-slate-200 rounded-xl bg-white">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium mb-6">Your toolkit is currently empty.</p>
                <a href="store.php" class="px-6 py-3 bg-brand-600 text-white text-sm font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30 inline-block">
                    Browse Math Resources
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($cartItems as $item): 
                        $product = $item['product'];
                        $quantity = $item['quantity'];
                        $subtotal = $item['subtotal'];
                        $bgClass = $product['file_type'] === 'pdf' ? 'bg-indigo-50' : 'bg-slate-50';
                    ?>
                        <li class="p-4 md:p-6 flex flex-col sm:flex-row gap-4 sm:gap-6">
                            <div class="h-24 w-24 sm:h-20 sm:w-20 mx-auto sm:mx-0 flex-shrink-0 overflow-hidden rounded-lg border border-slate-200 <?= $bgClass ?> flex items-center justify-center">
                                <span class="text-xs font-bold text-slate-400"><?= strtoupper($product['file_type'] === 'pdf' ? 'PDF' : 'LINK') ?></span>
                            </div>
                            <div class="flex flex-1 flex-col">
                                <div class="flex flex-col sm:flex-row justify-between text-base font-bold text-slate-900">
                                    <h3 class="text-center sm:text-left"><?= h($product['title']) ?></h3>
                                    <p class="mt-2 sm:mt-0 text-center sm:text-right text-brand-700">R <?= number_format($subtotal, 2) ?></p>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 text-center sm:text-left"><?= h($product['category'] ?? 'Resource') ?></p>
                                <div class="mt-4 flex flex-1 flex-row items-end justify-between text-sm">
                                    <form method="POST" class="flex items-center gap-3">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <div class="flex items-center gap-2 bg-slate-50 rounded border border-slate-200 px-3 py-1">
                                            <span class="text-slate-500 font-medium">Qty:</span>
                                            <input type="number" name="quantity" value="<?= $quantity ?>" min="1" class="w-16 text-center font-bold text-slate-900 border-0 bg-transparent focus:ring-0">
                                            <button type="submit" class="text-brand-600 text-xs font-semibold hover:underline">Update</button>
                                        </div>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="font-bold text-red-500 hover:text-red-700 px-3 py-1 bg-red-50 rounded text-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="p-6 md:p-8 bg-slate-50 border-t border-slate-200">
                    <div class="flex justify-between items-center text-lg font-bold text-slate-900 mb-4">
                        <p>Subtotal</p>
                        <p class="text-2xl">R <?= number_format($cartTotal, 2) ?></p>
                    </div>
                    <p class="text-sm text-slate-500 mb-6">Digital items are delivered instantly via email. Physical items ship within 2-3 days.</p>
                    <div class="flex gap-4">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="clear_cart">
                            <button type="submit" class="px-6 py-3 border border-red-300 text-red-600 font-bold rounded-lg hover:bg-red-50 transition-colors">Clear Cart</button>
                        </form>
                        <a href="checkout.php" class="flex-1 px-6 py-3 bg-slate-900 text-white text-base font-bold rounded-xl hover:bg-brand-600 transition-colors flex justify-center items-center gap-2 shadow-lg text-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Checkout Securely
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>