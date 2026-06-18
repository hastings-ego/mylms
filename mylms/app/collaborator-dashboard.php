<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Redirect if not a collaborator or admin
$role = $_SESSION['role'] ?? 'student';
if ($role !== 'collaborator' && !isAdmin()) {
    set_flash('error', 'You do not have permission to access this page.');
    redirect('dashboard.php');
}

if (isAdmin()) {
    redirect('admin/dashboard.php');
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get all products in the system (admin/collaborators can see and manage all products or just theirs)
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$totalProducts = count($allProducts);
$activeProducts = count(array_filter($allProducts, function($p) { return $p['is_active']; }));
$totalValue = array_sum(array_column($allProducts, 'price'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaborator Dashboard | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 900: '#312e81' } }
                }
            }
        }
    </script>
    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        #sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 1024px) {
            #sidebar.open { transform: translateX(0); }
            #sidebar.closed { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased h-screen overflow-hidden flex">

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 z-20 hidden lg:hidden transition-opacity opacity-0 pointer-events-none" onclick="toggleSidebar()"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="w-72 bg-white border-r border-slate-200 flex flex-col justify-between z-30 fixed lg:static h-full closed shadow-2xl lg:shadow-none transform -translate-x-full lg:translate-x-0">
        <div class="flex flex-col h-full">
            <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center">
                <a href="collaborator-dashboard.php" class="flex items-center gap-2">
                    <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
                </a>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-6 flex flex-col">
                <nav class="space-y-2 flex-1">
                    <a href="dashboard.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Back to Dashboard
                    </a>
                    <a href="collaborator-dashboard.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-lg bg-brand-50 text-brand-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Content Management
                    </a>
                    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Account Settings
                    </a>
                </nav>
            </div>
            <div class="p-4 border-t border-slate-200">
                <div class="bg-slate-50 rounded-lg p-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold"><?= strtoupper(substr($userName, 0, 2)) ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-slate-900 truncate"><?= h($userName) ?></div>
                        <div class="text-xs text-slate-500 truncate"><?= ucfirst($role) ?></div>
                    </div>
                </div>
                <a href="logout.php" class="w-full mt-3 text-center text-sm font-medium text-slate-500 hover:text-red-600 transition-colors block">Sign Out</a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto flex flex-col relative w-full">
        <header class="bg-white border-b border-slate-200 px-4 py-3 lg:py-5 lg:px-12 flex items-center justify-between sticky top-0 z-10">
            <div class="hidden lg:block"><p class="text-sm text-slate-500 font-bold" id="header-date"></p></div>
            <div class="flex items-center gap-4 ml-auto">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-600 hover:text-brand-600 bg-slate-50 rounded-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </header>

        <div class="p-6 md:p-10 lg:p-12 max-w-6xl mx-auto w-full animate-fade-in">
            <header class="mb-8 md:mb-10">
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Content Management</h1>
                <p class="text-slate-500 mt-2">Manage your products and track performance.</p>
            </header>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Products</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $totalProducts ?></p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Active Products</p>
                    <p class="mt-2 text-3xl font-extrabold text-brand-600"><?= $activeProducts ?></p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Value</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900">R <?= number_format($totalValue, 2) ?></p>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900">Your Products</h2>
                    <a href="../admin/products.php" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700">+ Manage in Admin</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Image</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php if (empty($allProducts)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        No products yet. <a href="../admin/products.php" class="text-brand-600 hover:text-brand-700 font-semibold">Create your first product →</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allProducts as $product): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-slate-900"><?= h($product['title']) ?></div>
                                            <div class="text-xs text-slate-500">ID #<?= $product['id'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= h($product['category']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">R <?= number_format($product['price'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full <?= $product['file_type'] === 'pdf' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800' ?>">
                                                <?= strtoupper($product['file_type']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                                <div class="w-10 h-10 bg-slate-100 rounded overflow-hidden">
                                                    <img src="<?= h($product['image_path']) ?>" alt="Product" class="w-full h-full object-cover">
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400">No image</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="../admin/products.php" class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg bg-white hover:border-brand-300 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-brand-50 rounded-lg flex items-center justify-center text-brand-600 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Add New Product</h3>
                        <p class="text-sm text-slate-500">Create and upload new content</p>
                    </div>
                </a>
                <a href="store.php" class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg bg-white hover:border-brand-300 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-brand-50 rounded-lg flex items-center justify-center text-brand-600 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">View Store</h3>
                        <p class="text-sm text-slate-500">See how your products appear</p>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const isOpen = sidebar.classList.contains('open');
            if (!isOpen) {
                sidebar.classList.remove('closed', '-translate-x-full');
                sidebar.classList.add('open', 'translate-x-0');
                overlay.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100');
            } else {
                sidebar.classList.remove('open', 'translate-x-0');
                sidebar.classList.add('closed', '-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const dateStr = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            const dateEl = document.getElementById('header-date');
            if (dateEl) dateEl.innerText = dateStr;
        });
    </script>
</body>
</html>
