<?php
/**
 * Health Controller
 * Used for uptime monitoring and verifying the API server is functional.
 */
class HealthController {
    
    public function processRequest($method) {
        switch ($method) {
            case 'GET':
                $this->getHealthStatus();
                break;
            default:
                $this->methodNotAllowed();
                break;
        }
    }

    private function getHealthStatus() {
        // Here we could also test DB connection if needed
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $status = [
            "status" => "ok",
            "message" => "Fun Maths Mastery API is running smoothly.",
            "timestamp" => date("Y-m-d H:i:s"),
            "database_connected" => $db !== null
        ];
        
        http_response_code(200);
        echo json_encode($status);
    }
    
    private function methodNotAllowed() {
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["message" => "Method not allowed for /api/health."]);
    }
}
?>
