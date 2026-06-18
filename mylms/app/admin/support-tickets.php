<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Ensure only admin can access
if (!isAdmin()) {
    redirect('../login.php');
}

// Handle ticket response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'respond') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $response = trim($_POST['response'] ?? '');
        $status = trim($_POST['status'] ?? 'in_progress');
        
        if ($response && $ticketId > 0) {
            $stmt = $pdo->prepare("
                UPDATE support_tickets
                SET response = ?, status = ?, updated_at = datetime('now')
                WHERE id = ?
            ");
            if ($stmt->execute([$response, $status, $ticketId])) {
                set_flash('success', 'Ticket response saved.');
            } else {
                set_flash('error', 'Failed to save response.');
            }
        }
    }
    redirect('support-tickets.php');
}

// Get all support tickets
$stmt = $pdo->query("
    SELECT st.*, u.name as user_name, u.email
    FROM support_tickets st
    JOIN users u ON st.user_id = u.id
    ORDER BY 
        CASE WHEN st.priority = 'urgent' THEN 1
             WHEN st.priority = 'high' THEN 2
             WHEN st.priority = 'medium' THEN 3
             ELSE 4
        END,
        st.created_at DESC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$statusCounts = [];
foreach ($tickets as $ticket) {
    $status = $ticket['status'];
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets | Admin</title>
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
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Admin Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <img src="../assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                    <span class="font-bold text-slate-900 hidden sm:inline">Admin Panel</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Support Tickets</span>
                <a href="../logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 -mb-px">
                <a href="dashboard.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Dashboard</a>
                <a href="support-tickets.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-semibold border-b-2 border-brand-600 text-brand-600">Support Tickets</a>
                <a href="users.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Users</a>
                <a href="products.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Products</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Status Overview -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg p-4 border border-slate-200">
                <div class="text-2xl font-bold text-slate-900"><?= count($tickets) ?></div>
                <div class="text-sm text-slate-600">Total Tickets</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-slate-200">
                <div class="text-2xl font-bold text-red-600"><?= $statusCounts['open'] ?? 0 ?></div>
                <div class="text-sm text-slate-600">Open</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-slate-200">
                <div class="text-2xl font-bold text-blue-600"><?= $statusCounts['in_progress'] ?? 0 ?></div>
                <div class="text-sm text-slate-600">In Progress</div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-slate-200">
                <div class="text-2xl font-bold text-green-600"><?= $statusCounts['resolved'] ?? 0 ?></div>
                <div class="text-sm text-slate-600">Resolved</div>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-bold text-slate-900">Support Tickets</h2>
            </div>
            
            <?php if (empty($tickets)): ?>
                <div class="p-6 text-center text-slate-500">
                    No support tickets yet.
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-200">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="font-bold text-slate-900"><?= h($ticket['subject']) ?></h3>
                                    <p class="text-sm text-slate-600 mt-1">From: <strong><?= h($ticket['user_name']) ?></strong> (<?= h($ticket['email']) ?>)</p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 text-xs rounded-full font-medium <?= 
                                        $ticket['priority'] === 'urgent' ? 'bg-red-100 text-red-800' :
                                        ($ticket['priority'] === 'high' ? 'bg-orange-100 text-orange-800' :
                                        ($ticket['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-800'))
                                    ?>">
                                        <?= ucfirst($ticket['priority']) ?>
                                    </span>
                                    <span class="px-3 py-1 text-xs rounded-full font-medium <?= 
                                        $ticket['status'] === 'open' ? 'bg-red-100 text-red-800' :
                                        ($ticket['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                        ($ticket['status'] === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800'))
                                    ?>">
                                        <?= ucfirst($ticket['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-slate-700 mb-3"><?= h($ticket['message']) ?></p>
                            
                            <?php if ($ticket['response']): ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                    <p class="text-sm font-semibold text-green-900">Admin Response:</p>
                                    <p class="text-sm text-green-800 mt-1"><?= h($ticket['response']) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center text-xs text-slate-500">
                                <span><?= date('M d, Y H:i', strtotime($ticket['created_at'])) ?></span>
                                <button class="text-brand-600 hover:text-brand-700 font-semibold" onclick="toggleReply(<?= $ticket['id'] ?>)">Reply</button>
                            </div>
                            
                            <!-- Reply Form -->
                            <form id="reply-<?= $ticket['id'] ?>" method="POST" class="mt-4 hidden">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="respond">
                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                
                                <textarea name="response" placeholder="Type your response..." class="w-full px-4 py-2 border border-slate-300 rounded-lg mb-3" rows="3" required></textarea>
                                
                                <div class="flex gap-2 items-center">
                                    <select name="status" class="px-4 py-2 border border-slate-300 rounded-lg text-sm">
                                        <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 text-sm font-semibold">Send Response</button>
                                    <button type="button" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg text-sm font-semibold" onclick="toggleReply(<?= $ticket['id'] ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleReply(ticketId) {
            const form = document.getElementById('reply-' + ticketId);
            form.classList.toggle('hidden');
        }
    </script>

</body>
</html>
