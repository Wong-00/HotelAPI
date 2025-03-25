<?php
require 'db_connect.php';

$pdo->beginTransaction(); 
try {
    $pdo->exec("DELETE FROM rooms");

    $room_types = [
        ["Single", 50, 2, 40],  
        ["Double", 70, 3, 30],  
        ["Suite", 100, 4, 20],   
        ["Deluxe", 150, 4, 10]   
    ];

    $status = "available";
    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, price, capacity, status) VALUES (?, ?, ?, ?, ?)");

    $room_id = 1;
    foreach ($room_types as $type) {
        list($type_name, $price, $capacity, $count) = $type;
        
        for ($i = 1; $i <= $count; $i++) {
            $room_number = sprintf("R%03d", $room_id); // R001, R002, ...
            $stmt->execute([$room_number, $type_name, $price, $capacity, $status]);
            $room_id++;
        }
    }

    $pdo->commit(); // 提交事务
    echo "✅ Rooms inserted successfully based on room type allocation!";
} catch (Exception $e) {
    $pdo->rollBack(); // 发生错误时回滚
    echo "❌ Failed: " . $e->getMessage();
}
?>
