<head>
    <link rel="stylesheet" href="assets/styles/calendar-styles.css">
</head>

<?php
function render_calendar($month, $year, $conn)
{
    $current_date = new DateTime();
    $min_date = clone $current_date;

    // Calculate the previous and next month/year
    $previous_month = $month - 1;
    $next_month = $month + 1;
    $previous_year = $year;
    $next_year = $year;

    if ($previous_month == 0) {
        $previous_month = 12;
        $previous_year--;
    }
    if ($next_month == 13) {
        $next_month = 1;
        $next_year++;
    }

    // Fetch reservations for the given month and year
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(event_start, '%Y-%m-%d') AS date, status
        FROM reservations 
        WHERE DATE_FORMAT(event_start, '%Y-%m') = ?
    ");
    $stmt->execute(["$year-$month"]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize reservations by date
    $reservations_by_date = [];
    foreach ($reservations as $reservation) {
        $reservations_by_date[$reservation['date']][] = $reservation;
    }

    // Calendar setup
    $first_day_of_month = strtotime("$year-$month-01");
    $days_in_month = date('t', $first_day_of_month);
    $start_day = date('w', $first_day_of_month); // 0 = Sunday, 1 = Monday, ...

    // Adjust so the calendar starts on Monday
    $start_day = ($start_day == 0) ? 6 : $start_day - 1; // If Sunday (0), set it to 6 (Saturday), otherwise shift by 1

    // Previous month's last day
    $previous_month_days = date('t', strtotime("$previous_year-$previous_month-01"));

    // Output calendar with title
    echo '<div class="calendar-container">';

    // Calendar Title
    $month_name = date('F', $first_day_of_month);
    echo "<div class='calendar-title'>Reservations for $month_name $year</div>";

    // Calendar Header (Days of the week)
    echo '<div class="calendar">';
    echo '<div class="day-header">Mon</div>';
    echo '<div class="day-header">Tue</div>';
    echo '<div class="day-header">Wed</div>';
    echo '<div class="day-header">Thu</div>';
    echo '<div class="day-header">Fri</div>';
    echo '<div class="day-header">Sat</div>';
    echo '<div class="day-header">Sun</div>';

    // Empty cells for previous month's days
    $previous_month_day = $previous_month_days - ($start_day - 1); // Start from last days of the previous month
    for ($i = 0; $i < $start_day; $i++) {
        echo "<div class='day empty grey'><span class='day-number'>$previous_month_day</span></div>";
        $previous_month_day++;
    }

    // Days of the current month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        $classes = 'day';
        $label = '';
        $clickable = false;

        if (isset($reservations_by_date[$date])) {
            foreach ($reservations_by_date[$date] as $reservation) {
                if ($reservation['status'] === 'approved') {
                    $classes .= ' booked';
                    $label = 'Booked';
                } elseif ($reservation['status'] === 'pending') {
                    $classes .= ' pending';
                    $label = 'Pending';
                    $clickable = true;
                }
            }
        } else {
            $clickable = true; // Available dates are clickable
        }

        echo "<div class='$classes'>";
        if ($clickable) {
            echo "<a href='reservation.php?date=$date' class='clickable'>";
        }
        echo "<span class='day-number'>$day</span>";
        if ($label) {
            echo "<div class='label'>$label</div>";
        }
        if ($clickable) {
            echo "</a>";
        }
        echo "</div>";
    }

    // Empty cells for next month's days
    $next_month_day = 1;
    for ($i = $start_day + $days_in_month; $i < 42; $i++) {
        echo "<div class='day empty grey'><span class='day-number'>$next_month_day</span></div>";
        $next_month_day++;
    }

    echo '</div>'; // Close calendar

    // Navigation buttons at the bottom
    echo '<div class="calendar-navigation">';

    // Disable "Previous" button if viewing before the current date
    $current_calendar_date = new DateTime("$year-$month-01");
    if ($current_calendar_date > $min_date) {
        echo "<a href='?month=$previous_month&year=$previous_year' class='nav-button'>&laquo; Previous</a>";
    } else {
        echo "<span class='nav-button disabled'>&laquo; Previous</span>";
    }

    echo "<a href='?month=$next_month&year=$next_year' class='nav-button'>Next &raquo;</a>";


    echo '</div>'; // Close calendar-navigation

    echo '</div>'; // Close calendar-container
}
?>

