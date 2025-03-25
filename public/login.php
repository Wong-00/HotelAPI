<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>User Login</h2>
    <form id="loginForm">
        <label for="email">Email:</label>
        <input type="email" id="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p id="message"></p>

    <script>
        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;

            fetch("http://localhost/HotelAPI/api/auth.php?action=login", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    localStorage.setItem("jwt_token", data.token);  // **存储 JWT Token**
                    window.location.href = "dashboard.php";  // **跳转到 dashboard**
                } else {
                    document.getElementById("message").textContent = data.message;
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
