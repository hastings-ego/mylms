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
$user = getUserById($userId);
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($name) || empty($email)) {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                // Check if email already taken by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $error = 'This email is already in use by another account.';
                } else {
                    if (updateUserProfile($userId, $name, $email)) {
                        $_SESSION['user_name'] = $name;
                        $success = 'Profile updated successfully.';
                        // Refresh user data
                        $user = getUserById($userId);
                    } else {
                        $error = 'Failed to update profile. Please try again.';
                    }
                }
            }
        } elseif ($action === 'update_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'Please fill in all password fields.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters long.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($currentPassword, $userData['password'])) {
                    if (updateUserPassword($userId, $newPassword)) {
                        $success = 'Password changed successfully.';
                    } else {
                        $error = 'Failed to update password. Please try again.';
                    }
                } else {
                    $error = 'Current password is incorrect.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                <a href="dashboard.php" class="flex items-center gap-2">
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
                        Student Dashboard
                    </a>
                    <a href="my-courses.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        All My Courses
                    </a>
                    <a href="library.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Referencing & Sheets
                    </a>
                    <a href="tutoring.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        1-on-1 Math Tutors
                    </a>
                    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-lg bg-brand-50 text-brand-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Account Settings
                    </a>
                    <div class="mt-8 mb-2 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-t border-slate-100 pt-6">Quick Links</div>
                    <a href="store.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 justify-between">
                        <span class="flex items-center gap-3"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>Student Store</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </nav>
            </div>
            <div class="p-4 border-t border-slate-200">
                <div class="bg-slate-50 rounded-lg p-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold"><?= strtoupper(substr($_SESSION['user_name'], 0, 2)) ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-slate-900 truncate"><?= h($_SESSION['user_name']) ?></div>
                        <div class="text-xs text-slate-500 truncate">Grade 11 Learner</div>
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
                <button class="relative p-2 text-slate-400 hover:text-brand-600 transition-colors rounded-full hover:bg-slate-50" onclick="alert('No new notifications.')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
                </button>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-600 hover:text-brand-600 bg-slate-50 rounded-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </header>

        <div class="p-6 md:p-10 lg:p-12 max-w-6xl mx-auto w-full animate-fade-in">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Account Settings</h1>
            <p class="text-slate-500 mt-2">Manage your profile and subscription preferences.</p>

            <!-- Flash Messages -->
            <?php if ($success): ?>
                <div class="mt-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"><?= h($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mt-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800"><?= h($error) ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <!-- Profile Management -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Personal Details</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                    <input type="text" name="name" value="<?= h($user['name'] ?? '') ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                                    <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Grade / Math Level</label>
                                <select name="grade" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" disabled>
                                    <option value="10">Grade 10</option>
                                    <option value="11" selected>Grade 11</option>
                                    <option value="12">Grade 12</option>
                                    <option value="uni">University Freshman</option>
                                </select>
                                <p class="text-xs text-slate-500 mt-2">Grade level management coming soon.</p>
                            </div>
                            <div class="pt-4 border-t border-slate-100 flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Change Password</h3>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="update_password">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Current Password</label>
                                <input type="password" name="current_password" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">New Password (min. 6 characters)</label>
                                <input type="password" name="new_password" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                            </div>
                            <div class="pt-4 border-t border-slate-100 flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Subscription Management -->
                <div class="space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-brand-500 rounded-full opacity-20 blur-xl"></div>
                        <h3 class="text-lg font-bold mb-6 text-brand-400">Current Plan</h3>
                        <div class="mb-6">
                            <div class="text-2xl font-black mb-1">Pro Mastery</div>
                            <p class="text-slate-400 text-sm">Access to all modules & 1 live session/mo.</p>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 mb-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-300">Renews</span>
                                <span class="font-bold">June 12, 2026</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-300">Amount</span>
                                <span class="font-bold">R 450.00 / mo</span>
                            </div>
                        </div>
                        <button onclick="alert('Subscription management coming soon. Please contact support.')" class="w-full py-3 bg-white text-slate-900 font-bold rounded-lg hover:bg-slate-100 transition-colors mb-3">Manage Subscription</button>
                        <button onclick="alert('Payment history coming soon.')" class="w-full py-2 text-slate-400 hover:text-white text-sm font-medium transition-colors">View Payment History</button>
                    </div>
                </div>
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