<?php
// profile.php

session_start();

// Simulate a database of users with roles
$users = [
    1 => ['name' => 'User A', 'email' => 'usera@example.com', 'role' => 'User'],
    2 => ['name' => 'User B', 'email' => 'userb@example.com', 'role' => 'User'],
    3 => ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'Admin']
];

// If the user is not logged in, redirect them to login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from the URL (this simulates the vulnerability)
$user_id_from_url = $_GET['id']; // Use the `id` in the URL

// Check if the `id` exists in the users array
if (array_key_exists($user_id_from_url, $users)) {
    $user = $users[$user_id_from_url]; // Fetch the user data based on URL ID
} else {
    // If no user found, show an error
    die("User not found.");
}
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
        <h2>Welcome, <?php echo $user['name']; ?>!</h2>
        <p>Email: <?php echo $user['email']; ?></p>
        <p>Role: <?php echo $user['role']; ?></p> <!-- Display role -->
        <p>User ID: <?php echo $user_id_from_url; ?></p>
        <!-- Display other profile details -->
    </div>
</body>
</html>
