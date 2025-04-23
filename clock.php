<?php
session_start();
include 'includes/db.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Capture action and employee_id
$action = isset($_POST['action']) ? $_POST['action'] : null;
$employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $employee_id) {
    // Check if employee exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo "<div class='alert alert-danger'>Error: Employee ID does not exist!</div>";
        exit();
    }


    // Check if there's an existing log for today
    $stmt = $conn->prepare("SELECT * FROM time_logs WHERE employee_id = ? AND DATE(check_in) = CURDATE()");
    $stmt->execute([$employee_id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        // Create a new record for today
        $stmt = $conn->prepare("INSERT INTO time_logs (employee_id, check_in) VALUES (?, NOW())");
        $stmt->execute([$employee_id]);
        $log_id = $conn->lastInsertId(); // Get the new log's ID
    } else {
        $log_id = $log['id'];
    }

    // Update time log fields based on the action
    switch ($action) {
        case 'am_time_in':
            $stmt = $conn->prepare("UPDATE time_logs SET am_time_in = NOW() WHERE id = ?");
            break;
        case 'am_time_out':
            $stmt = $conn->prepare("UPDATE time_logs SET am_time_out = NOW() WHERE id = ?");
            break;
        case 'pm_time_in':
            $stmt = $conn->prepare("UPDATE time_logs SET pm_time_in = NOW() WHERE id = ?");
            break;
        case 'pm_time_out':
            $stmt = $conn->prepare("UPDATE time_logs SET pm_time_out = NOW(), check_out = NOW() WHERE id = ?");
            break;
        case 'ot_time_in':
            $stmt = $conn->prepare("UPDATE time_logs SET ot_time_in = NOW() WHERE id = ?");
            break;
        case 'ot_time_out':
            $stmt = $conn->prepare("UPDATE time_logs SET ot_time_out = NOW() WHERE id = ?");
            break;
        case 'check_out':
            $stmt = $conn->prepare("UPDATE time_logs SET check_out = NOW() WHERE id = ?");
            break;
        default:
            echo "<div class='alert alert-danger'>Invalid action.</div>";
            exit();
    }

    $stmt->execute([$log_id]);

    // Calculate total working hours if check-out is recorded
    if ($action == 'check_out') {
        $stmt = $conn->prepare("
                UPDATE time_logs 
                SET total_hours = 
                    (TIMESTAMPDIFF(SECOND, am_time_in, am_time_out) +
                    TIMESTAMPDIFF(SECOND, pm_time_in, pm_time_out) +
                    TIMESTAMPDIFF(SECOND, ot_time_in, ot_time_out)) / 3600
                WHERE id = ?
            ");
        $stmt->execute([$log_id]);
    }

    echo "<div class='alert alert-success'>Time log updated successfully!</div>";
}

// Function to generate buttons
function showButton($label, $value, $class)
{
    echo "<button type='submit' name='action' value='$value' class='btn $class'>$label</button>";
}

// Fetch events from database
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <title>Employee Clock System</title>
    <style>
        /* ... (previous styles remain unchanged) ... */

        .alert {
            padding: 10px;
            margin-top: 10px;
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .clock {
            font-size: 150px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }

        /* Enhanced Calendar Styles */
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .calendar-nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .calendar-nav-btn:hover {
            background-color: #f0f0f0;
            transform: scale(1.1);
        }

        .calendar-title {
            margin: 0 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .day-header {
            font-weight: 600;
            color: #555;
            padding: 0.5rem;
            text-align: center;
        }

        .calendar-day {
            border: 1px solid #e9ecef;
            padding: 0.5rem;
            min-height: 120px;
            transition: all 0.2s;
            background-color: white;
        }

        .calendar-day:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .calendar-day.empty {
            background-color: #f8f9fa;
        }

        .calendar-day.today {
            background-color: #e6f7ff;
            border: 1px solid #b3e0ff;
        }

        .date-number {
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
        }

        .event-item {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            margin-bottom: 0.25rem;
            border-radius: 4px;
            background-color: #f8f9fa;
            border-left: 5px solid red;
            cursor: pointer;
            transition: all 0.2s;
        }

        .event-item:hover {
            background-color: #e9ecef;
            transform: translateX(2px);
        }

        .event-title {
            font-weight: 600;
            color: red;
        }

        .event-desc {
            color: #6c757d;
            font-size: 0.7rem;
        }

        /* Floating button enhancement */
        #floatingButton {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 1000;
        }

        #floatingButton:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .modal-dialog {
            max-width: 90%;
            /* Adjust width as needed */
            width: auto;
        }

        /* Modal enhancements */
        .modal-content {
            border-radius: 10px;
            margin-left: 5%;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .clock {
                font-size: 80px;
            }

            #floatingButton {
                width: 50px;
                height: 50px;
                font-size: 20px;
                right: 10px;
            }

            .calendar-day {
                min-height: 80px;
                padding: 0.25rem;
            }

            .date-number {
                font-size: 0.9rem;
            }

            .event-item {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
        }

        #floatingLoginButton {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #007bff;
            color: white;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            text-decoration: none;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        #floatingLoginButton:hover {
            width: 160px;
            background-color: #0056b3;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        #floatingLoginButton .login-text {
            opacity: 0;
            margin-left: 10px;
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }

        #floatingLoginButton:hover .login-text {
            opacity: 1;
        }
    </style>

    </style>
    <script>
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.style.display = "none", 500);
            });
        }, 5000);

        function updateClock() {
            const clockElement = document.getElementById('clock');
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            clockElement.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }

        setInterval(updateClock, 1000);
        window.onload = updateClock;
    </script>
