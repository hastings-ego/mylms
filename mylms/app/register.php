<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';
$name = '';
$email = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered. Please login instead.';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $hashed_password]);

                set_flash('success', 'Registration successful. You can now log in.');
                redirect('login.php');
            } catch (Throwable $e) {
                $error = 'Registration failed. Please try again.';
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
    <title>Register | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white font-sans antialiased h-screen overflow-hidden flex">

    <!-- Left Side: Interactive / Decorative (same as login) -->
    <div class="hidden lg:flex w-1/2 bg-slate-900 border-r border-slate-200 relative items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30"></div>
        <div class="absolute top-1/3 right-1/4 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30"></div>

        <div class="relative z-10 w-full max-w-md p-10 bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 text-white shadow-2xl">
            <h2 class="text-3xl font-extrabold mb-4">"Mathematics is not about numbers, equations, computations, or algorithms: it is about understanding."</h2>
            <p class="text-slate-400">— William Paul Thurston</p>
            <div class="mt-8 flex gap-4">
                <div class="w-12 h-12 bg-brand-600 rounded-full flex items-center justify-center font-bold text-lg">+</div>
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center font-bold text-lg">÷</div>
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center font-bold text-lg">√</div>
            </div>
        </div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-32 relative bg-white">
        <!-- Mobile Logo -->
        <div class="absolute top-8 left-8 lg:hidden">
            <a href="index.php" class="flex items-center gap-2">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </a>
        </div>

        <div class="w-full max-w-sm mx-auto z-10">
            <!-- Header -->
            <div class="mb-10 lg:mb-12 text-center lg:text-left">
                <div class="hidden lg:flex items-center gap-2 mb-8">
                    <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Create an account.</h1>
                <p class="text-slate-500">Start your maths mastery journey today.</p>
            </div>

            <!-- Success Message -->
            <?php $flash = get_flash(); if ($flash && $flash['type'] === 'success'): ?>
                <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    <?= h($flash['message']) ?> <a href="login.php" class="font-bold underline">Login here</a>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="" class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?= h($name) ?>"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="Jane Doe" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= h($email) ?>"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="jane@example.com" required>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password * (min. 6 characters)</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="••••••••" required>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="••••••••" required>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 focus:ring-4 focus:ring-brand-500/30 transition-all shadow-lg shadow-brand-500/30">
                    Register
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600">
                    Already have an account?
                    <a href="login.php" class="font-bold text-brand-600 hover:text-brand-700">Sign in</a>
                </p>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <div class="flex justify-center text-xs text-slate-400 gap-4">
                    <a href="terms.php" class="hover:text-slate-600">Terms of Service</a>
                    <a href="privacy.php" class="hover:text-slate-600">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
