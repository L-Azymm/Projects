<?php
// profile.php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
// Simulate user data
$users = [
    'userA' => ['role' => 'user', 'name' => 'User A'],
    'userB' => ['role' => 'user', 'name' => 'User B'],
    'admin' => ['role' => 'admin', 'name' => 'Administrator']
];

$user_info = $users[$username];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="profile-container">
        <h2>Welcome, <?php echo $user_info['name']; ?>!</h2>
        <p>Role: <?php echo ucfirst($user_info['role']); ?></p>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
