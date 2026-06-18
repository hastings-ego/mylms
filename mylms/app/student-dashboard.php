<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Redirect if not logged in or is admin
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

// Handle class enrollment
if (isset($_GET['action']) && $_GET['action'] === 'enroll' && isset($_GET['class_id'])) {
    $classId = (int)$_GET['class_id'];
    if (enrollStudentInClass($userId, $classId)) {
        set_flash('success', 'Successfully enrolled in class!');
    } else {
        set_flash('error', 'Already enrolled or enrollment failed.');
    }
    redirect('student-dashboard.php?tab=classes');
}

// Handle class unenrollment
if (isset($_GET['action']) && $_GET['action'] === 'unenroll' && isset($_GET['class_id'])) {
    $classId = (int)$_GET['class_id'];
    unenrollStudentFromClass($userId, $classId);
    set_flash('success', 'Unenrolled from class.');
    redirect('student-dashboard.php?tab=classes');
}

// Handle support ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_ticket') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($subject) || empty($message)) {
            set_flash('error', 'Please fill in all fields.');
        } else {
            if (createSupportTicket($userId, $subject, $message)) {
                set_flash('success', 'Support ticket created. We\'ll get back to you soon!');
            } else {
                set_flash('error', 'Failed to create support ticket.');
            }
        }
    }
    redirect('student-dashboard.php?tab=support');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name) || empty($email)) {
            set_flash('error', 'Please fill in all fields.');
        } else {
            if (updateUserProfile($userId, $name, $email)) {
                $_SESSION['user_name'] = $name;
                set_flash('success', 'Profile updated successfully.');
            } else {
                set_flash('error', 'Failed to update profile.');
            }
        }
    }
    redirect('student-dashboard.php?tab=settings');
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            set_flash('error', 'Please fill in all fields.');
        } elseif ($newPassword !== $confirmPassword) {
            set_flash('error', 'New passwords do not match.');
        } elseif (strlen($newPassword) < 6) {
            set_flash('error', 'Password must be at least 6 characters.');
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                if (updateUserPassword($userId, $newPassword)) {
                    set_flash('success', 'Password changed successfully.');
                } else {
                    set_flash('error', 'Failed to change password.');
                }
            } else {
                set_flash('error', 'Current password is incorrect.');
            }
        }
    }
    redirect('student-dashboard.php?tab=settings');
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Get data for different tabs
$purchased = getUserPurchasedProducts($userId);
$enrolledClasses = getStudentEnrolledClasses($userId);
$availableClasses = getAvailableClassesForStudent($userId, 20);
$supportTickets = getStudentSupportTickets($userId);
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button.active { @apply border-b-2 border-brand-600 text-brand-600; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                <span class="font-bold text-slate-900 hidden sm:inline">Student Dashboard</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($userName) ?></span>
                <a href="logout.php" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-red-600 transition-colors">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php $flash = get_flash(); if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Tab Navigation -->
        <div class="bg-white rounded-t-lg border-b border-slate-200 flex gap-8 px-6 sticky top-16 z-40">
            <button class="tab-button <?= $activeTab === 'classes' ? 'active' : '' ?> py-4 font-semibold text-slate-600 hover:text-brand-600 transition-colors" onclick="switchTab('classes')">
                📚 Classes
            </button>
            <button class="tab-button <?= $activeTab === 'materials' ? 'active' : '' ?> py-4 font-semibold text-slate-600 hover:text-brand-600 transition-colors" onclick="switchTab('materials')">
                📖 My Materials
            </button>
            <button class="tab-button <?= $activeTab === 'settings' ? 'active' : '' ?> py-4 font-semibold text-slate-600 hover:text-brand-600 transition-colors" onclick="switchTab('settings')">
                ⚙️ Settings
            </button>
            <button class="tab-button <?= $activeTab === 'support' ? 'active' : '' ?> py-4 font-semibold text-slate-600 hover:text-brand-600 transition-colors" onclick="switchTab('support')">
                💬 Support
            </button>
        </div>

        <!-- Classes Tab -->
        <div id="classes" class="tab-content <?= $activeTab === 'classes' ? 'active' : '' ?> bg-white rounded-b-lg p-6 border border-slate-200">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-brand-50 to-brand-100 rounded-lg p-6 border border-brand-200">
                    <div class="text-3xl font-bold text-brand-600"><?= count($enrolledClasses) ?></div>
                    <div class="text-sm text-slate-600 mt-1">Enrolled Classes</div>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg p-6 border border-slate-200">
                    <div class="text-3xl font-bold text-slate-600"><?= count($availableClasses) ?></div>
                    <div class="text-sm text-slate-600 mt-1">Available Classes</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <div class="text-3xl font-bold text-green-600"><?= count($supportTickets) ?></div>
                    <div class="text-sm text-slate-600 mt-1">Support Tickets</div>
                </div>
            </div>

            <!-- Enrolled Classes Section -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-900 mb-4">My Enrolled Classes</h3>
                <?php if (empty($enrolledClasses)): ?>
                    <div class="text-center py-12 bg-slate-50 rounded-lg">
                        <p class="text-slate-500">No classes enrolled yet. Browse available classes below!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($enrolledClasses as $class): ?>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-slate-900"><?= h($class['title']) ?></h4>
                                        <p class="text-sm text-slate-600 mt-1">Instructor: <?= h($class['tutor_name']) ?></p>
                                        <p class="text-sm text-slate-600"><?= date('M d, Y H:i', strtotime($class['start_at'])) ?></p>
                                        <p class="text-xs text-slate-500 mt-2"><?= $class['student_count'] ?? 0 ?> students enrolled</p>
                                    </div>
                                    <a href="?action=unenroll&class_id=<?= $class['id'] ?>&tab=classes" class="text-red-600 hover:text-red-800 text-sm font-medium">Leave</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Classes Section -->
            <div>
                <h3 class="text-lg font-bold text-slate-900 mb-4">Discover More Classes</h3>
                <?php if (empty($availableClasses)): ?>
                    <div class="text-center py-12 bg-slate-50 rounded-lg">
                        <p class="text-slate-500">No classes available at the moment. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($availableClasses as $class): ?>
                            <div class="bg-white rounded-lg p-4 border border-slate-200 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-slate-900"><?= h($class['title']) ?></h4>
                                        <p class="text-sm text-slate-600 mt-1">Instructor: <?= h($class['tutor_name']) ?></p>
                                        <p class="text-sm text-brand-600 font-medium"><?= date('M d, Y H:i', strtotime($class['start_at'])) ?></p>
                                        <p class="text-xs text-slate-500 mt-2"><?= $class['student_count'] ?? 0 ?> students enrolled</p>
                                        <p class="text-sm text-slate-600 mt-2"><?= substr(h($class['description'] ?? ''), 0, 100) ?>...</p>
                                    </div>
                                </div>
                                <a href="?action=enroll&class_id=<?= $class['id'] ?>&tab=classes" class="inline-block mt-4 w-full text-center px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-semibold">Enroll Now</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Materials Tab -->
        <div id="materials" class="tab-content <?= $activeTab === 'materials' ? 'active' : '' ?> bg-white rounded-b-lg p-6 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">My Purchased Materials</h3>
            <?php if (empty($purchased)): ?>
                <div class="text-center py-12 bg-slate-50 rounded-lg">
                    <p class="text-slate-500 mb-4">You haven't purchased any materials yet.</p>
                    <a href="store.php" class="inline-block px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Browse Store</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($purchased as $product): ?>
                        <div class="bg-white rounded-lg p-4 border border-slate-200 hover:shadow-lg transition-shadow">
                            <div class="flex gap-4">
                                <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                    <img src="<?= h($product['image_path']) ?>" alt="<?= h($product['title']) ?>" class="w-20 h-20 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-slate-100 rounded flex items-center justify-center text-slate-400">📄</div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-900"><?= h($product['title']) ?></h4>
                                    <p class="text-sm text-slate-600 mt-1"><?= h($product['category']) ?></p>
                                    <p class="text-sm text-slate-500 mt-2">R <?= number_format($product['price'], 2) ?></p>
                                    <p class="text-xs text-slate-400 mt-1">Purchased: <?= date('M d, Y', strtotime($product['purchase_date'])) ?></p>
                                </div>
                            </div>
                            <a href="product.php?id=<?= $product['id'] ?>" class="inline-block mt-3 px-4 py-1.5 text-sm bg-brand-100 text-brand-600 rounded hover:bg-brand-200">View Material</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Settings Tab -->
        <div id="settings" class="tab-content <?= $activeTab === 'settings' ? 'active' : '' ?> bg-white rounded-b-lg p-6 border border-slate-200">
            <div class="max-w-2xl">
                <!-- Profile Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Account Profile</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="<?= h($currentUser['name']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" value="<?= h($currentUser['email']) ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-semibold">Update Profile</button>
                    </form>
                </div>

                <!-- Password Section -->
                <div class="border-t border-slate-200 pt-8">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Change Password</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-semibold">Change Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Support Tab -->
        <div id="support" class="tab-content <?= $activeTab === 'support' ? 'active' : '' ?> bg-white rounded-b-lg p-6 border border-slate-200">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Create Ticket Form -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Create Support Ticket</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="create_ticket">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Subject</label>
                            <input type="text" name="subject" placeholder="What can we help you with?" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Message</label>
                            <textarea name="message" rows="5" placeholder="Describe your issue in detail..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required></textarea>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-semibold">Submit Ticket</button>
                    </form>
                </div>

                <!-- Support Tickets History -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Your Support Tickets</h3>
                    <?php if (empty($supportTickets)): ?>
                        <div class="text-center py-8 bg-slate-50 rounded-lg">
                            <p class="text-slate-500">No support tickets yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($supportTickets as $ticket): ?>
                                <div class="border border-slate-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-semibold text-slate-900"><?= h($ticket['subject']) ?></h4>
                                        <span class="px-2 py-1 text-xs rounded-full <?= 
                                            $ticket['status'] === 'resolved' ? 'bg-green-100 text-green-800' :
                                            ($ticket['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')
                                        ?>">
                                            <?= ucfirst($ticket['status']) ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-2"><?= h(substr($ticket['message'], 0, 100)) ?>...</p>
                                    <p class="text-xs text-slate-400 mt-2"><?= date('M d, Y', strtotime($ticket['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Update URL
            window.history.pushState({}, '', `?tab=${tabName}`);
        }
    </script>

</body>
</html>
