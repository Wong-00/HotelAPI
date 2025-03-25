<?php
require 'db_connect.php';
require 'middleware.php';

header("Content-Type: application/json");

// **获取所有可用房间**
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getAvailableRooms') {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available'");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($rooms);
}

// **更新房间信息**
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $_GET['action'] === 'updateRoom') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['id']) || !isset($data['price']) || !isset($data['status'])) {
        echo json_encode(["message" => "Missing fields"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE rooms SET price = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$data['price'], $data['status'], $data['id']])) {
        echo json_encode(["message" => "Room updated successfully"]);
    } else {
        echo json_encode(["message" => "Failed to update room"]);
    }
}
?>
