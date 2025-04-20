<?php
$header_title = "User Dashboard";
$header_icon = "assets/image/dashboard-icon.svg";
include 'includes/header.php';

include 'includes/calendar.php';
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit();
}

include 'includes/db_connection.php';

// Fetch user reservations
$stmt = $conn->prepare("SELECT * FROM reservations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reservation deletion
if (isset($_GET['delete'])) {
    $reservation_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    header("Location: user_dashboard.php"); // Redirect to refresh the page
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard | Swiftride</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .dashboard-container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            padding: 20px;
            gap: 20px;
        }


        .list-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            background-color: #A7C7E7;
            padding: 10px;
            color: #000000;
            border: 3px solid #000000;
            margin-bottom: 10;
        }

        .list-container {

            margin: 10 auto;
            height: auto;
            background: #ffffff;
            padding: 10px;
            border: 2px solid #000000;
        }

        .list-box,
        .calendar-box {
            flex: 1;
            background-color: white;
            border: 2px solid #ccc;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .list-content {
            max-height: 400px;
            overflow-y: scroll;
            padding: 10px;
        }

        .reservation-item {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border: 2px solid #000;
        }


        .toggle-buttons {
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
            padding: 10px;

        }

        .toggle-buttons button {
            flex: 1;
            margin: 0 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: 2px solid #000;
            background-color: #f2f2f2;
        }

        .toggle-buttons button.active {
            background-color: #A7C7E7;
            color: #000;
        }

        .calendar-wrapper {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <div class="list-box">
            <div class="list-container">
                <div class="list-title">Your Reservations</div>
                <div class="toggle-buttons">
                    <button id="pending-button" class="active" onclick="toggleList('pending')">Pending</button>
                    <button id="approved-button" onclick="toggleList('approved')">Approved</button>
                </div>
                <div id="pending-list" class="list-content">
                    <?php foreach ($reservations as $reservation): ?>
                        <?php if ($reservation['status'] === 'pending'): ?>
                            <div class="reservation-item">
                                <p><b>Ticket ID:</b> <?= htmlspecialchars($reservation['id']) ?></p>
                                <p><b>Event Name:</b> <?= htmlspecialchars($reservation['purpose']) ?></p>
                                <p><b>Event Date:</b> <?= htmlspecialchars($reservation['event_start']) ?></p>
                                <p><b>Destination:</b> <?= htmlspecialchars($reservation['destination']) ?></p>
                                <a href="?delete=<?= $reservation['id'] ?>" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div id="approved-list" class="list-content" style="display: none;">
                    <?php foreach ($reservations as $reservation): ?>
                        <?php if ($reservation['status'] === 'approved'): ?>
                            <div class="reservation-item">
                                <p><b>Ticket ID:</b> <?= htmlspecialchars($reservation['id']) ?></p>
                                <p><b>Event Name:</b> <?= htmlspecialchars($reservation['purpose']) ?></p>
                                <p><b>Event Date:</b> <?= htmlspecialchars($reservation['event_start']) ?> <b> >> </b> <?= htmlspecialchars($reservation['event_end']) ?></p>
                                <p><b>Assembly Time:</b> <?= htmlspecialchars($reservation['assembly_time']) ?></p>
                                <p><b>Assembly Point:</b> <?= htmlspecialchars($reservation['assembly_point']) ?></p>
                                <p><b>Destination:</b> <?= htmlspecialchars($reservation['destination']) ?></p>
                                <p><b>Driver:</b> <?= htmlspecialchars($reservation['driver']) ?></p>
                                <p><b>Transport:</b> <?= htmlspecialchars($reservation['transport']) ?></p>

                                <a href="?delete=<?= $reservation['id'] ?>" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="calendar-box">
            <div class="calendar-wrapper">
                <?php render_calendar($month, $year, $conn); ?>
            </div>
        </div>
    </div>

    <script>
        function toggleList(status) {
            const pendingList = document.getElementById('pending-list');
            const approvedList = document.getElementById('approved-list');
            const pendingButton = document.getElementById('pending-button');
            const approvedButton = document.getElementById('approved-button');

            if (status === 'pending') {
                pendingList.style.display = 'block';
                approvedList.style.display = 'none';
                pendingButton.classList.add('active');
                approvedButton.classList.remove('active');
            } else {
                pendingList.style.display = 'none';
                approvedList.style.display = 'block';
                approvedButton.classList.add('active');
                pendingButton.classList.remove('active');
            }
        }
    </script>

</body>

</html>