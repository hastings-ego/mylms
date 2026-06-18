<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get product ID from URL
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if (!$productId) {
    set_flash('error', 'Invalid product request.');
    redirect('my-courses.php');
}

$userId = $_SESSION['user_id'];

// Check if user has purchased this product
if (!hasPurchased($userId, $productId)) {
    set_flash('error', 'You have not purchased this resource.');
    redirect('store.php');
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    set_flash('error', 'Product not found or unavailable.');
    redirect('my-courses.php');
}

// Handle based on file type
if ($product['file_type'] === 'pdf') {
    $filePath = $product['file_path'];
    $fullPath = __DIR__ . '/' . $filePath;
    
    // Check if file exists
    if (!file_exists($fullPath)) {
        $title = h($product['title']);
        $description = nl2br(h($product['description'] ?? ''));
        $category = h($product['category'] ?? 'Resource');
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Fun Maths Mastery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="max-w-2xl w-full bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Purchased resource</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900"><?= $title ?></h1>
        <div class="mt-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= $category ?></div>
        <div class="mt-6 space-y-4 text-slate-700 leading-7">
            <p><?= $description ?></p>
            <p>The physical PDF file is not present on the server yet, so this is a preview page for now.</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="my-courses.php" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-3 text-white font-semibold hover:bg-brand-700">Back to My Courses</a>
            <a href="store.php" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50">Browse Store</a>
        </div>
    </div>
</body>
</html>
<?php
        exit;
    }
    
    // Serve the PDF file
    $fileName = basename($filePath);
    $fileSize = filesize($fullPath);
    
    // Set headers for inline display (browser will show PDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Clear output buffer and read file
    ob_clean();
    flush();
    readfile($fullPath);
    exit;
    
} elseif ($product['file_type'] === 'link') {
    // External link: redirect to the stored URL
    $linkUrl = $product['file_path'];
    
    // Validate URL (basic check)
    if (!filter_var($linkUrl, FILTER_VALIDATE_URL)) {
        set_flash('error', 'Invalid resource link. Please contact support.');
        redirect('my-courses.php');
    }
    
    // Redirect to external link
    header("Location: $linkUrl");
    exit;
    
} else {
    set_flash('error', 'Unknown file type.');
    redirect('my-courses.php');
}
?>
