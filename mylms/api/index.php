<?php
/**
 * Fun Maths Mastery - Basic API Router
 * This acts as the single entry point for all API calls.
 */

// Basic Security headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the requested URI (e.g. /api/health)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Enforce /api base mapping
if ($uri[1] !== 'api') {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["message" => "Endpoint not found."]);
    exit();
}

$endpoint = isset($uri[2]) ? $uri[2] : null;

// Routing logic
switch ($endpoint) {
    case 'health':
        require_once 'controllers/HealthController.php';
        $controller = new HealthController();
        $controller->processRequest($_SERVER["REQUEST_METHOD"]);
        break;

    case 'auth':
        // Placeholder for AuthController
        echo json_encode(["status" => "pending", "message" => "Auth endpoint under construction."]);
        break;

    case 'products':
        // Placeholder for ProductsController
        echo json_encode(["status" => "pending", "message" => "Products endpoint under construction."]);
        break;

    default:
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["message" => "API Endpoint missing or invalid."]);
        break;
}
