<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Ensure only admin can access
if (!isAdmin()) {
    redirect('../login.php');
}

// Fetch statistics
// Package-based user counts
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$totalStudents = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'collaborator'");
$totalCollaborators = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'tutor'");
$totalTutors = $stmt->fetchColumn();

// Revenue from completed orders
$stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'completed'");
$totalRevenue = $stmt->fetchColumn();
$totalRevenue = $totalRevenue ?: 0;

// Recent orders (last 5)
$stmt = $pdo->prepare("
    SELECT o.id, o.order_date, o.total, o.status, u.name as user_name, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent product sales (last 5 order items with product names)
$stmt = $pdo->prepare("
    SELECT oi.id, oi.created_at, p.title as product_name, oi.price, u.name as user_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    JOIN users u ON o.user_id = u.id
    ORDER BY oi.id DESC
    LIMIT 5
");
$stmt->execute();
$recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                            900: '#e35b35',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Admin Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <img src="../assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                    <span class="font-bold text-slate-900 hidden sm:inline">Admin Panel</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($_SESSION['user_name'] ?? 'Admin') ?></span>
                <a href="../logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Admin Navigation (simple tabs) -->
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 -mb-px">
                <a href="dashboard.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-semibold border-b-2 border-brand-600 text-brand-600">Dashboard</a>
                <a href="collaborators.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent">Collaborators</a>
                <a href="tutors.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent">Tutors</a>
                <a href="products.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent">Products</a>
                <a href="orders.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent">Orders</a>
                <a href="users.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2 border-transparent">Users</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-brand-100 text-brand-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">Students</p>
                        <p class="text-2xl font-extrabold text-slate-900"><?= $totalStudents ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-brand-100 text-brand-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">Collaborators</p>
                        <p class="text-2xl font-extrabold text-slate-900"><?= $totalCollaborators ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-brand-100 text-brand-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">Tutors</p>
                        <p class="text-2xl font-extrabold text-slate-900"><?= $totalTutors ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-brand-100 text-brand-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                        <p class="text-2xl font-extrabold text-slate-900">R <?= number_format($totalRevenue, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two column layout: Recent Orders & Recent Sales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900">Recent Orders</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php if (empty($recentOrders)): ?>
                                <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No orders yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">#<?= $order['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= h($order['user_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">R <?= number_format($order['total'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?= ucfirst($order['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-right">
                    <a href="orders.php" class="text-sm font-medium text-brand-600 hover:text-brand-800">View all orders →</a>
                </div>
            </div>

            <!-- Recent Sales (product sales) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900">Recent Product Sales</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php if (empty($recentSales)): ?>
                                <tr><td colspan="4" class="px-6 py-4 text-center text-slate-500">No sales yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?= h($sale['product_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= h($sale['user_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">R <?= number_format($sale['price'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= date('d M Y', strtotime($sale['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-right">
                    <a href="orders.php" class="text-sm font-medium text-brand-600 hover:text-brand-800">View all orders →</a>
                </div>
            </div>
        </div>

        <!-- Package Control -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Students</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Learner access</h2>
                <p class="mt-3 text-sm text-slate-500">Monitor student accounts and upgrade selected learners into collaborators.</p>
                <a href="users.php" class="mt-5 inline-flex px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">Manage Students</a>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Collaborators</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Content partners</h2>
                <p class="mt-3 text-sm text-slate-500">Invite collaborators, reset passwords, or promote existing students to contributor access.</p>
                <a href="collaborators.php" class="mt-5 inline-flex px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Open Collaborators</a>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Tutors</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">1-on-1 teaching team</h2>
                <p class="mt-3 text-sm text-slate-500">Invite tutors, manage access, and keep the teaching roster aligned with the pricing package.</p>
                <a href="tutors.php" class="mt-5 inline-flex px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Open Tutors</a>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Materials</p>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Grant access</h2>
                <p class="mt-3 text-sm text-slate-500">Assign resources to students from the admin or collaborator workflow.</p>
                <a href="assign-materials.php" class="mt-5 inline-flex px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">Assign Materials</a>
            </div>
        </div>
    </main>
</body>
</html>
