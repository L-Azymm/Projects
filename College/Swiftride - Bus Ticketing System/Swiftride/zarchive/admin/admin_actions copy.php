<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE reservations SET status = 'approved' WHERE id = ?");
        $stmt->execute([$reservation_id]);
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE reservations SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$reservation_id]);
    }

    header("Location: admin_dashboard.php");
    exit();
}
?>
