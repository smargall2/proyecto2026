<?php
// api/perfil.php - VERSIÓN SIMPLE SIN $stmt
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $ci = $_GET['ci'] ?? null;
        if (!$ci) {
            echo json_encode(['success' => false, 'message' => 'CI requerido']);
            exit;
        }
        
        try {
            $query = "SELECT p.*, o.Rol 
                      FROM Persona p
                      LEFT JOIN Operario o ON p.CI = o.CI_operario
                      WHERE p.CI = '$ci'";
            $result = $conn->query($query);
            $user = $result->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $user]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $query = "UPDATE Persona 
                      SET Nombre_completo = '{$data['Nombre_completo']}',
                          Telefono = '{$data['Telefono']}',
                          Mail = '{$data['Mail']}',
                          Direccion = '{$data['Direccion']}'
                      WHERE CI = '{$data['CI']}'";
            $conn->query($query);
            
            echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Subir foto de perfil
        if (isset($_FILES['foto'])) {
            $ci = $_POST['CI'] ?? null;
            if (!$ci) {
                echo json_encode(['success' => false, 'message' => 'CI requerido']);
                exit;
            }
            
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'perfil_' . $ci . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $filepath)) {
                echo json_encode([
                    'success' => true,
                    'foto_url' => 'uploads/' . $filename,
                    'message' => 'Foto actualizada'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al subir la foto']);
            }
        }
        break;
}
?>