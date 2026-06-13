<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Ensure only admin can access
if (!isAdmin()) {
    redirect('../login.php');
}

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    // Optional: also remove associated file from uploads folder
    $stmt = $pdo->prepare("SELECT file_path, file_type FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product && $product['file_type'] === 'pdf' && file_exists($product['file_path'])) {
        unlink($product['file_path']); // delete physical file
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    set_flash('success', 'Product deleted successfully.');
    redirect('products.php');
}

// Handle add/edit product
$editProduct = null;
$isEdit = false;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editProduct) {
        $isEdit = true;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
        redirect('products.php');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'General');
    $file_type = $_POST['file_type'] ?? 'pdf';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || $price < 0) {
        set_flash('error', 'Title and valid price are required.');
    } else {
        $file_path = '';
        $uploadError = false;

        if ($file_type === 'pdf') {
            // Handle file upload
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $originalName = basename($_FILES['pdf_file']['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    set_flash('error', 'Only PDF files are allowed.');
                    $uploadError = true;
                } else {
                    $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                    $destination = $uploadDir . $safeName;
                    if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $destination)) {
                        $file_path = 'uploads/products/' . $safeName;
                    } else {
                        set_flash('error', 'File upload failed.');
                        $uploadError = true;
                    }
                }
            } elseif ($isEdit && !empty($_POST['existing_file_path'])) {
                // Keep existing file
                $file_path = $_POST['existing_file_path'];
            } else {
                set_flash('error', 'Please select a PDF file to upload.');
                $uploadError = true;
            }
        } else { // link
            $link_url = trim($_POST['link_url'] ?? '');
            if (filter_var($link_url, FILTER_VALIDATE_URL)) {
                $file_path = $link_url;
            } else {
                set_flash('error', 'Please provide a valid URL (starting with http:// or https://).');
                $uploadError = true;
            }
        }

        if (!$uploadError && !empty($file_path)) {
            if ($isEdit) {
                $stmt = $pdo->prepare("UPDATE products SET title = ?, description = ?, price = ?, category = ?, file_type = ?, file_path = ?, is_active = ? WHERE id = ?");
                $result = $stmt->execute([$title, $description, $price, $category, $file_type, $file_path, $is_active, $editProduct['id']]);
                if ($result) {
                    // If file was replaced, delete old file (if different and old exists)
                    if ($file_type === 'pdf' && isset($_FILES['pdf_file']['error']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                        $oldFile = $editProduct['file_path'];
                        if (!empty($oldFile) && file_exists($oldFile) && $oldFile !== $file_path) {
                            unlink($oldFile);
                        }
                    }
                    set_flash('success', 'Product updated successfully.');
                } else {
                    set_flash('error', 'Failed to update product.');
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (title, description, price, category, file_type, file_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$title, $description, $price, $category, $file_type, $file_path, $is_active]);
                if ($result) {
                    set_flash('success', 'Product added successfully.');
                } else {
                    set_flash('error', 'Failed to add product.');
                }
            }
            redirect('products.php');
        }
    }
}

