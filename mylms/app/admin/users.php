<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$studentUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$adminUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$accessCount = (int)$pdo->query("SELECT COUNT(*) FROM user_product_access")->fetchColumn();

$stmt = $pdo->query("
    SELECT
        u.id,
        u.name,
        u.email,
        u.role,
        u.created_at,
        COUNT(DISTINCT o.id) AS order_count,
        COUNT(DISTINCT upa.product_id) AS product_count
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    LEFT JOIN user_product_access upa ON upa.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC, u.id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedUserId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedUser = null;
$selectedProducts = [];
if ($selectedUserId) {
    $stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedUser) {
        $selectedProducts = getUserPurchasedProducts($selectedUserId);
    }
}

renderAdminLayoutStart('Users', 'users');
?>
    <div class="space-y-8">
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">All Users</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $totalUsers ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Students</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-600"><?= $studentUsers ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Admins</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $adminUsers ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Product Access Grants</p>
                <p class="mt-2 text-3xl font-extrabold text-green-600"><?= $accessCount ?></p>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h1 class="text-lg font-bold text-slate-900">Registered Users</h1>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Resources</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="<?= $selectedUserId === (int)$user['id'] ? 'bg-brand-50/60' : '' ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-slate-900"><?= h($user['name']) ?></div>
                                            <div class="text-xs text-slate-500">ID #<?= $user['id'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= h($user['email']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-slate-900 text-white' : 'bg-brand-100 text-brand-700' ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= (int)$user['order_count'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= (int)$user['product_count'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <a href="users.php?id=<?= $user['id'] ?>" class="text-sm font-semibold text-brand-600 hover:text-brand-700">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <?php if ($selectedUser): ?>
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide mb-2">User Profile</p>
                        <h2 class="text-2xl font-extrabold text-slate-900"><?= h($selectedUser['name']) ?></h2>
                        <p class="text-sm text-slate-500 mt-2"><?= h($selectedUser['email']) ?></p>
                    </div>

                    <div class="space-y-3 text-sm mb-6">
                        <div>
                            <span class="font-semibold text-slate-900">Role:</span>
                            <span class="ml-2 px-2.5 py-1 rounded-full text-xs font-semibold <?= $selectedUser['role'] === 'admin' ? 'bg-slate-900 text-white' : 'bg-brand-100 text-brand-700' ?>">
                                <?= ucfirst($selectedUser['role']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900">Joined:</span>
                            <span class="ml-2 text-slate-600"><?= date('d M Y, H:i', strtotime($selectedUser['created_at'])) ?></span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900">Purchased resources:</span>
                            <span class="ml-2 text-slate-600"><?= count($selectedProducts) ?></span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="font-bold text-slate-900 mb-4">Recent Purchases</h3>
                        <div class="space-y-3">
                            <?php if (empty($selectedProducts)): ?>
                                <p class="text-sm text-slate-500">No purchases recorded yet.</p>
                            <?php else: ?>
                                <?php foreach ($selectedProducts as $product): ?>
                                    <div class="rounded-lg border border-slate-200 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="font-semibold text-slate-900"><?= h($product['title']) ?></div>
                                                <div class="text-xs text-slate-500 mt-1"><?= h($product['category'] ?? 'Resource') ?> • <?= strtoupper($product['file_type']) ?></div>
                                            </div>
                                            <div class="text-sm font-bold text-slate-900">R <?= number_format($product['price'], 2) ?></div>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-2">Purchased: <?= date('d M Y, H:i', strtotime($product['purchase_date'])) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-full flex flex-col justify-center text-center py-10">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-4M9 20H4v-2a4 4 0 015-4m8-8a4 4 0 11-8 0 4 4 0 018 0zm-8 8a4 4 0 100-8 4 4 0 000 8z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">Select a user</h2>
                        <p class="text-sm text-slate-500 mt-2">Choose a student or admin from the table to view their profile and purchases.</p>
                    </div>
                <?php endif; ?>
            </aside>
        </section>
    </div>
<?php
renderAdminLayoutEnd();
