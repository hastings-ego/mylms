<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Remember me: set cookie for 30 days
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30); // 30 days
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
                // Store token in database (optional but more secure)
                try {
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                } catch (Throwable $e) {
                    // If remember_token is unavailable for any reason, keep the login alive.
                }
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Fun Maths Mastery</title>
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

    <!-- Left Side: Interactive / Decorative -->
    <div class="hidden lg:flex w-1/2 bg-slate-900 border-r border-slate-200 relative items-center justify-center overflow-hidden">
        <!-- Abstract Math Decorations -->
        <div class="absolute inset-0 z-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-1/3 right-1/4 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

        <!-- Showcase Card -->
        <div class="relative z-10 w-full max-w-md p-10 bg-white/5 backdrop-blur-lg rounded-2xl border border-white/10 text-white shadow-2xl">
            <h2 class="text-3xl font-extrabold mb-4">"The only way to learn mathematics is to do mathematics."</h2>
            <p class="text-slate-400">— Paul Halmos</p>
            <div class="mt-8 flex gap-4">
                <div class="w-12 h-12 bg-brand-600 rounded-full flex items-center justify-center font-bold text-lg">&Sigma;</div>
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center font-bold text-lg">&int;</div>
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center font-bold text-lg">&pi;</div>
            </div>
        </div>
    </div>

    <!-- Right Side: Form -->
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
                <?php $flash = get_flash(); if ($flash && $flash['type'] === 'success'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        <?= h($flash['message']) ?>
                    </div>
                <?php endif; ?>
                <div class="hidden lg:flex items-center gap-2 mb-8">
                    <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Welcome back.</h1>
                <p class="text-slate-500">Log in to view your progress and resources.</p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= h($email) ?>"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="jane@example.com" required>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                        <a href="forgot-password.php" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none transition-all"
                        placeholder="••••••••" required>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                        class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                    <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer">Remember me for 30 days</label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 focus:ring-4 focus:ring-brand-500/30 transition-all shadow-lg shadow-brand-500/30 flex justify-center items-center gap-2">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-600">
                    Don't have an account yet?
                    <a href="register.php" class="font-bold text-brand-600 hover:text-brand-700">Enroll today</a>
                </p>
                <div class="mt-4 flex justify-center text-xs text-slate-400 gap-4">
                    <a href="terms.php" class="hover:text-slate-600">Terms of Service</a>
                    <a href="privacy.php" class="hover:text-slate-600">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
