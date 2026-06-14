<?php
require_once 'config/db.php';
require_once 'config/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$message = '';
$error = '';
$email = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $user = getUserByEmail($email);
            if ($user) {
                $token = createPasswordResetToken((int)$user['id']);
                $resetLink = 'reset-password.php?token=' . urlencode($token);
                $message = 'If that email exists in our system, a reset link has been generated below.';
            } else {
                $message = 'If that email exists in our system, a reset link has been generated below.';
            }
        } catch (Throwable $e) {
            $error = 'Unable to start the reset flow right now. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Fun Maths Mastery</title>
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
            <h1 class="mt-4 text-3xl font-extrabold text-slate-900">Reset your password</h1>
            <p class="mt-2 text-slate-500">Enter your email and we’ll generate a reset link.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-5 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none">
            </div>
            <button type="submit" class="w-full py-3 px-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-all">Generate Reset Link</button>
        </form>

        <?php if ($resetLink): ?>
            <div class="mt-6 p-4 rounded-lg bg-slate-50 border border-slate-200">
                <p class="text-sm font-semibold text-slate-900 mb-2">Reset link</p>
                <a href="<?= h($resetLink) ?>" class="text-brand-600 break-all underline"><?= h($resetLink) ?></a>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center text-sm">
            <a href="login.php" class="font-semibold text-brand-600 hover:text-brand-700">Back to login</a>
        </div>
    </div>
</body>
</html>
