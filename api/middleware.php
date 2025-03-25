<?php
require "db_connect.php";
require "../vendor/autoload.php";  // 确保 JWT 库已安装

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secret_key = "your_secret_key";

function authenticate($secret_key) {
    $headers = getallheaders();

    // 🛠 解决 `Authorization` 头丢失的问题
    $token = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: Missing token"]);
        exit;
    }

    // 解析 Bearer Token
    $token = str_replace("Bearer ", "", $token);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, "HS256"));
        return $decoded->user_id;
    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: Token has expired"]);
        exit;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized: Invalid token"]);
        exit;
    }
}
?>
