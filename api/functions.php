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
    case "getStudentsDetails":
        $id = $_POST['id'];
        $sql = "SELECT * FROM info WHERE id = " . intval($id);   
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
    case "addStudents":
        $name  = $_POST['name'] ?? '';
        $class = $_POST['class'] ?? '';

        if (empty($name) || empty($class)) {
            response("error", "Missing required fields");
        }

        $created_at = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO info (name, class, created_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $class, $created_at);

        if ($stmt->execute()) {
            response("success", "Student added successfully");
        } else {
            response("error", "Failed to add student: " . $stmt->error);
        }
        break;
    case "editStudents":
        $id    = $_POST['id'] ?? '';
        $name  = $_POST['name'] ?? '';
        $class = $_POST['class'] ?? '';

        if (empty($id) || empty($name) || empty($class)) {
            response("error", "Missing required fields");
        }

        $stmt = $conn->prepare("UPDATE info SET name = ?, class = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $class, $id);

        if ($stmt->execute()) {
            response("success", "Student updated successfully");
        } else {
            response("error", "Failed to update student: " . $stmt->error);
        }
        break;
    case "deleteStudents":
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            response("error", "No Such id Detected");
        }

        $stmt = $conn->prepare("DELETE FROM info WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            response("success", "Student deleted successfully");
        } else {
            response("error", "Failed to delete student: " . $stmt->error);
        }
        break;
    default:
        response("error", "Unknown action: $action");
}

?>
