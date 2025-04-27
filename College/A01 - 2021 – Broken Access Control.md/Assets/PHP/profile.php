<?php
session_start();
$conn = new mysqli("localhost", "root", "", "demo");

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

$id = $_GET["id"];
$sql = "SELECT * FROM users WHERE id='$id'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "<h2>Profile of " . htmlspecialchars($user["username"]) . "</h2>";
    echo "Role: " . htmlspecialchars($user["role"]);
} else {
    echo "User not found.";
}
?>
<br><br>
<a href="admin.php">Admin Page</a> | <a href="logout.php">Logout</a>
