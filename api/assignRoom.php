<?php
require "db_connect.php";
require "middleware.php";  // 确保用户已登录
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit;
}

// **验证 JWT Token**
$secret_key = "your_secret_key"; 
$user_id = authenticate($secret_key); // 从 token 获取 user_id

// **接收前端数据**
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['room_id']) || empty($data['check_in_date']) || empty($data['check_out_date'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}

$room_id = $data['room_id'];
$check_in_date = $data['check_in_date'];
$check_out_date = $data['check_out_date'];

try {
    // **开启事务**
    $pdo->beginTransaction();

    // **检查房间是否存在**
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        http_response_code(400);
        echo json_encode(["message" => "Room does not exist"]);
        $pdo->rollBack();
        exit;
    }

    // **检查时间段是否已被预订**
    $stmt = $pdo->prepare("
        SELECT * FROM bookings 
        WHERE room_id = ? 
        AND (
            (check_in_date <= ? AND check_out_date > ?) 
            OR 
            (check_in_date < ? AND check_out_date >= ?) 
            OR 
            (check_in_date >= ? AND check_out_date <= ?)
        )
    ");
    $stmt->execute([$room_id, $check_in_date, $check_in_date, $check_out_date, $check_out_date, $check_in_date, $check_out_date]);
    $existing_booking = $stmt->fetch();

    if ($existing_booking) {
        http_response_code(400);
        echo json_encode(["message" => "Room is already booked during this period"]);
        $pdo->rollBack();
        exit;
    }

    // **插入 booking 记录**
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $room_id, $check_in_date, $check_out_date]);

    // **提交事务**
    $pdo->commit();

    echo json_encode(["message" => "Room booked successfully"]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Failed to book room", "error" => "Internal Server Error"]);
}
?>
