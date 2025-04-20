<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

include 'includes/db_connection.php';

// Fetch data for different sections
$stmt = $conn->prepare("SELECT * FROM reservations WHERE status = 'pending'");
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users
$stmt_users = $conn->prepare("SELECT * FROM users WHERE user_type = 'student' OR user_type = 'staff'");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Fetch all drivers
$stmt_drivers = $conn->prepare("SELECT * FROM drivers");
$stmt_drivers->execute();
$drivers = $stmt_drivers->fetchAll(PDO::FETCH_ASSOC);

// Fetch all transport types
$stmt_transport = $conn->prepare("SELECT * FROM transport");
$stmt_transport->execute();
$transport = $stmt_transport->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard</title>
    <style>
        nav {
            margin-bottom: 20px;
        }

        nav a {
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .section {
            display: none;
        }

        .active {
            display: block;
        }
    </style>
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            const targetSection = document.getElementById(sectionId);
            if (targetSection) targetSection.classList.add('active');
        }
    </script>
</head>

<body>
    <h2>Admin Dashboard</h2>
    <a href="logout.php">Logout</a>

    <nav>
        <a href="#" onclick="showSection('booking-management')">Booking Management</a>
        <a href="#" onclick="showSection('user-management')">User Management</a>
        <a href="#" onclick="showSection('driver-management')">Driver Management</a>
        <a href="#" onclick="showSection('transport-management')">Transport Management</a>
    </nav>

    <!-- Booking Management Section -->
    <div id="booking-management" class="section active">
        <h3>Pending Reservations</h3>
        <?php if (count($reservations) > 0): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Event Start</th>
                        <th>Event End</th>
                        <th>Assembly Point</th>
                        <th>Destination</th>
                        <th>Passengers</th>
                        <th>Transport</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= htmlspecialchars($reservation['purpose']) ?></td>
                            <td><?= htmlspecialchars($reservation['event_start']) ?></td>
                            <td><?= htmlspecialchars($reservation['event_end']) ?></td>
                            <td><?= htmlspecialchars($reservation['assembly_point']) ?></td>
                            <td><?= htmlspecialchars($reservation['destination']) ?></td>
                            <td>Students: <?= htmlspecialchars($reservation['number_of_student']) ?><br>
                                Staff: <?= htmlspecialchars($reservation['number_of_staff']) ?></td>
                            <td><?= htmlspecialchars($reservation['vehicle_type']) ?></td>
                            <td><?= htmlspecialchars($reservation['status']) ?></td>
                            <td>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="action" value="approve">Approve</button>
                                </form>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="action" value="reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending reservations.</p>
        <?php endif; ?>
    </div>

    <!-- User Management Section -->
    <div id="user-management" class="section">
        <h3>Manage Users</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Driver Management Section -->
    <div id="driver-management" class="section">
        <h3>Manage Drivers</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Driver Name</th>
                    <th>Contact</th>
                    <th>Vehicle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?= htmlspecialchars($driver['name']) ?></td>
                        <td><?= htmlspecialchars($driver['contact']) ?></td>
                        <td><?= htmlspecialchars($driver['vehicle']) ?></td>
                        <td>
                            <a href="edit_driver.php?id=<?= $driver['id'] ?>">Edit</a>
                            <a href="delete_driver.php?id=<?= $driver['id'] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Transport Management Section -->
    <div id="transport-management" class="section">
        <h3>Manage Transport</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Vehicle Type</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transport as $vehicle): ?>
                    <tr>
                        <td><?= htmlspecialchars($vehicle['type']) ?></td>
                        <td><?= htmlspecialchars($vehicle['capacity']) ?></td>
                        <td>
                            <a href="edit_transport.php?id=<?= $vehicle['id'] ?>">Edit</a>
                            <a href="delete_transport.php?id=<?= $vehicle['id'] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
