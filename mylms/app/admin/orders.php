<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$completedOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$revenue = (float)($pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'")->fetchColumn() ?: 0);

$stmt = $pdo->query("
    SELECT
        o.id,
        o.order_date,
        o.total,
        o.status,
        o.payment_method,
        o.payment_status,
        o.payment_reference,
        u.name AS user_name,
        u.email AS user_email,
        COUNT(oi.id) AS item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.order_date DESC, o.id DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedOrderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selectedOrder = null;
$selectedItems = [];
if ($selectedOrderId) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name AS user_name, u.email AS user_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$selectedOrderId]);
    $selectedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedOrder) {
        $stmt = $pdo->prepare("
            SELECT oi.price, oi.created_at, p.title AS product_name, p.file_type, p.category
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        $stmt->execute([$selectedOrderId]);
        $selectedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

renderAdminLayoutStart('Orders', 'orders');
?>
    <div class="space-y-8">
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Orders</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900"><?= $totalOrders ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Completed</p>
                <p class="mt-2 text-3xl font-extrabold text-green-600"><?= $completedOrders ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pending</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-600"><?= $pendingOrders ?></p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Revenue</p>
                <p class="mt-2 text-3xl font-extrabold text-brand-600">R <?= number_format($revenue, 2) ?></p>
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h1 class="text-lg font-bold text-slate-900">All Orders</h1>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">No orders have been placed yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="<?= $selectedOrderId === (int)$order['id'] ? 'bg-brand-50/60' : '' ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">#<?= $order['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900"><?= h($order['user_name']) ?></div>
                                            <div class="text-xs text-slate-500"><?= h($order['user_email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= (int)$order['item_count'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-700' : ($order['status'] === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">R <?= number_format($order['total'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <a href="orders.php?id=<?= $order['id'] ?>" class="text-sm font-semibold text-brand-600 hover:text-brand-700">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <?php if ($selectedOrder): ?>
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide mb-2">Order Details</p>
                        <h2 class="text-2xl font-extrabold text-slate-900">Order #<?= $selectedOrder['id'] ?></h2>
                        <p class="text-sm text-slate-500 mt-2">Placed on <?= date('d M Y, H:i', strtotime($selectedOrder['order_date'])) ?></p>
                    </div>
                    <div class="space-y-3 text-sm mb-6">
                        <div>
                            <span class="font-semibold text-slate-900">Customer:</span>
                            <div class="text-slate-600"><?= h($selectedOrder['user_name']) ?></div>
                            <div class="text-slate-500"><?= h($selectedOrder['user_email']) ?></div>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900">Status:</span>
                            <span class="ml-2 px-2.5 py-1 rounded-full text-xs font-semibold <?= $selectedOrder['status'] === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                                <?= ucfirst($selectedOrder['status']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900">Total:</span>
                            <span class="ml-2 font-bold text-slate-900">R <?= number_format($selectedOrder['total'], 2) ?></span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900">Payment:</span>
                            <span class="ml-2 text-slate-600"><?= h(ucfirst($selectedOrder['payment_method'] ?? 'yoco')) ?></span>
                            <div class="mt-1 text-xs text-slate-500"><?= h(ucfirst($selectedOrder['payment_status'] ?? 'paid')) ?><?= !empty($selectedOrder['payment_reference']) ? ' • ' . h($selectedOrder['payment_reference']) : '' ?></div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="font-bold text-slate-900 mb-4">Items</h3>
                        <div class="space-y-3">
                            <?php foreach ($selectedItems as $item): ?>
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-slate-900"><?= h($item['product_name']) ?></div>
                                            <div class="text-xs text-slate-500 mt-1"><?= h($item['category']) ?> • <?= strtoupper($item['file_type']) ?></div>
                                        </div>
                                        <div class="text-sm font-bold text-slate-900">R <?= number_format($item['price'], 2) ?></div>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-2">Added: <?= date('d M Y, H:i', strtotime($item['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-full flex flex-col justify-center text-center py-10">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6m-9 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H7a2 2 0 00-2 2v7a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">Select an order</h2>
                        <p class="text-sm text-slate-500 mt-2">Choose any order from the list to inspect the customer and purchased items.</p>
                    </div>
                <?php endif; ?>
            </aside>
        </section>
    </div>
<?php
renderAdminLayoutEnd();
