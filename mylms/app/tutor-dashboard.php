<?php
require_once 'config/db.php';
require_once 'config/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$role = $_SESSION['role'] ?? 'student';
if ($role !== 'tutor' && !isAdmin()) {
    redirect('dashboard.php');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$tutors = isAdmin() ? getUsersByRole('tutor') : [];
$isEditing = false;
$editClass = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $candidate = getLiveClassById((int) $_GET['edit']);
    if ($candidate && ((int) $candidate['tutor_id'] === $userId || isAdmin())) {
        $editClass = $candidate;
        $isEditing = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token. Please refresh the page and try again.');
        redirect('tutor-dashboard.php');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'save_class') {
        $startDate = trim($_POST['start_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $targetTutorId = isAdmin() ? (int) ($_POST['tutor_id'] ?? 0) : $userId;

        $data = [
            'id' => (int) ($_POST['class_id'] ?? 0),
            'tutor_id' => $targetTutorId,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_at' => $startDate && $startTime ? date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTime)) : '',
            'end_at' => $endDate && $endTime ? date('Y-m-d H:i:s', strtotime($endDate . ' ' . $endTime)) : '',
            'meet_link' => trim($_POST['meet_link'] ?? ''),
            'status' => trim($_POST['status'] ?? 'published'),
        ];

        if ($data['tutor_id'] <= 0 || $data['title'] === '' || $data['start_at'] === '' || $data['end_at'] === '') {
            set_flash('error', 'Please provide a title and both start/end times.');
        } elseif (strtotime($data['end_at']) <= strtotime($data['start_at'])) {
            set_flash('error', 'The class end time must be after the start time.');
        } elseif ($data['meet_link'] !== '' && !isGoogleMeetUrl($data['meet_link'])) {
            set_flash('error', 'Please enter a valid Google Meet link.');
        } else {
            $saved = saveLiveClass($data);
            if ($saved) {
                set_flash('success', $data['id'] ? 'Class updated successfully.' : 'Class created successfully.');
            } else {
                set_flash('error', 'Unable to save the class. Please check the details and try again.');
            }
        }

        redirect('tutor-dashboard.php');
    }
}

$scopeTutorId = isAdmin() && isset($_GET['tutor_id']) ? (int) $_GET['tutor_id'] : 0;
if (isAdmin() && $scopeTutorId > 0) {
    $upcomingClasses = getLiveClasses([
        'tutor_id' => $scopeTutorId,
        'upcoming_only' => true,
    ]);
    $allClasses = getLiveClasses(['tutor_id' => $scopeTutorId]);
} elseif ($role === 'tutor') {
    $upcomingClasses = getLiveClasses([
        'tutor_id' => $userId,
        'upcoming_only' => true,
    ]);
    $allClasses = getLiveClasses(['tutor_id' => $userId]);
} else {
    $upcomingClasses = getLiveClasses(['upcoming_only' => true]);
    $allClasses = getLiveClasses();
}
$flash = get_flash();

$defaultStart = $isEditing ? date('Y-m-d', strtotime($editClass['start_at'])) : date('Y-m-d');
$defaultStartTime = $isEditing ? date('H:i', strtotime($editClass['start_at'])) : '15:00';
$defaultEnd = $isEditing ? date('Y-m-d', strtotime($editClass['end_at'])) : date('Y-m-d');
$defaultEndTime = $isEditing ? date('H:i', strtotime($editClass['end_at'])) : '16:00';
$defaultTutorId = $isEditing ? (int) $editClass['tutor_id'] : ($role === 'tutor' ? $userId : (int) ($tutors[0]['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#ee9c85', 600: '#18a4a3', 700: '#18a4a3', 900: '#18a4a3' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="tutor-dashboard.php" class="flex items-center gap-3">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                <div>
                    <div class="font-bold text-slate-900">Tutor Dashboard</div>
                    <div class="text-xs text-slate-500">Google Meet session control</div>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <a href="tutoring.php" class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">Student View</a>
                <a href="logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Your Classes</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= count($allClasses) ?></p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Upcoming</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-600"><?= count($upcomingClasses) ?></p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Visibility Rule</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">-5 mins</p>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Session Setup</p>
                        <h1 class="text-2xl font-extrabold text-slate-900"><?= $isEditing ? 'Edit live class' : 'Post a Google Meet link' ?></h1>
                    </div>
                    <?php if ($isEditing): ?>
                        <a href="tutor-dashboard.php" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Cancel edit</a>
                    <?php endif; ?>
                </div>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="save_class">
                    <input type="hidden" name="class_id" value="<?= $isEditing ? (int) $editClass['id'] : 0 ?>">

                    <?php if (isAdmin()): ?>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tutor</label>
                            <select name="tutor_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                <option value="">Select a tutor</option>
                                <?php foreach ($tutors as $tutor): ?>
                                    <option value="<?= (int) $tutor['id'] ?>" <?= $defaultTutorId === (int) $tutor['id'] ? 'selected' : '' ?>><?= h($tutor['name']) ?> (<?= h($tutor['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="tutor_id" value="<?= $userId ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Class Title</label>
                        <input type="text" name="title" value="<?= $isEditing ? h($editClass['title']) : '' ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" placeholder="Algebra Live Class" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" placeholder="Short notes for students..."><?= $isEditing ? h($editClass['description']) : '' ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                            <input type="date" name="start_date" value="<?= h($defaultStart) ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Start Time</label>
                            <input type="time" name="start_time" value="<?= h($defaultStartTime) ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                            <input type="date" name="end_date" value="<?= h($defaultEnd) ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">End Time</label>
                            <input type="time" name="end_time" value="<?= h($defaultEndTime) ?>" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Google Meet Link</label>
                        <input
                            type="url"
                            name="meet_link"
                            value="<?= $isEditing ? h($editClass['meet_link']) : '' ?>"
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none"
                            placeholder="https://meet.google.com/abc-defg-hij"
                        >
                        <p class="text-xs text-slate-500 mt-2">Students will only see the link starting 5 minutes before class time.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none">
                            <option value="published" <?= !$isEditing || ($editClass['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= $isEditing && ($editClass['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="cancelled" <?= $isEditing && ($editClass['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-sm">
                            <?= $isEditing ? 'Update Class' : 'Publish Class' ?>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="bg-slate-900 text-white rounded-xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-brand-400">Google Meet Rule</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        Post the Meet link here before class. Students will only see it in the final 5 minutes before the class start time.
                    </p>
                    <?php if (isAdmin()): ?>
                        <div class="mt-4">
                            <a href="tutor-dashboard.php" class="inline-flex px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100">View all tutors</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Schedule Snapshot</h2>
                    <div class="space-y-3">
                        <?php if (empty($upcomingClasses)): ?>
                            <p class="text-sm text-slate-500">No upcoming classes yet.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($upcomingClasses, 0, 4) as $class): ?>
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="font-semibold text-slate-900"><?= h($class['title']) ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?= date('d M Y, H:i', strtotime($class['start_at'])) ?> - <?= date('H:i', strtotime($class['end_at'])) ?></div>
                                    <div class="text-xs text-slate-500 mt-2">
                                        Link: <?= $class['meet_link'] ? 'saved' : 'not added yet' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </section>

        <section class="mt-8 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-bold text-slate-900">Your Live Classes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Link</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <?php if (empty($allClasses)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No classes created yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allClasses as $class): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-900"><?= h($class['title']) ?></div>
                                        <div class="text-xs text-slate-500 mt-1"><?= h($class['description'] ?: 'No description added.') ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <?= date('d M Y, H:i', strtotime($class['start_at'])) ?><br>
                                        to <?= date('d M Y, H:i', strtotime($class['end_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <?= $class['meet_link'] ? (isGoogleMeetUrl($class['meet_link']) ? 'Google Meet saved' : 'Invalid link') : 'Not added' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $class['status'] === 'published' ? 'bg-green-100 text-green-700' : ($class['status'] === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') ?>">
                                            <?= ucfirst($class['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="tutor-dashboard.php?edit=<?= (int) $class['id'] ?>" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
