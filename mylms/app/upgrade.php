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

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'student';

// Define upgrade options
$upgradeOptions = [
    'collaborator' => [
        'title' => 'Contributors Package',
        'description' => 'Upload your own content, monetize your materials, and create worksheets.',
        'features' => [
            'Upload your own content',
            'Monetize your materials',
            'Create and manage worksheets',
            'Access analytics dashboard'
        ],
        'price' => 999.99,
        'from_roles' => ['student']
    ],
    'tutor' => [
        'title' => 'Tutor Package',
        'description' => 'Offer 1-on-1 tutoring sessions and host your own classroom.',
        'features' => [
            '1-on-1 tutoring sessions',
            'Host one classroom',
            'Schedule live classes',
            'Lifetime updates',
            'Priority support'
        ],
        'price' => 499.99,
        'from_roles' => ['student', 'collaborator']
    ]
];

// Check if user is trying to upgrade to a specific role
$targetRole = isset($_GET['role']) ? $_GET['role'] : null;

// Validate the target role
if ($targetRole && !isset($upgradeOptions[$targetRole])) {
    set_flash('error', 'Invalid upgrade option.');
    redirect('dashboard.php');
}

// Check if user can upgrade to this role
if ($targetRole && !in_array($userRole, $upgradeOptions[$targetRole]['from_roles'])) {
    set_flash('error', 'You are not eligible for this upgrade.');
    redirect('dashboard.php');
}

// Check if user already has this role
if ($targetRole && $userRole === $targetRole) {
    set_flash('error', 'You already have this role.');
    redirect('dashboard.php');
}

// Process upgrade payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect('upgrade.php');
    }

    $selectedRole = $_POST['upgrade_role'] ?? null;

    if (!$selectedRole || !isset($upgradeOptions[$selectedRole])) {
        set_flash('error', 'Please select a valid upgrade option.');
        redirect('upgrade.php');
    }

    if (!in_array($userRole, $upgradeOptions[$selectedRole]['from_roles'])) {
        set_flash('error', 'You are not eligible for this upgrade.');
        redirect('upgrade.php');
    }

    try {
        $pdo->beginTransaction();

        // Get upgrade price
        $upgradePrice = $upgradeOptions[$selectedRole]['price'];
        $paymentReference = 'UPGRADE-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
        $paymentStatus = 'paid';

        // Create an order for the upgrade (for record-keeping)
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total, status, order_date, payment_method, payment_status, payment_reference)
            VALUES (?, ?, 'completed', CURRENT_TIMESTAMP, 'yoco', ?, ?)
        ");
        $stmt->execute([$userId, $upgradePrice, $paymentStatus, $paymentReference]);
        $orderId = $pdo->lastInsertId();

        // Create role upgrade record
        $stmt = $pdo->prepare("
            INSERT INTO role_upgrades (user_id, from_role, to_role, price, order_id, payment_reference, payment_status, upgraded_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$userId, $userRole, $selectedRole, $upgradePrice, $orderId, $paymentReference, 'paid']);

        // Update user's role
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$selectedRole, $userId]);

        // Update session role
        $_SESSION['role'] = $selectedRole;

        $pdo->commit();

        set_flash('success', 'Congratulations! Your account has been upgraded successfully. You now have access to all ' . ucfirst($selectedRole) . ' features.');
        redirect('dashboard.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Upgrade failed: ' . $e->getMessage());
        redirect('upgrade.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Account | Fun Maths Mastery</title>
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
<body class="bg-slate-50 text-slate-900 font-sans antialiased">

    <!-- Navigation -->
    <nav class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="inline-flex items-center justify-center w-8 h-8 rounded bg-slate-100 text-slate-500 hover:text-white hover:bg-brand-600 transition-colors" title="Back to Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($_SESSION['user_name'] ?? 'Student') ?></span>
                <a href="logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <!-- Flash Message -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">Upgrade Your Account</h1>
            <p class="text-lg text-slate-600">Unlock premium features to grow as an educator or contributor.</p>
            <p class="text-sm text-slate-500 mt-2">Current Role: <span class="font-semibold text-slate-900"><?= ucfirst($userRole) ?></span></p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <?php foreach ($upgradeOptions as $role => $option): ?>
                <?php if (in_array($userRole, $option['from_roles'])): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-8">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <h2 class="text-2xl font-bold text-slate-900"><?= h($option['title']) ?></h2>
                                    <p class="text-slate-600 mt-2"><?= h($option['description']) ?></p>
                                </div>
                            </div>

                            <div class="mb-6 py-4 border-t border-b border-slate-200">
                                <div class="text-4xl font-extrabold text-brand-600 mb-1">R <?= number_format($option['price'], 2) ?></div>
                                <p class="text-sm text-slate-600">One-time payment</p>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <?php foreach ($option['features'] as $feature): ?>
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-slate-700"><?= h($feature) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="upgrade">
                                <input type="hidden" name="upgrade_role" value="<?= h($role) ?>">

                                <button type="submit" class="w-full py-3 px-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors">
                                    Upgrade to <?= ucfirst($role) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Ineligible upgrades message -->
        <?php
        $ineligibleRoles = array_filter($upgradeOptions, function($option, $role) use ($userRole) {
            return !in_array($userRole, $option['from_roles']);
        }, ARRAY_FILTER_USE_BOTH);
        ?>

        <?php if (!empty($ineligibleRoles)): ?>
            <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Not eligible for other upgrades?</h3>
                <p class="text-blue-800">You may need to upgrade to an intermediate level first. Contact support if you have any questions about upgrade eligibility.</p>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
