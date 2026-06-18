<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';
$tempPassword = '';
$newInviteEmail = '';

function generateTutorPassword($prefix = 'TUT') {
    return $prefix . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'invite') {
            $name = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = trim($_POST['password'] ?? '');

            if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid name and email address.';
            } else {
                $stmt = $pdo->prepare("SELECT id, role FROM users WHERE lower(email) = lower(?)");
                $stmt->execute([$email]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing && $existing['role'] === 'admin') {
                    $error = 'Admin accounts cannot be converted.';
                } else {
                    if ($password === '') {
                        $password = generateTutorPassword('TUT');
                    }

                    if ($existing) {
                        updateUserRole($existing['id'], 'tutor');
                        if (!empty($_POST['reset_password'])) {
                            updateUserPassword($existing['id'], $password);
                        }
                        $success = 'Existing user upgraded to tutor.';
                    } else {
                        createUserWithRole($name, $email, $password, 'tutor');
                        $success = 'Tutor account created successfully.';
                    }

                    $tempPassword = $password;
                    $newInviteEmail = $email;
                }
            }
        } elseif ($action === 'promote') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId > 0) {
                updateUserRole($userId, 'tutor');
                $success = 'Student upgraded to tutor.';
            }
        }
    }
}

$totalTutors = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'tutor'")->fetchColumn();
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$recentTutors = getUsersByRole('tutor');
$students = getUsersByRole('student');

renderAdminLayoutStart('Tutors', 'tutors');
?>
    <div class="space-y-8">
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="p-4 rounded-lg bg-brand-50 border border-brand-200 text-brand-800">
                <div class="font-semibold"><?= h($success) ?></div>
                <?php if ($tempPassword): ?>
                    <div class="mt-2 text-sm">Email: <?= h($newInviteEmail) ?> | Temporary password: <span class="font-bold"><?= h($tempPassword) ?></span></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tutors</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-600"><?= $totalTutors ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Students Available to Upgrade</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $totalStudents ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Package Focus</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">Tutor</p>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Invite Flow</p>
                        <h1 class="text-2xl font-extrabold text-slate-900">Invite or upgrade tutors</h1>
                    </div>
                </div>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="invite">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                            <input type="text" name="name" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" placeholder="Tutor Name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                            <input type="email" name="email" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" placeholder="tutor@example.com" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Temporary Password</label>
                            <input type="text" name="password" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" placeholder="Leave blank to auto-generate">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                                <input type="checkbox" name="reset_password" value="1" class="w-4 h-4 text-brand-600 rounded border-slate-300">
                                Reset password if this email already exists
                            </label>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700">Invite Tutor</button>
                        <p class="text-sm text-slate-500 self-center">Existing student accounts can be promoted with one click.</p>
                    </div>
                </form>

                <div class="mt-10 border-t border-slate-200 pt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Students to Upgrade</h2>
                        <span class="text-sm text-slate-500"><?= count($students) ?> students</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <?php if (empty($students)): ?>
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No students available for upgrade.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td class="px-4 py-4 text-sm font-semibold text-slate-900"><?= h($student['name']) ?></td>
                                            <td class="px-4 py-4 text-sm text-slate-600"><?= h($student['email']) ?></td>
                                            <td class="px-4 py-4 text-sm text-slate-600"><?= date('d M Y', strtotime($student['created_at'])) ?></td>
                                            <td class="px-4 py-4 text-right">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                    <input type="hidden" name="action" value="promote">
                                                    <input type="hidden" name="user_id" value="<?= (int)$student['id'] ?>">
                                                    <button type="submit" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Upgrade</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Recent Tutors</h2>
                    <div class="space-y-3">
                        <?php if (empty($recentTutors)): ?>
                            <p class="text-sm text-slate-500">No tutors yet.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($recentTutors, 0, 6) as $tutor): ?>
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="font-semibold text-slate-900"><?= h($tutor['name']) ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?= h($tutor['email']) ?></div>
                                    <div class="text-xs text-slate-500 mt-2">Joined: <?= date('d M Y', strtotime($tutor['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-slate-900 rounded-xl shadow-lg p-6 text-white">
                    <h2 class="text-lg font-bold text-brand-400">Workflow</h2>
                    <p class="mt-3 text-sm text-slate-300 leading-6">
                        Use this page to create a tutor login or upgrade an existing student to tutor access.
                        Temporary passwords can be generated automatically for quick onboarding.
                    </p>
                </div>
            </aside>
        </section>
    </div>
<?php
renderAdminLayoutEnd();
