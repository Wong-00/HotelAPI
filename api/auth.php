<?php
require 'db_connect.php';
require 'middleware.php';
require "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = getenv("JWT_SECRET_KEY") ?: "your_secret_key"; 

header("Content-Type: application/json");

// 获取 action，避免 `Undefined variable $action` 错误
$action = $_GET['action'] ?? '';

/** 📝 1️⃣ 用户注册 (Register) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(["message" => "Fill in all fields!"]);
        exit;
    }

    // 检查邮箱是否已存在
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);

    if ($stmt->fetch()) {
        echo json_encode(["message" => "Email already exists!"]);
    } else {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

        if ($stmt->execute([$data['name'], $data['email'], $hashed_password])) {
            echo json_encode(["message" => "User created successfully"]);
        } else {
            echo json_encode(["message" => "Failed to register!"]);
        }
    }
}

/** 📝 2️⃣ 用户登录 (Login) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(["message" => "Email and password are required"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['password'])) {
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + (60 * 5)  // Token 5 分钟后过期
        ];
        $jwt = JWT::encode($payload, $secret_key, "HS256");

        echo json_encode(["token" => $jwt]);  // 返回 JWT Token
    } else {
        echo json_encode(["message" => "Invalid credentials"]);
    }
}

/** 📝 3️⃣ 获取用户信息 (Profile) */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'profile') {
    $user_id = authenticate($secret_key);

    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(["message" => "User not found"]);
    }
}

/** 📝 4️⃣ 更新用户信息 (Update Profile) */
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $action === 'update_profile') {
    $user_id = authenticate($secret_key);
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name']) || empty($data['email'])) {
        echo json_encode(["message" => "Name and email are required"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$data['name'], $data['email'], $user_id])) {
        echo json_encode(["message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["message" => "Failed to update profile"]);
    }
}
?>
