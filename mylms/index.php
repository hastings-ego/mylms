<?php
/**
 * Fun Maths Mastery - Development Router
 * Run this with: `php -S localhost:8000 router.php`
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the URI starts with /api, route it to our API gateway
if (preg_match('/^\/api\//', $uri)) {
    $_SERVER['SCRIPT_NAME'] = '/api/index.php';
    require __DIR__ . '/api/index.php';
    exit;
}

// Redirect root to public/index.html
if ($uri === '/' || $uri === '') {
    header("Location: /public/index.html");
    exit;
}

// Otherwise, serve static files (treating '/' as the root workspace)
// Actually it's cleaner to serve directly if it exists
if (file_exists(__DIR__ . $uri)) {
    return false; // let the built-in server serve the static file
}

// Fallback 404
header("HTTP/1.1 404 Not Found");
echo "404 Not Found";
?>
