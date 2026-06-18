<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}
if (isAdmin()) {
    redirect('admin/dashboard.php');
}
if (($_SESSION['role'] ?? '') === 'tutor') {
    redirect('tutor-dashboard.php');
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get data
try {
    $purchased = getUserPurchasedProducts($userId);
    $enrolledClasses = getStudentEnrolledClasses($userId);
    $availableClasses = getAvailableClassesForStudent($userId, 20);
    $supportTickets = getStudentSupportTickets($userId);
} catch (Exception $e) {
    $purchased = [];
    $enrolledClasses = [];
    $availableClasses = [];
    $supportTickets = [];
}

$activeTab = $_GET['tab'] ?? 'classes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden { display: none !important; }
        .block { display: block !important; }
        .tab-active { border-bottom: 2px solid #4f46e5; color: #4f46e5; }
    </style>
</head>
<body class="bg-slate-50">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
            <div>
                <h1 class="font-bold text-xl text-slate-900">Fun Maths Mastery</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($userName) ?></span>
                <a href="logout.php" class="text-sm text-red-600 hover:text-red-800">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Tabs Navigation -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 flex gap-8">
            <button onclick="showTab('classes')" class="py-4 font-semibold border-b-2 border-transparent hover:border-indigo-600 tab-btn <?= $activeTab === 'classes' ? 'tab-active' : '' ?>" data-tab="classes">
                📚 Classes
            </button>
            <button onclick="showTab('materials')" class="py-4 font-semibold border-b-2 border-transparent hover:border-indigo-600 tab-btn <?= $activeTab === 'materials' ? 'tab-active' : '' ?>" data-tab="materials">
                📖 Materials
            </button>
            <button onclick="showTab('settings')" class="py-4 font-semibold border-b-2 border-transparent hover:border-indigo-600 tab-btn <?= $activeTab === 'settings' ? 'tab-active' : '' ?>" data-tab="settings">
                ⚙️ Settings
            </button>
            <button onclick="showTab('support')" class="py-4 font-semibold border-b-2 border-transparent hover:border-indigo-600 tab-btn <?= $activeTab === 'support' ? 'tab-active' : '' ?>" data-tab="support">
                💬 Support
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Classes Tab -->
        <div id="tab-classes" class="<?= $activeTab !== 'classes' ? 'hidden' : '' ?>">
            <h2 class="text-2xl font-bold mb-6 text-slate-900">My Classes</h2>
            
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-200">
                    <div class="text-3xl font-bold text-indigo-600"><?= count($enrolledClasses) ?></div>
                    <div class="text-sm text-slate-600">Enrolled</div>
                </div>
                <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                    <div class="text-3xl font-bold text-slate-600"><?= count($availableClasses) ?></div>
                    <div class="text-sm text-slate-600">Available</div>
                </div>
                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <div class="text-3xl font-bold text-green-600"><?= count($supportTickets) ?></div>
                    <div class="text-sm text-slate-600">Support Tickets</div>
                </div>
            </div>

            <h3 class="text-lg font-bold mb-4 text-slate-900">Enrolled Classes</h3>
            <?php if (empty($enrolledClasses)): ?>
                <p class="text-slate-500 py-8 text-center bg-slate-50 rounded">No enrolled classes yet</p>
            <?php else: ?>
                <div class="grid gap-4 mb-8">
                    <?php foreach ($enrolledClasses as $class): ?>
                        <div class="bg-white p-4 rounded-lg border border-slate-200">
                            <h4 class="font-bold text-slate-900"><?= h($class['title']) ?></h4>
                            <p class="text-sm text-slate-600">Instructor: <?= h($class['tutor_name']) ?></p>
                            <p class="text-sm text-indigo-600"><?= date('M d, Y H:i', strtotime($class['start_at'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3 class="text-lg font-bold mb-4 text-slate-900">Available Classes</h3>
            <?php if (empty($availableClasses)): ?>
                <p class="text-slate-500 py-8 text-center bg-slate-50 rounded">No available classes</p>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($availableClasses as $class): ?>
                        <div class="bg-white p-4 rounded-lg border border-slate-200 hover:shadow-lg">
                            <h4 class="font-bold text-slate-900"><?= h($class['title']) ?></h4>
                            <p class="text-sm text-slate-600">Instructor: <?= h($class['tutor_name']) ?></p>
                            <p class="text-sm text-indigo-600"><?= date('M d, Y H:i', strtotime($class['start_at'])) ?></p>
                            <p class="text-xs text-slate-500 mt-2"><?= $class['student_count'] ?? 0 ?> students enrolled</p>
                            <a href="?action=enroll&class_id=<?= $class['id'] ?>&tab=classes" class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-semibold">Enroll</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Materials Tab -->
        <div id="tab-materials" class="<?= $activeTab !== 'materials' ? 'hidden' : '' ?>">
            <h2 class="text-2xl font-bold mb-6 text-slate-900">My Materials</h2>
            <?php if (empty($purchased)): ?>
                <p class="text-slate-500 py-8 text-center bg-slate-50 rounded">No purchased materials yet</p>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($purchased as $product): ?>
                        <div class="bg-white p-4 rounded-lg border border-slate-200">
                            <h4 class="font-bold text-slate-900"><?= h($product['title']) ?></h4>
                            <p class="text-sm text-slate-600"><?= h($product['category']) ?></p>
                            <p class="text-sm font-semibold text-slate-900">R<?= number_format($product['price'], 2) ?></p>
                            <a href="product.php?id=<?= $product['id'] ?>" class="inline-block mt-3 px-4 py-2 bg-indigo-100 text-indigo-600 rounded hover:bg-indigo-200 text-sm font-semibold">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Settings Tab -->
        <div id="tab-settings" class="<?= $activeTab !== 'settings' ? 'hidden' : '' ?>">
            <h2 class="text-2xl font-bold mb-6 text-slate-900">Settings</h2>
            <p class="text-slate-600">Profile settings coming soon</p>
        </div>

        <!-- Support Tab -->
        <div id="tab-support" class="<?= $activeTab !== 'support' ? 'hidden' : '' ?>">
            <h2 class="text-2xl font-bold mb-6 text-slate-900">Support</h2>
            <p class="text-slate-600">Support tickets: <?= count($supportTickets) ?></p>
        </div>

    </main>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.add('hidden');
            });
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            
            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active');
            });
            event.target.classList.add('tab-active');
            
            // Update URL
            window.location.href = '?tab=' + tabName;
        }
    </script>

</body>
</html>
