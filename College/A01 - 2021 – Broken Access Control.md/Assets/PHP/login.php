<?php
session_start();
$conn = new mysqli("localhost", "root", "", "demo");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION["user"] = $result->fetch_assoc();
        header("Location: profile.php?id=" . $_SESSION["user"]["id"]);
        exit();
    } else {
        echo "Invalid login!";
    }
}
?>

<form method="POST">
  <h2>Login</h2>
  Username: <input type="text" name="username" required><br><br>
  Password: <input type="password" name="password" required><br><br>
  <button type="submit">Login</button>
</form>
