<?php
// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

function response($status, $data = null) {
    echo json_encode([
        "status" => $status,
        "data"   => $data
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!$action) {
    response("error", "Missing 'action' parameter");
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "students";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    response("error", "Database connection failed: " . $conn->connect_error);
}

switch ($action) {
    case "getStudents":
        $keyword = $_POST['search'] ?? '';
        $where = "";
        if (!empty($keyword)) {
            $where = "WHERE name LIKE '%" . $conn->real_escape_string($keyword) . "%' ";
        }
        $sql = "SELECT * FROM info ".$where;   // Adjust columns if needed
        $result = $conn->query($sql);
    
        if ($result->num_rows === 0) {
            response("success", []);  // No students found
        }
    
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    
        response("success", $students);
        break;
    default:
        response("error", "Unknown action: $action");
}
?>
