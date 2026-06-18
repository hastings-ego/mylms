<?php
/**
 * Seed sample data for testing
 */

require_once 'app/config/db.php';

try {
    // Check if tutor exists and get their ID
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'tutor' LIMIT 1");
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tutor) {
        echo "No tutor found. Creating demo tutor...\n";
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Demo Tutor', 'tutor@demo.com', password_hash('password123', PASSWORD_BCRYPT), 'tutor']);
        $tutorId = $pdo->lastInsertId();
    } else {
        $tutorId = $tutor['id'];
    }
    
    // Add sample live classes
    $now = new DateTime();
    $classes = [
        [
            'title' => 'Algebra Basics - Grade 8',
            'description' => 'Learn the fundamentals of algebra including variables, expressions, and equations.',
            'start_at' => $now->modify('+1 day')->format('Y-m-d H:i:s'),
            'end_at' => $now->modify('+1 hour')->format('Y-m-d H:i:s'),
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
        ],
        [
            'title' => 'Geometry Problem Solving',
            'description' => 'Master geometric shapes, angles, and spatial reasoning with practical problems.',
            'start_at' => $now->modify('+2 days')->format('Y-m-d H:i:s'),
            'end_at' => $now->modify('+1 hour')->format('Y-m-d H:i:s'),
            'meet_link' => 'https://meet.google.com/xyz-uvwx-yz',
        ],
        [
            'title' => 'Calculus Introduction',
            'description' => 'Introduction to limits, derivatives, and basic calculus concepts.',
            'start_at' => $now->modify('+3 days')->format('Y-m-d H:i:s'),
            'end_at' => $now->modify('+1 hour')->format('Y-m-d H:i:s'),
            'meet_link' => 'https://meet.google.com/pqr-stuv-wx',
        ],
    ];
    
    foreach ($classes as $class) {
        $stmt = $pdo->prepare("
            INSERT OR IGNORE INTO live_classes (tutor_id, title, description, start_at, end_at, meet_link, status)
            VALUES (?, ?, ?, ?, ?, ?, 'published')
        ");
        $stmt->execute([
            $tutorId,
            $class['title'],
            $class['description'],
            $class['start_at'],
            $class['end_at'],
            $class['meet_link'],
        ]);
    }
    
    echo "✓ Sample data seeded successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
