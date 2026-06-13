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
        set_flash('error', 'The requested file could not be found. Please contact support.');
        redirect('my-courses.php');
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