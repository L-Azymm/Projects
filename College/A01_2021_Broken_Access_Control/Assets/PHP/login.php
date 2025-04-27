<?php
// login.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Simulate authentication (replace with database check)
    $users = [
        'userA' => ['password' => 'password123', 'id' => 1],
        'userB' => ['password' => 'password123', 'id' => 2],
        'admin' => ['password' => 'admin123', 'id' => 3]
    ];

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($users[$username]) && $users[$username]['password'] == $password) {
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $users[$username]['id']; // Store user ID in session
        header("Location: profile.php?id=" . $_SESSION['user_id']); // Redirect to profile page with user ID
        exit();
    } else {
        $error_message = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
