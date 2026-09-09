<?php
// api/login.php - VERSIÓN SIMPLE (SOLO TEXTO PLANO)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['usuario']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $usuario = $data['usuario'];
    $password = $data['password'];
    
    // Consulta para obtener el usuario
    $query = "SELECT p.*, o.Rol 
              FROM Persona p
              LEFT JOIN Operario o ON p.CI = o.CI_operario
              WHERE p.CI = '$usuario' OR p.Mail = '$usuario'";
    
    $result = $conn->query($query);
    $user = $result->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si existe el usuario
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // 🔑 COMPARACIÓN DIRECTA EN TEXTO PLANO
    if ($password === $user['Contrasena']) {
        // Actualizar último acceso
        $update = "UPDATE Persona SET Ultimo_acceso = NOW() WHERE CI = '{$user['CI']}'";
        $conn->query($update);
        
        // Determinar rol
        $rol = $user['Rol'] ?? 'Cliente';
        
        echo json_encode([
            'success' => true,
            'user' => [
                'CI' => $user['CI'],
                'Nombre_completo' => $user['Nombre_completo'],
                'Mail' => $user['Mail'],
                'rol' => $rol
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>