// Fetch all products for listing
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { brand: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 900: '#312e81' } }
                }
            }
        }
    </script>
    <style>
        .flash-message { transition: opacity 0.5s; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">

    <!-- Admin Header (same as dashboard) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <img src="../assets/logo.jpeg" alt="Fun Maths Mastery" class="w-10 h-10">
                    <span class="font-bold text-slate-900 hidden sm:inline">Admin Panel</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">Welcome, <?= h($_SESSION['user_name'] ?? 'Admin') ?></span>
                <a href="../logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Sign Out</a>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 -mb-px">
                <a href="dashboard.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Dashboard</a>
                <a href="products.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-semibold border-b-2 border-brand-600 text-brand-600">Products</a>
                <a href="orders.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Orders</a>
                <a href="users.php" class="inline-flex items-center px-1 pt-4 pb-3 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent">Users</a>
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

        <!-- Add/Edit Product Form (shown when edit or add via GET parameter? We'll show inline above list but can also be modal. For simplicity, show form when ?action=add or ?edit=id. We'll use a toggle using GET param) -->
        <?php if (isset($_GET['action']) && $_GET['action'] === 'add' || $isEdit): ?>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900"><?= $isEdit ? 'Edit Product' : 'Add New Product' ?></h2>
                    <a href="products.php" class="text-sm text-brand-600 hover:text-brand-800">&larr; Back to Products</a>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="save">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="existing_file_path" value="<?= h($editProduct['file_path']) ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Product Title *</label>
                            <input type="text" name="title" value="<?= $isEdit ? h($editProduct['title']) : '' ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Price (R) *</label>
                            <input type="number" step="0.01" min="0" name="price" value="<?= $isEdit ? $editProduct['price'] : '' ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600"><?= $isEdit ? h($editProduct['description']) : '' ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                            <input type="text" name="category" value="<?= $isEdit ? h($editProduct['category']) : 'General' ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">File Type *</label>
                            <select name="file_type" id="file_type" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600" required>
                                <option value="pdf" <?= ($isEdit && $editProduct['file_type'] === 'pdf') ? 'selected' : '' ?>>PDF Document</option>
                                <option value="link" <?= ($isEdit && $editProduct['file_type'] === 'link') ? 'selected' : '' ?>>External Link (URL)</option>
                            </select>
                        </div>
                        <div id="pdf_upload_container" class="<?= ($isEdit && $editProduct['file_type'] === 'link') ? 'hidden' : '' ?>">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">PDF File <?= $isEdit ? '(Leave empty to keep current)' : '*' ?></label>
                            <input type="file" name="pdf_file" accept=".pdf" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
                            <?php if ($isEdit && $editProduct['file_type'] === 'pdf'): ?>
                                <p class="text-xs text-slate-500 mt-1">Current file: <?= basename($editProduct['file_path']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div id="link_container" class="<?= ($isEdit && $editProduct['file_type'] === 'pdf') ? 'hidden' : '' ?>">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">External URL *</label>
                            <input type="url" name="link_url" value="<?= $isEdit && $editProduct['file_type'] === 'link' ? h($editProduct['file_path']) : '' ?>" placeholder="https://example.com/resource" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-600">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($isEdit && $editProduct['is_active'] == 1) ? 'checked' : 'checked' ?> class="w-4 h-4 text-brand-600 rounded">
                            <label for="is_active" class="ml-2 text-sm text-slate-700">Active (visible in store)</label>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="px-6 py-2 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700"><?= $isEdit ? 'Update Product' : 'Add Product' ?></button>
                        <a href="products.php" class="px-6 py-2 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Products List -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-900">All Products</h2>
                <a href="products.php?action=add" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700">+ Add New Product</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-slate-500">No products found. Click "Add New Product" to get started.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?= $product['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?= h($product['title']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?= h($product['category']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">R <?= number_format($product['price'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $product['file_type'] === 'pdf' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800' ?>">
                                            <?= strtoupper($product['file_type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="products.php?edit=<?= $product['id'] ?>" class="text-brand-600 hover:text-brand-800">Edit</a>
                                        <a href="products.php?delete=<?= $product['id'] ?>" onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')" class="text-red-600 hover:text-red-800">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Toggle file/link fields based on file type selection
        const fileTypeSelect = document.getElementById('file_type');
        const pdfContainer = document.getElementById('pdf_upload_container');
        const linkContainer = document.getElementById('link_container');

        if (fileTypeSelect) {
            fileTypeSelect.addEventListener('change', function() {
                if (this.value === 'pdf') {
                    pdfContainer.classList.remove('hidden');
                    linkContainer.classList.add('hidden');
                } else {
                    pdfContainer.classList.add('hidden');
                    linkContainer.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>