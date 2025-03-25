<?php
require "db_connect.php";
require "middleware.php"; // 确保用户已登录
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

// 验证 JWT Token
$secret_key = "your_secret_key";
$user_id = authenticate($secret_key); 

// 接收前端数据
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['room_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing room_id"]);
    exit;
}

$room_id = $data['room_id'];

try {
    $pdo->beginTransaction();

    // 检查该房间是否有未完成的预订
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE room_id = ? AND check_out_date >= CURDATE()");
    $stmt->execute([$room_id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Room has active bookings and cannot be deleted"]);
        $pdo->rollBack();
        exit;
    }

    // 标记房间为 unavailable
    $stmt = $pdo->prepare("UPDATE rooms SET status = 'unavailable' WHERE id = ?");
    $stmt->execute([$room_id]);

    $pdo->commit();
    echo json_encode(["message" => "Room marked as unavailable"]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Failed to delete room", "error" => $e->getMessage()]);
}
?>
