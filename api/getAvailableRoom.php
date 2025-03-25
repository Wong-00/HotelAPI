<?php
require 'db_connect.php';
header("Content-Type: application/json");

// 获取查询参数
$type = $_GET['type'] ?? null;
$min_price = $_GET['min_price'] ?? null;
$max_price = $_GET['max_price'] ?? null;
$min_capacity = $_GET['min_capacity'] ?? null;
$max_capacity = $_GET['max_capacity'] ?? null;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Pagination limit
$offset = ($page - 1) * $limit;

// SQL check
$query = "SELECT * FROM rooms WHERE status = 'available'";
$params = [];
$filters = [];

// Filter conditions
if ($type) {
    $filters[] = "room_type = ?";
    $params[] = $type;
}
if ($min_price) {
    $filters[] = "price >= ?";
    $params[] = (float)$min_price;
}
if ($max_price) {
    $filters[] = "price <= ?";
    $params[] = (float)$max_price;
}
if ($min_capacity) {
    $filters[] = "capacity >= ?";
    $params[] = (int)$min_capacity;
}
if ($max_capacity) {
    $filters[] = "capacity <= ?";
    $params[] = (int)$max_capacity;
}

if (!empty($filters)) {
    $query .= " AND " . implode(" AND ", $filters);
}

// Total rooms count
$countQuery = "SELECT COUNT(*) AS total FROM rooms WHERE status = 'available'";
$countParams = $params; // Copy params

if (!empty($filters)) {
    $countQuery .= " AND " . implode(" AND ", $filters);
}

// add pagination
$query .= " LIMIT $limit OFFSET $offset";

try {
    // get rooms
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // get total rooms count
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalRooms = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRooms / $limit);

    // response Json
    echo json_encode([
        "total_rooms" => $totalRooms,
        "total_pages" => $totalPages,
        "current_page" => $page,
        "rooms" => $rooms
    ]);
} catch (PDOException $e) {
    echo json_encode(["message" => "Failed to fetch rooms", "error" => $e->getMessage()]);
}
?>
