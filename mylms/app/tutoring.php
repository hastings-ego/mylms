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

// Handle search
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$liveClasses = [];
$tutorsWithClasses = [];

if (!empty($searchQuery)) {
    $liveClasses = searchLiveClasses($searchQuery, true);
} else {
    $liveClasses = getLiveClasses([
        'status' => 'published',
        'upcoming_only' => true,
    ]);
}

// Always get tutors with classes for the tutor directory
$tutorsWithClasses = getTutorsWithClasses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1-on-1 Math Tutors | Fun Maths Mastery</title>
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

    <!-- Sidebar Navigation (identical to dashboard) -->
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
                    <a href="tutoring.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-lg bg-brand-50 text-brand-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        1-on-1 Math Tutors
                    </a>
                    <a href="settings.php" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50">
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
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Live Math Tutoring</h1>
            <p class="text-slate-500 mt-2">Join scheduled Google Meet sessions. Meeting links unlock 5 minutes before class starts.</p>

            <!-- Search Bar -->
            <form method="GET" class="mt-6 mb-8">
                <div class="relative max-w-md">
                    <input type="text" name="search" placeholder="Search classes or tutors..." value="<?= h($searchQuery) ?>" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600 focus:border-transparent">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <section class="mt-8 mb-10">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Google Meet</p>
                        <h2 class="text-xl font-bold text-slate-900">Upcoming live classes</h2>
                    </div>
                </div>
                <?php if (empty($liveClasses)): ?>
                    <div class="rounded-xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm">
                        <?php if (!empty($searchQuery)): ?>
                            No live classes found matching "<strong><?= h($searchQuery) ?></strong>". <a href="tutoring.php" class="text-brand-600 hover:text-brand-700">Clear search</a>
                        <?php else: ?>
                            No live classes have been published yet. Check back soon.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($searchQuery)): ?>
                    <div class="mb-4 flex items-center justify-between text-slate-600">
                        <p>Found <strong><?= count($liveClasses) ?></strong> class<?= count($liveClasses) !== 1 ? 'es' : '' ?> matching "<strong><?= h($searchQuery) ?></strong>"</p>
                        <a href="tutoring.php" class="text-sm text-brand-600 hover:text-brand-700">Clear search</a>
                    </div>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($liveClasses as $class): ?>
                            <?php
                                $isOpen = canViewLiveClassLink($class);
                                $visibleAt = strtotime($class['start_at']) - 300;
                                $minutesUntilOpen = max(0, (int) ceil(($visibleAt - time()) / 60));
                                $timeLabel = date('D, d M Y', strtotime($class['start_at'])) . ' • ' . date('H:i', strtotime($class['start_at'])) . ' - ' . date('H:i', strtotime($class['end_at']));
                            ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600"><?= h($class['tutor_name'] ?? 'Tutor') ?></p>
                                        <h3 class="mt-1 text-lg font-bold text-slate-900"><?= h($class['title']) ?></h3>
                                        <p class="mt-2 text-sm text-slate-500"><?= h($timeLabel) ?></p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= h(ucfirst($class['status'])) ?></span>
                                </div>
                                <p class="mt-4 text-sm leading-6 text-slate-600"><?= h($class['description'] ?: 'No class notes added yet.') ?></p>
                                <div class="mt-5">
                                    <?php if ($isOpen && !empty($class['meet_link'])): ?>
                                        <a href="<?= h($class['meet_link']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-3 text-sm font-bold text-white hover:bg-brand-700 transition-colors">
                                            Join Google Meet
                                        </a>
                                    <?php else: ?>
                                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                            <?php if (!empty($class['meet_link'])): ?>
                                                Link unlocks in <?= $minutesUntilOpen ?> minute<?= $minutesUntilOpen === 1 ? '' : 's' ?>.
                                            <?php else: ?>
                                                Waiting for the tutor to post the Google Meet link.
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Tutors Directory -->
                <?php if (empty($tutorsWithClasses)): ?>
                    <div class="col-span-full rounded-xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm">
                        No tutors available at the moment. Check back soon.
                    </div>
                <?php else: ?>
                    <?php foreach ($tutorsWithClasses as $tutor): ?>
                        <?php $tutorClasses = getTutorClasses($tutor['id']); ?>
                        <div class="flex flex-col sm:flex-row gap-6 p-6 border border-slate-200 rounded-xl bg-white shadow-sm hover:shadow-md hover:border-brand-300 transition-all">
                            <div class="w-20 h-20 rounded-full bg-slate-200 flex-shrink-0 border-4 border-slate-50 overflow-hidden shadow-sm">
                                <svg class="w-full h-full text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row justify-between items-start mb-2 gap-2">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900"><?= h($tutor['name']) ?></h3>
                                        <p class="text-sm font-medium text-brand-600 mb-1">Math Tutor</p>
                                        <p class="text-xs text-slate-500"><?= $tutor['class_count'] ?> upcoming class<?= $tutor['class_count'] !== 1 ? 'es' : '' ?></p>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-600 mb-6 font-medium">
                                    <?php if (!empty($tutorClasses)): ?>
                                        Next class: <?= date('d M, H:i', strtotime($tutorClasses[0]['start_at'])) ?>
                                    <?php else: ?>
                                        No upcoming classes scheduled
                                    <?php endif; ?>
                                </p>
                                <div class="flex flex-wrap gap-2 items-center">
                                    <a href="mailto:<?= h($tutor['email']) ?>" class="flex-1 sm:flex-none px-4 py-2 bg-brand-600 text-white text-sm font-bold rounded-lg hover:bg-brand-700 transition-colors inline-block text-center">Contact Tutor</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
                <strong>Note:</strong> Tutoring booking system is coming soon. For now, please contact us at <a href="mailto:tutors@funmathsmastery.com" class="underline">tutors@funmathsmastery.com</a> to schedule a session.
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
