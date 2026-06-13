<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (!isAdmin()) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    echo '<div class="text-red-600">Invalid order ID.</div>';
    exit;
}

// Fetch order info
$stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<div class="text-red-600">Order not found.</div>';
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.title as product_name, p.file_type FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div>
    <div class="mb-6">
        <h4 class="font-bold text-slate-900">Order #<?= $order['id'] ?></h4>
        <p class="text-sm text-slate-600">Placed on <?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
        <p class="text-sm text-slate-600">Customer: <?= h($order['user_name']) ?> (<?= h($order['user_email']) ?>)</p>
        <p class="text-sm">Status: <span class="font-semibold <?= $order['status'] === 'completed' ? 'text-green-600' : ($order['status'] === 'pending' ? 'text-yellow-600' : 'text-red-600') ?>"><?= ucfirst($order['status']) ?></span></p>
    </div>
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr><th class="px-4 py-2 text-left text-xs font-medium">Product</th><th class="px-4 py-2 text-left text-xs font-medium">Type</th><th class="px-4 py-2 text-right text-xs font-medium">Price</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td class="px-4 py-2 text-sm"><?= h($item['product_name']) ?></td>
                    <td class="px-4 py-2 text-sm"><?= strtoupper($item['file_type']) ?></td>
                    <td class="px-4 py-2 text-sm text-right">R <?= number_format($item['price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-slate-50">
            <tr><td colspan="2" class="px-4 py-2 text-right font-bold">Total:</td><td class="px-4 py-2 text-right font-bold">R <?= number_format($order['total'], 2) ?></td></tr>
        </tfoot>
    </table>
</div>