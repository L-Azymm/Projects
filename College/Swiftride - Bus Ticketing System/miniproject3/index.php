<?php
include 'includes/db_connection.php';

$header_title = "Home";
$header_icon = "assets/image/home-icon.png";
include 'includes/header.php';
include 'includes/calendar.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="assets/image/favicon.png" type="image/x-icon">
    <title>Homepage | Swiftride</title>
</head>

<body>
    <div class="container">
        <!-- Right-aligned calendar -->
        <div class="calendar-wrapper">
            <?php render_calendar($month, $year, $conn); ?>
        </div>
    </div>
</body>

</html>