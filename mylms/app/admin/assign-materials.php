<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$role = $_SESSION['role'] ?? '';
if (!isLoggedIn() || (!isAdmin() && $role !== 'collaborator')) {
    redirect('../login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $action = $_POST['action'] ?? 'assign';

        if ($userId <= 0 || $productId <= 0) {
            $error = 'Please choose both a student and a material.';
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT id, title FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !$product) {
                $error = 'Unable to find the selected student or material.';
            } elseif ($user['role'] === 'admin') {
                $error = 'Admin accounts do not receive material grants.';
            } else {
                if ($action === 'revoke') {
                    $stmt = $pdo->prepare("DELETE FROM user_product_access WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$userId, $productId]);
                    $success = 'Material access revoked.';
                } else {
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO user_product_access (user_id, product_id, purchase_date) VALUES (?, ?, datetime('now'))");
                    $stmt->execute([$userId, $productId]);
                    $success = 'Material assigned successfully.';
                }
            }
        }
    }
}

$students = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$materials = $pdo->query("SELECT id, title, category, file_type, price FROM products WHERE is_active = 1 ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$assignments = $pdo->query("
    SELECT
        upa.purchase_date,
        u.id AS user_id,
        u.name AS user_name,
        u.email AS user_email,
        p.id AS product_id,
        p.title AS product_title,
        p.category AS product_category,
        p.file_type
    FROM user_product_access upa
    JOIN users u ON u.id = upa.user_id
    JOIN products p ON p.id = upa.product_id
    ORDER BY upa.purchase_date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

renderAdminLayoutStart('Assign Materials', 'products');
?>
    <div class="space-y-8">
        <?php if ($success): ?>
            <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"><?= h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800"><?= h($error) ?></div>
        <?php endif; ?>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Students</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= count($students) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Active Materials</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-600"><?= count($materials) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Assigned Access</p>
                <p class="mt-2 text-3xl font-extrabold text-green-600"><?= count($assignments) ?></p>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Material Access</p>
                        <h1 class="text-2xl font-extrabold text-slate-900">Assign materials to students</h1>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="assign">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Student</label>
                            <select name="user_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= (int) $student['id'] ?>"><?= h($student['name']) ?> (<?= h($student['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Material</label>
                            <select name="product_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 outline-none" required>
                                <option value="">Select material</option>
                                <?php foreach ($materials as $material): ?>
                                    <option value="<?= (int) $material['id'] ?>"><?= h($material['title']) ?> (<?= h($material['category'] ?? 'Resource') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700">Assign Material</button>
                        <p class="text-sm text-slate-500 self-center">Use this for collaborator/admin-granted access outside of checkout.</p>
                    </div>
                </form>

                <div class="mt-10 border-t border-slate-200 pt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">Recent Assignments</h2>
                        <span class="text-sm text-slate-500"><?= count($assignments) ?> recent entries</span>
                    </div>
                    <div class="space-y-3">
                        <?php if (empty($assignments)): ?>
                            <p class="text-sm text-slate-500">No assignments yet.</p>
                        <?php else: ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <div class="rounded-xl border border-slate-200 p-4 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-semibold text-slate-900"><?= h($assignment['user_name']) ?></div>
                                        <div class="text-xs text-slate-500"><?= h($assignment['user_email']) ?> • <?= h($assignment['product_title']) ?></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500"><?= date('d M Y, H:i', strtotime($assignment['purchase_date'])) ?></div>
                                        <form method="POST" class="mt-2">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="revoke">
                                            <input type="hidden" name="user_id" value="<?= (int) $assignment['user_id'] ?>">
                                            <input type="hidden" name="product_id" value="<?= (int) $assignment['product_id'] ?>">
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Revoke</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="bg-slate-900 text-white rounded-xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-brand-400">What this does</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        This page gives collaborators and admins a manual way to grant a product to a student, even when the material is not purchased through checkout.
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Available Materials</h2>
                    <div class="space-y-3">
                        <?php foreach (array_slice($materials, 0, 6) as $material): ?>
                            <div class="rounded-lg border border-slate-200 p-4">
                                <div class="font-semibold text-slate-900"><?= h($material['title']) ?></div>
                                <div class="text-xs text-slate-500 mt-1"><?= h($material['category'] ?? 'Resource') ?> • <?= strtoupper(h($material['file_type'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </section>
    </div>
<?php
renderAdminLayoutEnd();
