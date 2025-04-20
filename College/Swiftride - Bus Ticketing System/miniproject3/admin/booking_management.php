<?php
$header_icon = "../assets/image/ticket-icon.png";
$header_title = "Booking Management";
include 'header.php';
include '../includes/db_connection.php'; // Assumes $conn is a PDO instance

// Fetch pending reservations from the database
try {
    $stmt_pending = $conn->prepare("SELECT * FROM reservations WHERE status = 'pending'");
    $stmt_pending->execute();
    $pending_reservations = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

    $stmt_approved = $conn->prepare("SELECT * FROM reservations WHERE status = 'approved'");
    $stmt_approved->execute();
    $approved_reservations = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    exit();
}

// Handle form submission for action buttons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];
    $driver = $_POST['driver'];
    $transport = $_POST['transport'];

    if ($action === 'approve') {
        $update_stmt = $conn->prepare(
            "UPDATE reservations SET status = 'approved', driver = ?, transport = ? WHERE id = ?"
        );
        $update_stmt->execute([$driver, $transport, $reservation_id]);
    } elseif ($action === 'reject') {
        $update_stmt = $conn->prepare(
            "UPDATE reservations SET status = 'rejected', driver = ?, transport = ? WHERE id = ?"
        );
        $update_stmt->execute([$driver, $transport, $reservation_id]);
    }

    header('Location: booking_management.php');
    exit();
}
?>

<style>
    /* General container styling */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 15px;
    }

    /* Header section */
    h2 {
        color: #333;
        margin-bottom: 10px;
    }

    /* Table styles */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    .table th {
        background-color: #f2f2f2;
        color: #555;
    }

    .table-striped tbody tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    /* Button styles */
    .btn {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.2s ease-in-out;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn:hover {
        transform: scale(1.1);
    }

    /* Dropdown menu styles */
    select.form-control {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table th,
        .table td {
            font-size: 12px;
        }

        .btn {
            padding: 3px 5px;
        }
    }
</style>

<div class="container">
    <hr>

    <!-- Pending Reservations Section -->
    <h2>Pending Reservations</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>User ID</th>
                <th>Purpose</th>
                <th>Date</th>
                <th>Destination</th>
                <th>Passengers</th>
                <th>Driver</th>
                <th>Transport</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_reservations as $reservation): ?>
                <tr>
                    <form method="POST" action="">
                        <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['event_start']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['destination']); ?></td>
                        <td>Student: <?php echo htmlspecialchars($reservation['number_of_student']); ?><br>
                       Staff: <?php echo htmlspecialchars($reservation['number_of_staff']); ?></td>
                        <td>
                            <select name="driver" class="form-control">
                                <option value="<?php echo htmlspecialchars($reservation['driver']); ?>">
                                    <?php echo htmlspecialchars($reservation['driver']); ?>
                                </option>
                                <option value="Michael Schumacher">Michael Schumacher</option>
                                <option value="Lewis Hamilton">Lewis Hamilton</option>
                                <option value="Max Verstappen">Max Verstappen</option>
                                <option value="Sebastian Vettel">Sebastian Vettel</option>
                            </select>
                        </td>

                        <td>
                            <select name="transport" class="form-control">
                                <option value="<?php echo htmlspecialchars($reservation['transport']); ?>">
                                    <?php echo htmlspecialchars($reservation['transport']); ?>
                                </option>
                                <option value="BUS-001">BUS-001</option>
                                <option value="VAN-002">VAN-002</option>
                                <option value="BUS-003">BUS-003</option>
                                <option value="MPV-004">MPV-004</option>
                            </select>
                        </td>

                        <!-- Action Buttons -->
                        <td>
                            <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['id']); ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <!-- Approved Reservations Section -->
    <h2>Approved Reservations</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Purpose</th>
                <th>Date</th>
                <th>Driver</th>
                <th>Transport</th>
                <th>Allowance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($approved_reservations as $reservation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['event_start']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['driver']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['transport']); ?></td>
                    <td>RM <?php echo htmlspecialchars($reservation['allowance']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
