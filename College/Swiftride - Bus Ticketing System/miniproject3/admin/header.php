<?php
session_start();
// Set the default header title
$header_title = isset($header_title) ? $header_title : "Admin Dashboard"; // Default to "Admin Dashboard" if no title is set
$header_icon = isset($header_icon) ? $header_icon : "../assets/image/dashboard-icon.svg"; // Default icon

?>

<head>
    <link rel="stylesheet" href="../assets/styles/header-styles.css">
</head>

<header class="header">
    <div class="header-content">
        <img src="<?php echo $header_icon; ?>" alt="Icon" class="header-icon"> <!-- Dynamically display header icon -->
        <h1><?php echo $header_title; ?></h1> <!-- Dynamically display header title -->
        <nav>


                <a class="navbar-button">Menu</a>
                <div class="dropdown">
                    <a href="booking_management.php">Booking Management</a>
                    <a href="login.php">Logout</a>

                </div>
        </nav>
    </div>
</header>