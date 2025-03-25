<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome to Dashboard</h2>
    <p><strong>Name:</strong> <span id="username">Loading...</span></p>
    <p><strong>Email:</strong> <span id="useremail">Loading...</span></p>

    <button onclick="logout()">Logout</button>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let token = localStorage.getItem("jwt_token");

            if (!token) {
                window.location.href = "login.php";
                return;
            }

            fetch("http://localhost/HotelAPI/api/auth.php?action=profile", {
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + token
                }
            })
            .then(response => response.text())  // 先用 text()，防止 JSON 解析失败
            .then(data => {
                console.log("API Response:", data); // 先检查 API 返回的数据

                try {
                    let jsonData = JSON.parse(data); // 解析 JSON
                    if (jsonData.message) {
                        localStorage.removeItem("jwt_token");
                        window.location.href = "login.php";
                    } else {
                        document.getElementById("username").textContent = jsonData.name || "Unknown";
                        document.getElementById("useremail").textContent = jsonData.email || "Unknown";
                    }
                } catch (error) {
                    console.error("JSON Parse Error:", error);
                    localStorage.removeItem("jwt_token");
                    window.location.href = "login.php";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                localStorage.removeItem("jwt_token");
                window.location.href = "login.php";
            });
        });

        function logout() {
            localStorage.removeItem("jwt_token");
            window.location.href = "login.php";
        }
    </script>
</body>
</html>
