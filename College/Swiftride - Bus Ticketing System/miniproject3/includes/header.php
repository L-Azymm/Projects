<?php
session_start(); // Start the session to check user login status

// Set the default header title
$header_title = isset($header_title) ? $header_title : "Home"; // Default to "Home" if no title is set
$header_icon = isset($header_icon) ? $header_icon : "assets/image/home-icon.png"; // Default icon

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<head>
    <link rel="stylesheet" href="assets/styles/header-styles.css">
    <link rel="icon" href="assets/image/favicon.png" type="image/x-icon">
</head>

<header class="header">
    <div class="header-content">
        <img src="<?php echo $header_icon; ?>" alt="Icon" class="header-icon"> <!-- Dynamically display header icon -->
        <h1><?php echo $header_title; ?></h1> <!-- Dynamically display header title -->
        <nav>
            <a class="navbar-button">Menu</a>
            <div class="dropdown">
                <!-- Show Home only if the user is not logged in -->
                <?php if (!$is_logged_in): ?>
                    <a href="index.php">Home</a>
                <?php endif; ?>

                <!-- Only show dashboard link if the user is logged in -->
                <?php if ($is_logged_in): ?>
                    <a href="user_dashboard.php">Dashboard</a>
                <?php endif; ?>

                <!-- Only show reservation link if the user is logged in -->
                <?php if ($is_logged_in): ?>
                    <a href="reservation.php">Reservation</a>
                <?php endif; ?>

                <a href="about.php">About Us</a>

                <!-- Show Login/Register only if the user is not logged in -->
                <?php if (!$is_logged_in): ?>
                    <a href="login_register.php">Login/Regiser</a>
                <?php endif; ?>

                <!-- Show Logout option if the user is logged in -->
                <?php if ($is_logged_in): ?>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

