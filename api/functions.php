<?php
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
    case "addStudents":
        $name  = $_POST['name'] ?? '';
        $class = $_POST['class'] ?? '';

        if (empty($name) || empty($class)) {
            response("error", "Missing required fields");
        }
        
        $stmt = $conn->prepare("INSERT INTO info (name, class) VALUES (?, ?)");

        $stmt->bind_param("ss", $name, $class);

        if ($stmt->execute()) {
            response("success", "Student added successfully");
        } else {
            response("error", "Failed to add student: " . $stmt->error);
        }
        break;

switch ($action) {
    case "getStudents":
        $keyword = $_POST['search'] ?? '';
        $where = "";
        if (!empty($keyword)) {
            $where = "WHERE name LIKE '%" . $conn->real_escape_string($keyword) . "%' ";
        }
        $sql = "SELECT * FROM info ".$where;   
        $result = $conn->query($sql);
    
        if ($result->num_rows === 0) {
            response("success", []); 
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
