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

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$productId) {
    set_flash('error', 'Product not found.');
    redirect('store.php');
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('store.php');
}

$userId = $_SESSION['user_id'];
$isPurchased = hasPurchased($userId, $productId);
$cartCount = getCartCount();

// Handle add to cart via GET
if (isset($_GET['add'])) {
    addToCart($productId, 1);
    set_flash('success', 'Product added to cart!');
    redirect('product.php?id=' . $productId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($product['title']) ?> | Fun Maths Mastery</title>
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
                            900: '#e35b35', }
                    }
                }
            }
        }
    </script>
    <style>
        details>summary { list-style: none; }
        details>summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Store Navigation -->
    <nav class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4 md:gap-6">
                <a href="store.php" class="flex items-center justify-center w-8 h-8 rounded bg-slate-100 text-slate-500 hover:text-white hover:bg-brand-600 transition-colors" title="Back to Store">
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

    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <!-- Flash Messages -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Left: Product Image -->
                <div class="w-full md:w-1/2 p-8 md:border-r border-slate-200 bg-slate-50 flex items-center justify-center min-h-[300px] md:min-h-[500px]">
                    <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                        <img src="<?= h($product['image_path']) ?>" alt="<?= h($product['title']) ?>" class="w-full h-full object-cover rounded-lg">
                    <?php else: ?>
                        <div class="text-center">
                            <svg class="w-32 h-32 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-slate-400 font-semibold mb-2"><?= h(substr($product['title'], 0, 30)) ?></p>
                            <span class="px-3 py-1 bg-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider rounded"><?= strtoupper($product['file_type']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right: Product Details -->
                <div class="w-full md:w-1/2 p-6 md:p-10 flex flex-col">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider rounded"><?= h($product['category'] ?? 'Resource') ?></span>
                        <?php if ($product['file_type'] === 'pdf'): ?>
                            <span class="px-2.5 py-1 bg-indigo-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded">Instant Download</span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded">External Link</span>
                        <?php endif; ?>
                        <?php if ($isPurchased): ?>
                            <span class="px-2.5 py-1 bg-green-100 text-green-800 text-xs font-bold uppercase tracking-wider rounded">Purchased</span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2 leading-tight"><?= h($product['title']) ?></h1>
                    <p class="text-3xl font-black text-brand-600 mb-6">R <?= number_format($product['price'], 2) ?></p>
                    
                    <p class="text-slate-600 mb-8 leading-relaxed"><?= nl2br(h($product['description'])) ?></p>
                    
                    <!-- Add to Cart or Access Button -->
                    <div class="mt-auto pt-6 border-t border-slate-100 pb-8">
                        <?php if ($isPurchased): ?>
                            <a href="download.php?product_id=<?= $product['id'] ?>" class="w-full py-4 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                                Access Resource
                            </a>
                        <?php else: ?>
                            <div class="flex gap-4 items-center">
                                <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden">
                                    <button onclick="decrementQty()" class="px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold border-r border-slate-200">-</button>
                                    <input type="number" id="pdp-qty" value="1" min="1" class="w-16 text-center font-bold text-slate-900 outline-none p-0 border-transparent focus:ring-0">
                                    <button onclick="incrementQty()" class="px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold border-l border-slate-200">+</button>
                                </div>
                                <a href="product.php?add=1&id=<?= $product['id'] ?>" onclick="event.preventDefault(); addToCartWithQty(<?= $product['id'] ?>)" class="flex-1 py-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    Add to Cart
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Accordion Sections -->
                    <div class="divide-y divide-slate-100 border-t border-slate-100">
                        <details class="group py-4" <?= isset($_GET['details']) ? 'open' : '' ?>>
                            <summary class="flex justify-between items-center font-bold cursor-pointer list-none">
                                <span>Key Features</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <div class="text-slate-600 mt-3 text-sm">
                                <?php
                                // Sample features - could be stored in DB, but for demo we provide generic
                                $features = [
                                    "High-quality digital content",
                                    "Instant access after purchase",
                                    "Lifetime access",
                                    "Printable format (PDF)"
                                ];
                                ?>
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($features as $feature): ?>
                                        <li><?= h($feature) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </details>
                        <details class="group py-4">
                            <summary class="flex justify-between items-center font-bold cursor-pointer list-none">
                                <span>Specifications</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <div class="text-slate-600 mt-3 space-y-2 text-sm">
                                <p><span class="font-bold text-slate-900">Format:</span> <?= strtoupper($product['file_type'] === 'pdf' ? 'PDF Document' : 'External Link') ?></p>
                                <p><span class="font-bold text-slate-900">Delivery:</span> <?= $product['file_type'] === 'pdf' ? 'Instant download after purchase' : 'External link provided after purchase' ?></p>
                                <p><span class="font-bold text-slate-900">License:</span> Personal use only</p>
                            </div>
                        </details>
                        <details class="group py-4">
                            <summary class="flex justify-between items-center font-bold cursor-pointer list-none">
                                <span>Shipping & Info</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            <p class="text-slate-600 mt-3 text-sm">
                                <?= $product['file_type'] === 'pdf' 
                                    ? 'This is a digital product. A download link will be available immediately after checkout.' 
                                    : 'This is an external link resource. The link will be revealed after purchase.' ?>
                            </p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function incrementQty() {
            let qtyInput = document.getElementById('pdp-qty');
            let currentVal = parseInt(qtyInput.value);
            if (!isNaN(currentVal)) {
                qtyInput.value = currentVal + 1;
            }
        }
        function decrementQty() {
            let qtyInput = document.getElementById('pdp-qty');
            let currentVal = parseInt(qtyInput.value);
            if (!isNaN(currentVal) && currentVal > 1) {
                qtyInput.value = currentVal - 1;
            }
        }
        function addToCartWithQty(productId) {
            let qty = parseInt(document.getElementById('pdp-qty').value);
            // Use POST or GET? We'll redirect with quantity parameter (simplified)
            window.location.href = 'product.php?add=1&id=' + productId + '&qty=' + qty;
        }
        // Override add to cart handling if qty specified
        <?php if (isset($_GET['qty']) && is_numeric($_GET['qty']) && isset($_GET['add']) && !$isPurchased): ?>
            // Remove the product from cart first? Actually addToCart function handles increments.
            // This is processed via PHP before page load.
        <?php endif; ?>
    </script>
    <?php
    // Handle add with quantity (if qty parameter present)
    if (isset($_GET['add']) && isset($_GET['qty']) && is_numeric($_GET['qty']) && !$isPurchased) {
        $qty = (int)$_GET['qty'];
        addToCart($productId, $qty);
        set_flash('success', 'Product added to cart!');
        redirect('product.php?id=' . $productId);
    }
    ?>
</body>
</html>