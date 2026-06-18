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

// Handle adding to cart via GET (simple approach)
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $productId = (int)$_GET['add'];
    addToCart($productId, 1);
    set_flash('success', 'Product added to cart!');
    redirect('store.php');
}

// Remove from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    removeFromCart($productId);
    set_flash('info', 'Product removed from cart.');
    redirect('store.php');
}

// Handle search
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];

if (!empty($searchQuery)) {
    $products = searchProducts($searchQuery, true);
} else {
    // Fetch all active products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get cart count for badge
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Store | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            
                        50: '#eef2ff',                                     50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#ee9c85',
                            600: '#f07450',
                            700: '#f07450',
                            900: '#e35b35',}
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Store Navigation (similar to original store.html) -->
    <nav class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4 md:gap-6">
                <a href="dashboard.php" class="flex items-center justify-center w-8 h-8 rounded bg-slate-100 text-slate-500 hover:text-white hover:bg-brand-600 transition-colors" title="Back to Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </div>
            <a href="cart.php" class="flex items-center gap-2 text-slate-600 hover:text-brand-600 transition-colors bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="font-bold text-sm bg-brand-100 text-brand-700 rounded-full px-2 py-0.5" id="cart-count"><?= $cartCount ?></span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 w-full">
        <!-- Flash Message -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : ($flash['type'] === 'info' ? 'bg-blue-50 border border-blue-200 text-blue-800' : 'bg-red-50 border border-red-200 text-red-800') ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <header class="mb-8 md:mb-10 text-center md:text-left flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900">Online Store.</h1>
                <p class="text-slate-500 mt-2">Essential calculators, workbooks, and digital formula sheets.</p>
            </div>
            <form method="GET" class="w-full md:w-72">
                <div class="relative">
                    <input type="text" name="search" placeholder="Search products..." value="<?= h($searchQuery) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600 focus:border-transparent">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </header>

        <?php if (empty($products)): ?>
            <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                <?php if (!empty($searchQuery)): ?>
                    <p class="text-slate-500 mb-4">No products found matching "<strong><?= h($searchQuery) ?></strong>"</p>
                    <a href="store.php" class="inline-block px-6 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700">Clear Search</a>
                <?php else: ?>
                    <p class="text-slate-500">No products available at the moment. Please check back later.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if (!empty($searchQuery)): ?>
            <div class="mb-6 flex items-center justify-between">
                <p class="text-slate-600">Found <strong><?= count($products) ?></strong> product<?= count($products) !== 1 ? 's' : '' ?> matching "<strong><?= h($searchQuery) ?></strong>"</p>
                <a href="store.php" class="text-sm text-brand-600 hover:text-brand-700">Clear Search</a>
            </div>
            <?php endif; ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="group border border-slate-200 rounded-xl overflow-hidden bg-white hover:border-brand-300 hover:shadow-lg transition-all flex flex-col">
                        <div class="aspect-[4/3] bg-slate-50 border-slate-100 relative p-4 flex items-center justify-center border-b group-hover:bg-slate-100 transition-colors overflow-hidden">
                            <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                <img src="<?= h($product['image_path']) ?>" alt="<?= h($product['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            <?php else: ?>
                                <div class="text-center">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-xs text-slate-400">No image</span>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded shadow-sm bg-white text-slate-800 border border-slate-200"><?= h($product['category'] ?? 'Resource') ?></span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight mb-1 group-hover:text-brand-600 transition-colors"><?= h($product['title']) ?></h3>
                            <p class="text-sm text-slate-500 mb-4 line-clamp-2"><?= h(substr($product['description'], 0, 80)) ?>...</p>
                            <div class="mt-auto flex items-center justify-between pt-4 border-t border-slate-50">
                                <span class="text-lg font-black text-slate-900">R <?= number_format($product['price'], 2) ?></span>
                                <a href="store.php?add=<?= $product['id'] ?>" class="px-4 py-2 bg-slate-900 text-white text-sm font-bold rounded-lg hover:bg-brand-600 transition-colors">Add to Cart</a>
                            </div>
                            <a href="product.php?id=<?= $product['id'] ?>" class="text-brand-600 text-xs mt-2 inline-block text-center hover:underline">View Details →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Update cart count if needed (no AJAX here, just page load)
        // For demo, cart count is already set via PHP.
    </script>
</body>
</html>