<?php
require "api/db_connect.php";

$users = [
    ['John Doe', 'john@example.com', password_hash('password123', PASSWORD_BCRYPT)],
    ['Alice Smith', 'alice@example.com', password_hash('password123', PASSWORD_BCRYPT)]
];

foreach ($users as $user) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute($user);
}

echo "Users inserted successfully!";
?>
