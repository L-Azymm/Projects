<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// ⚠️ Vulnerable: no check if user is really admin!

echo "<h2>Welcome to the Admin Panel</h2>";
echo "<p>Only admin should access this!</p>";
?>
<br><br>
<a href="profile.php?id=<?php echo $_SESSION['user']['id']; ?>">Back to Profile</a>
