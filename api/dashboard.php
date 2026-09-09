<?php
// api/dashboard.php - VERSIÓN SIMPLE SIN $stmt
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'stats':
            $stats = [];
            
            // Total pedidos
            $result = $conn->query("SELECT COUNT(*) as total FROM Pedido");
            $stats['total_pedidos'] = $result->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Pedidos completados
            $result = $conn->query("SELECT COUNT(*) as total FROM Pedido WHERE Estado = 'Completado'");
            $stats['pedidos_completados'] = $result->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Pedidos pendientes
            $result = $conn->query("SELECT COUNT(*) as total FROM Pedido WHERE Estado = 'Pendiente'");
            $stats['pedidos_pendientes'] = $result->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Productos en zona
            $result = $conn->query("SELECT COUNT(*) as total FROM Esta");
            $stats['productos_en_zona'] = $result->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'recent_orders':
            $query = "SELECT p.*, c.CI_cliente, pe.Nombre_completo as cliente_nombre
                      FROM Pedido p
                      LEFT JOIN Recibe_entrega r ON p.ID_pedido = r.ID_pedido
                      LEFT JOIN Cliente c ON r.CI_cliente = c.CI_cliente
                      LEFT JOIN Persona pe ON c.CI_cliente = pe.CI
                      ORDER BY p.Fecha_ingresado DESC
                      LIMIT 10";
            $result = $conn->query($query);
            $orders = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $orders]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>