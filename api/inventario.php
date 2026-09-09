<?php
// api/inventario.php - VERSIÓN SIMPLE SIN $stmt
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
        case 'zones':
            $query = "SELECT z.*, 
                      COUNT(e.ID_producto) as stock_actual,
                      z.Capacidad,
                      ROUND((COUNT(e.ID_producto) / z.Capacidad) * 100) as stock_percent
                      FROM Zona z
                      LEFT JOIN Esta e ON z.ID_zona = e.ID_zona
                      GROUP BY z.ID_zona";
            $result = $conn->query($query);
            $zones = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $zones]);
            break;
            
        case 'products':
            $query = "SELECT p.*, z.Nombre as zona_nombre, z.ID_zona
                      FROM Producto p
                      LEFT JOIN Esta e ON p.ID_producto = e.ID_producto
                      LEFT JOIN Zona z ON e.ID_zona = z.ID_zona
                      ORDER BY p.ID_producto";
            $result = $conn->query($query);
            $products = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $products]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>