<?php
require_once 'config/db.php';
require_once 'config/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$tokenData = $token ? getPasswordResetToken($token) : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!$tokenData) {
        $error = 'This reset link is invalid or has expired.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            if (updateUserPassword((int)$tokenData['user_id'], $newPassword) && markPasswordResetTokenUsed($token)) {
                set_flash('success', 'Password updated successfully. Please log in.');
                redirect('login.php');
            }
            $error = 'Unable to update your password. Please try again.';
        } catch (Throwable $e) {
            $error = 'Unable to update your password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Fun Maths Mastery</title>
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
<body class="bg-slate-50 font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-xl p-8">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center justify-center">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-16 h-16">
            </a>
            <h1 class="mt-4 text-3xl font-extrabold text-slate-900">Set a new password</h1>
            <p class="mt-2 text-slate-500">Choose a password you haven’t used before.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!$tokenData): ?>
            <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                The reset link is invalid or expired. Please request a new one.
            </div>
            <div class="mt-6 text-center">
                <a href="forgot-password.php" class="font-semibold text-brand-600 hover:text-brand-700">Request a new reset link</a>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-5">
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                    <input type="password" name="new_password" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none">
                </div>
                <button type="submit" class="w-full py-3 px-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-all">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
