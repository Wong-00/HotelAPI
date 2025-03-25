<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>User Registration</h2>
    <form id="registerForm">
        <label for="name">Name:</label>
        <input type="text" id="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p id="message"></p>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let name = document.getElementById("name").value;
            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;

            fetch("http://localhost/HotelAPI/api/auth.php?action=register", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name, email, password })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("message").textContent = data.message;
                if (data.message === "User created successfully") {
                    setTimeout(() => window.location.href = "login.php", 2000);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
