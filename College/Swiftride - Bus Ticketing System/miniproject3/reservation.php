<?php
include 'includes/db_connection.php';
$header_title = "Reservation Form";
$header_icon = "assets/image/ticket-icon.png";
include 'includes/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit(); // Stop further execution
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $user_id = $_SESSION['user_id']; //`user_id` is stored in session
    $purpose = $_POST['purpose'];
    $event_start = $_POST['event_start'];
    $event_end = $_POST['event_end'];
    $number_of_student = (int)$_POST['number_of_student'];
    $number_of_staff = (int)$_POST['number_of_staff'];
    $assembly_point = $_POST['assembly_point'];
    $destination = $_POST['destination'];
    $destination_latitude = (float)$_POST['destination_latitude'];
    $destination_longitude = (float)$_POST['destination_longitude'];
    $distance = (float)$_POST['distance'];
    $eta = $_POST['eta']; // ETA as string
    $assembly_time = $_POST['assembly_time']; // Assembly time as string

    // Calculate total allowance
    $allowance_per_student = 15; // RM 15 per student
    $allowance = $number_of_student * $allowance_per_student;

    // Insert reservation into the database
    $stmt = $conn->prepare("
        INSERT INTO reservations (
            user_id, purpose, event_start, event_end, status,
            number_of_student, number_of_staff, assembly_point, destination,
            destination_latitude, destination_longitude, distance, eta, assembly_time, allowance
        ) VALUES (
            :user_id, :purpose, :event_start, :event_end, 'pending',
            :number_of_student, :number_of_staff, :assembly_point, :destination,
            :destination_latitude, :destination_longitude, :distance, :eta, :assembly_time, :allowance
        )
    ");

    try {
        $stmt->execute([
            ':user_id' => $user_id,
            ':purpose' => $purpose,
            ':event_start' => $event_start,
            ':event_end' => $event_end,
            ':number_of_student' => $number_of_student,
            ':number_of_staff' => $number_of_staff,
            ':assembly_point' => $assembly_point,
            ':destination' => $destination,
            ':destination_latitude' => $destination_latitude,
            ':destination_longitude' => $destination_longitude,
            ':distance' => $distance,
            ':eta' => $eta,
            ':assembly_time' => $assembly_time,
            ':allowance' => $allowance
        ]);
        echo "<p>Reservation successfully created!</p>";
    } catch (PDOException $e) {
        echo "<p>Error creating reservation: " . $e->getMessage() . "</p>";
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Reservation Form</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="assets/styles/form.css">
</head>

<body>

    <div class="container">

        <div class="map-container">
            <div id="map"></div>
        </div>
        <div class="form-container">

            <form id="bookingForm" method="POST" action="">
                <h3> Event Details:</h3>

                <div class="input-group">
                    <label for="purpose">Event Name:</label>
                    <input type="text" id="purpose" name="purpose" required>
                </div>


                <div class="input-group">
                    <label for="event_start">From:</label>
                    <input type="datetime-local" id="event_start" name="event_start" required>
                </div>

                <div class="input-group">
                    <label for="event_end">To:</label>
                    <input type="datetime-local" id="event_end" name="event_end" required>
                </div>

                <h3> Passenger Details:</h3>

                <div class="input-group">
                    <label for="number_of_student">Number of Students:</label>
                    <input type="number" id="number_of_student" name="number_of_student" min="1" required>
                </div>

                <div class="input-group">
                    <label for="number_of_staff">Number of Staff (Optional):</label>
                    <input type="number" id="number_of_staff" name="number_of_staff" min="0" required>
                </div>

                <h3> Trip Details:</h3>
                <div class="input-group">
                    <label for="assembly_point">Assembly Point:</label>
                    <select id="assembly_point" name="assembly_point" required>
                        <option value="" disabled selected>Select Assembly Point</option>
                        <option value="Front of Feisol Hassan-Halle">Front of Feisol Hassan-Halle</option>
                        <option value="Hostel Assembly Area">Hostel Assembly Area</option>
                        <option value="Campus Assembly Area">Campus Assembly Area</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="destination">Destination:</label>
                    <input type="text" id="destination" name="destination" placeholder="Search or click on map" required>
                </div>
                <ul id="suggestions" class="suggestions"></ul>

                <div class="input-group">
                    <label for="assembly_time">Assembly Time:</label>
                    <input type="datetime-local" id="assembly_time" name="assembly_time" required readonly>
                </div>

                <div class="button-group">
                <button type="button" onclick="clearForm()" class="btn-clear">Clear</button>
                <button type="submit" class="btn-submit">Submit Reservation </button>
                </div>

                <input type="number" id="distance" name="distance" style="display: none;" required readonly>
                <input type="text" id="eta" name="eta" style="display: none;" required readonly>
                <input type="number" id="destination_latitude" name="destination_latitude" style="display: none;" required readonly>
                <input type="number" id="destination_longitude" name="destination_longitude" style="display: none;" required readonly>

            </form>
        </div>
    </div>
    <script src="assets/js/reservation-script.js"></script>
</body>

</html>