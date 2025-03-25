<?php
require "db_connect.php";
require "middleware.php"; 
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

// 验证 JWT Token
$secret_key = "your_secret_key";
$user_id = authenticate($secret_key);

// 接收前端数据
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['room_id']) || (!isset($data['price']) && !isset($data['status']) && !isset($data['room_type']))) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}

$room_id = $data['room_id'];
$price = isset($data['price']) ? $data['price'] : null;
$status = isset($data['status']) ? $data['status'] : null;
$room_type = isset($data['room_type']) ? $data['room_type'] : null;

try {
    $pdo->beginTransaction();

    // 确保房间存在
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        http_response_code(400);
        echo json_encode(["message" => "Room does not exist"]);
        $pdo->rollBack();
        exit;
    }

    // 如果房间已被预订，不能改为 available
    if ($status === 'available') {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_id = ? AND check_out_date >= CURDATE()");
        $stmt->execute([$room_id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(["message" => "Cannot change status to available while room is booked"]);
            $pdo->rollBack();
            exit;
        }
    }

    // 更新房间信息
    $query = "UPDATE rooms SET ";
    $params = [];
    if ($price !== null) {
        $query .= "price = ?, ";
        $params[] = $price;
    }
    if ($status !== null) {
        $query .= "status = ?, ";
        $params[] = $status;
    }
    if ($room_type !== null) {
        $query .= "room_type = ?, ";
        $params[] = $room_type;
    }

    $query = rtrim($query, ', ') . " WHERE id = ?";
    $params[] = $room_id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $pdo->commit();
    echo json_encode(["message" => "Room updated successfully"]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Failed to update room", "error" => $e->getMessage()]);
}
?>