</head>

<body class="d-flex justify-content-center align-items-center" style="height: 50vh;">
    <div class="text-center">
        <div id="clock" class="clock mb-4"></div>
        <div class="row">
            <form method="POST" action="clock.php">
                <div class="mb-5">
                    <input class="form-control form-control-lg text-center" type="text" id="employee_id" name="employee_id" placeholder="Employee ID" required>
                </div>
                <div>
                    <?php
                    $current_time = date('H:i');
                    if ($current_time >= '07:00' && $current_time <= '12:00') {
                        showButton('AM Time In', 'am_time_in', 'btn-success mx-2 px-2');
                        showButton('AM Time Out', 'am_time_out', 'btn-danger mx-2 px-2');
                    } elseif ($current_time >= '12:00' && $current_time <= '12:59') {
                        showButton('AM Time Out', 'am_time_out', 'btn-danger mx-2 px-2');
                        showButton('PM Time In', 'pm_time_in', 'btn-success mx-2 px-2');
                    } elseif ($current_time >= '13:00' && $current_time <= '17:00') {
                        showButton('PM Time In', 'pm_time_in', 'btn-success mx-2 px-2');
                        showButton('PM Time Out', 'pm_time_out', 'btn-danger mx-2 px-2');
                    } elseif ($current_time >= '17:00' && $current_time <= '17:59') {
                        showButton('PM Time Out', 'pm_time_out', 'btn-danger mx-2 px-2');
                        showButton('OT Time In', 'ot_time_in', 'btn-success mx-2 px-2');
                    } elseif ($current_time >= '18:00' && $current_time <= '20:00') {
                        showButton('OT Time In', 'ot_time_in', 'btn-success mx-2 px-2');
                        showButton('OT Time Out', 'ot_time_out', 'btn-danger mx-2 px-2');
                    } else {
                        echo "<div class='alert alert-warning'>No available actions at this time.</div>";
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Floating View Events Button -->
    <button id="floatingButton" type="button" class="btn btn-info px-4 mx-2" data-bs-toggle="modal" data-bs-target="#eventsModal">
        <i class="fas fa-calendar-alt"></i>
    </button>

    <!-- Floating Login Button -->
    <a href="login.php" id="floatingLoginButton" class="btn btn-primary">
        <i class="fas fa-sign-in-alt"></i>
        <span class="login-text">Login</span>
    </a>

    <!-- ... (previous code remains unchanged) -->

    <!-- Enhanced Events Modal -->
    <div class="modal fade" id="eventsModal" tabindex="-1" aria-labelledby="eventsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen"> <!-- Use modal-fullscreen class -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventsModalLabel">Company Events Calendar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow: hidden;"> <!-- Prevent scrolling -->
                    <div class="calendar-header">
                        <button type="button" class="btn btn-light calendar-nav-btn" onclick="prevMonth()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h3 class="calendar-title" id="modalMonthTitle"></h3>
                        <button type="button" class="btn btn-light calendar-nav-btn" onclick="nextMonth()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="calendar-container" style="height: 100%; overflow: auto;"> <!-- Scrollable calendar content -->
                        <?php
                        // Initialize calendar array
                        $calendar = [];
                        $today = date('Y-m-d');

                        try {
                            // Fetch events from database
                            $stmt = $conn->query("SELECT event_name AS title, event_date, description FROM events ORDER BY event_date ASC");

                            if ($stmt) {
                                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Group events by month and date
                                foreach ($events as $event) {
                                    $month = date('F Y', strtotime($event['event_date']));
                                    $day = date('j', strtotime($event['event_date']));
                                    $calendar[$month][$day][] = $event;
                                }
                            }
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Error loading events: " . $e->getMessage() . "</div>";
                        }

                        // Check if calendar has data
                        if (empty($calendar)) {
                            echo "<div class='alert alert-info text-center py-4'>No upcoming events scheduled.</div>";
                        } else {
                            $isFirstMonth = true;
                            foreach ($calendar as $month => $days) {
                                echo '<div class="month-container" data-month-name="' . htmlspecialchars($month) . '" style="' . ($isFirstMonth ? 'display: block;' : 'display: none;') . '">';

                                // Calendar grid
                                echo '<div class="calendar-grid">';

                                // Day headers
                                echo '<div class="row g-0">';
                                foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day) {
                                    echo "<div class='col day-header'>$day</div>";
                                }
                                echo '</div>';

                                // Calendar days
                                echo '<div class="row g-0">';
                                $firstDay = date('N', strtotime("first day of $month"));

                                // Empty days at start
                                for ($i = 1; $i < $firstDay; $i++) {
                                    echo "<div class='col calendar-day empty'></div>";
                                }

                                // Actual days
                                $daysInMonth = date('t', strtotime($month));
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $date = date('Y-m-d', strtotime("$month $day"));
                                    $isToday = ($date == $today) ? 'today' : '';
                                    $hasEvents = isset($days[$day]);

                                    echo "<div class='col calendar-day $isToday'>";
                                    echo "<div class='date-number'>$day</div>";

                                    if ($hasEvents) {
                                        echo '<div class="events">';
                                        foreach ($days[$day] as $event) {
                                            echo "<div class='event-item'>";
                                            echo "<div class='event-title'>{$event['title']}</div>";
                                            if (!empty($event['description'])) {
                                                echo "<div class='event-desc'>{$event['description']}</div>";
                                            }
                                            echo "</div>";
                                        }
                                        echo '</div>';
                                    }
                                    echo "</div>";

                                    // New row after each week
                                    if (($day + $firstDay - 1) % 7 == 0) {
                                        echo '</div><div class="row g-0">';
                                    }
                                }
                                echo '</div></div></div>';
                                $isFirstMonth = false;
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ... (remaining code remains unchanged) -->

    <script>
        // Enhanced Calendar Navigation with Animation
        const monthContainers = Array.from(document.querySelectorAll('.month-container'));
        let currentMonthIndex = 0;

        function updateMonthTitle() {
            const activeMonth = monthContainers[currentMonthIndex]?.getAttribute('data-month-name') || '';
            document.getElementById('modalMonthTitle').textContent = activeMonth;
        }

        function showCurrentMonth() {
            monthContainers.forEach((container, index) => {
                if (index === currentMonthIndex) {
                    container.style.display = 'block';
                    container.style.animation = 'fadeIn 0.3s ease-out';
                } else {
                    container.style.display = 'none';
                }
            });
            updateMonthTitle();
        }

        function prevMonth() {
            if (currentMonthIndex > 0) {
                currentMonthIndex--;
                showCurrentMonth();
            }
        }

        function nextMonth() {
            if (currentMonthIndex < monthContainers.length - 1) {
                currentMonthIndex++;
                showCurrentMonth();
            }
        }

        // Initialize when modal is shown
        document.getElementById('eventsModal').addEventListener('show.bs.modal', () => {
            currentMonthIndex = 0;
            showCurrentMonth();
        });

        // Add animation for smoother transitions
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0.5; transform: translateX(10px); }
                to { opacity: 1; transform: translateX(0); }
            }
        `;
        document.head.appendChild(style);

        // Add event item click handler
        document.addEventListener('click', function(e) {
            if (e.target.closest('.event-item')) {
                const eventTitle = e.target.closest('.event-item').querySelector('.event-title').textContent;
                alert(`Event Details:\n${eventTitle}`);
            }
        });
    </script>
</body>

</html>