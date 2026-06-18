<?php
require_once 'app/config/db.php';
require_once 'app/config/functions.php';

echo "=== DATABASE DEBUG TEST ===\n\n";

// Test 1: Check if we can connect
echo "✓ Database connection established\n";

// Test 2: Check users table
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "✓ Users in database: " . ($result['count'] ?? 0) . "\n";

// Test 3: Check live_classes table
$stmt = $pdo->query("SELECT COUNT(*) as count FROM live_classes");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "✓ Live classes in database: " . ($result['count'] ?? 0) . "\n";

// Test 4: Check class_enrollments table
if ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='class_enrollments'")) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM class_enrollments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Class enrollments in database: " . ($result['count'] ?? 0) . "\n";
} else {
    echo "✗ class_enrollments table does not exist\n";
}

// Test 5: Check support_tickets table
if ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='support_tickets'")) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM support_tickets");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Support tickets in database: " . ($result['count'] ?? 0) . "\n";
} else {
    echo "✗ support_tickets table does not exist\n";
}

// Test 6: Check user_product_access table
$stmt = $pdo->query("SELECT COUNT(*) as count FROM user_product_access");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "✓ User product access records: " . ($result['count'] ?? 0) . "\n";

// Test 7: Check products table
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "✓ Products in database: " . ($result['count'] ?? 0) . "\n";

// Test 8: Try to get a student user
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'student' LIMIT 1");
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if ($student) {
    echo "\n✓ Test student found: " . $student['name'] . " (ID: " . $student['id'] . ")\n";
    
    // Test 9: Try sample functions with this student
    echo "\nTesting functions with student ID: " . $student['id'] . "\n";
    
    $purchased = getUserPurchasedProducts($student['id']);
    echo "✓ getUserPurchasedProducts returned: " . count($purchased) . " items\n";
    
    $enrolled = getStudentEnrolledClasses($student['id']);
    echo "✓ getStudentEnrolledClasses returned: " . count($enrolled) . " classes\n";
    
    $available = getAvailableClassesForStudent($student['id'], 5);
    echo "✓ getAvailableClassesForStudent returned: " . count($available) . " classes\n";
    
    $tickets = getStudentSupportTickets($student['id']);
    echo "✓ getStudentSupportTickets returned: " . count($tickets) . " tickets\n";
} else {
    echo "✗ No student user found\n";
}

echo "\n=== END DEBUG ===\n";
?>
