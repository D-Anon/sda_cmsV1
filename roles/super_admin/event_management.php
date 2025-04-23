<?php
include "../super_admin/event_func.php";
include "../super_admin/header.php";

// Fetch all events and sort by date
$events = listEvents();
usort($events, function ($a, $b) {
    return strtotime($a['event_date']) - strtotime($b['event_date']);
});

// Get available years from events
$years = array_unique(array_map(function ($event) {
    return date('Y', strtotime($event['event_date']));
}, $events));

// Handle month/year selection
$selectedYear = $_GET['year'] ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');
$searchQuery = $_GET['search'] ?? '';

// Filter events for selected month/year
$filteredEvents = array_filter($events, function ($event) use ($selectedYear, $selectedMonth, $searchQuery) {
    $dateMatch = date('Y-m', strtotime($event['event_date'])) === "$selectedYear-$selectedMonth";
    $searchMatch = empty($searchQuery) ||
        stripos($event['event_name'], $searchQuery) !== false ||
        stripos($event['location'], $searchQuery) !== false ||
        stripos($event['description'], $searchQuery) !== false;
    return $dateMatch && $searchMatch;
});

// Generate calendar data
$date = new DateTime("$selectedYear-$selectedMonth-01");
$monthName = $date->format('F Y');
$firstDay = (int)$date->format('N'); // 1 (Monday) to 7 (Sunday)
$daysInMonth = (int)$date->format('t');
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        .event-management-container {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .calendar-header {
            background: var(--insurance-blue);
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-day {
            background: white;
            min-height: 90px;
            padding: 5px;
            position: relative;
            transition: all 0.2s ease;
            border: 1px solid rgba(42, 63, 84, 0.05);
            cursor: pointer;
        }

        .calendar-day:hover {
            background: #f8f9fa;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(42, 63, 84, 0.1);
        }

        .today {
            background: rgba(26, 188, 156, 0.1);
            border: 2px solid var(--professional-teal);
        }

        .calendar-event-indicator {
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
            display: inline-block;
            margin: 1px;
        }

        .month-filter-card {
            background: white;
            border-radius: 10px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.05);
        }

        .btn-insurance {
            background: var(--insurance-blue);
            color: white;
            border: none;
            transition: all 0.2s;
        }

        .btn-insurance:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-1px);
        }

        .btn-outline-insurance {
            border: 1px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
        }

        .badge-insurance {
            background: var(--professional-teal);
            color: white;
        }

        .event-card {
            border-radius: 8px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            transition: all 0.2s;
        }

        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 63, 84, 0.1);
        }

        .empty-state {
            color: rgba(42, 63, 84, 0.4);
        }

        /* New layout styles */
        .calendar-event-list-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .calendar-container {
            flex: 2;
        }

        .event-list-container {
            flex: 1;
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .event-list-header {
            background: var(--insurance-blue);
            color: white;
            padding: 12px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        .event-list-scroll {
            background: white;
            border-radius: 0 0 8px 8px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.05);
        }

        .event-list-item {
            padding: 12px;
            border-bottom: 1px solid rgba(42, 63, 84, 0.05);
            transition: all 0.2s;
        }

        .event-list-item:hover {
            background: rgba(42, 63, 84, 0.03);
        }

        .event-date-badge {
            background: var(--professional-teal);
            color: white;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        /* Modal styles */
        .day-events-modal .modal-header {
            background: var(--insurance-blue);
            color: white;
        }

        .day-events-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .day-event-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Search box */
        .search-box {
            position: relative;
            margin-bottom: 15px;
        }

        .search-box input {
            padding-left: 35px;
            border-radius: 20px;
            border: 1px solid rgba(42, 63, 84, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 10px;
            color: var(--insurance-blue);
        }

        @media (max-width: 992px) {
            .calendar-event-list-container {
                flex-direction: column;
            }

            .event-list-container {
                max-height: none;
            }
        }
    </style>
</head>

<body class="event-management-container">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 font-weight-bold" style="color: var(--insurance-blue);">
                <i class="fas fa-calendar-alt me-2"></i>Event Management
            </h1>
            <a href="event_add.php" class="btn btn-insurance">
                <i class="fas fa-plus-circle me-2"></i>New Event
            </a>
        </div>

        <!-- Calendar Filter -->
        <div class="card month-filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="form-label">Filter by:</label>
                    </div>
                    <div class="col-auto">
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= sprintf('%02d', $m) ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Combined Calendar and Event List -->
        <div class="calendar-event-list-container">
            <!-- Calendar View -->
            <div class="calendar-container">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--insurance-blue); color: white;">
                        <h3 class="h4 mb-0"><?= $monthName ?></h3>
                        <button type="button" class="btn btn-transparent text-dark" data-bs-toggle="popover" title="Tip" data-bs-placement="left" data-bs-content="Red dots show events. More dots mean more events on that date.">
                            ℹ️
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="calendar-grid">
                            <div class="calendar-header">Mon</div>
                            <div class="calendar-header">Tue</div>
                            <div class="calendar-header">Wed</div>
                            <div class="calendar-header">Thu</div>
                            <div class="calendar-header">Fri</div>
                            <div class="calendar-header">Sat</div>
                            <div class="calendar-header">Sun</div>

                            <?php for ($i = 1; $i < $firstDay; $i++): ?>
                                <div class="calendar-day" style="background: #f8f9fa;"></div>
                            <?php endfor; ?>

                            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                                $currentDate = date('Y-m-d', strtotime("$selectedYear-$selectedMonth-$day"));
                                $isToday = ($currentDate == $today);
                                $dayEvents = array_filter($filteredEvents, function ($event) use ($currentDate) {
                                    return date('Y-m-d', strtotime($event['event_date'])) === $currentDate;
                                });
                            ?>
                                <div class="calendar-day <?= $isToday ? 'today' : '' ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#dayEventsModal"
                                    data-date="<?= $currentDate ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="day-number"><?= $day ?></span>
                                        <?php if ($isToday): ?>
                                            <span class="badge badge-insurance badge-sm">Today</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-1">
                                        <?php if (!empty($dayEvents)): ?>
                                            <?php for ($i = 0; $i < min(3, count($dayEvents)); $i++): ?>
                                                <span class="calendar-event-indicator"></span>
                                            <?php endfor; ?>
                                            <?php if (count($dayEvents) > 3): ?>
                                                <small class="text-muted">+<?= count($dayEvents) - 3 ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>

                            <?php
                            $totalCells = $firstDay - 1 + $daysInMonth;
                            $remainingCells = (7 - ($totalCells % 7)) % 7;
                            for ($i = 0; $i < $remainingCells; $i++): ?>
                                <div class="calendar-day" style="background: #f8f9fa;"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event List -->
            <div class="event-list-container">
                <div class="card shadow-sm">
                    <h3 class="event-list-header h4 mb-0">
                        Events for <?= $monthName ?> (<?= count($filteredEvents) ?>)
                    </h3>

                    <div class="event-list-scroll">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="eventSearch" placeholder="Search events..."
                                value="<?= htmlspecialchars($searchQuery) ?>">
                        </div>

                        <?php if (count($filteredEvents) > 0): ?>
                            <?php foreach ($filteredEvents as $event):
                                $eventDate = strtotime($event['event_date']);
                                $isPast = $eventDate < time();
                            ?>
                                <div class="event-list-item">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="event-date-badge">
                                            <?= date("j M", $eventDate) ?>
                                        </span>
                                        <h4 class="h5 mb-0" style="color: var(--insurance-blue);">
                                            <?= htmlspecialchars($event['event_name']) ?>
                                        </h4>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clock me-2" style="color: var(--insurance-blue); font-size: 0.8rem;"></i>
                                        <small class="text-muted">
                                            <?= date("g:i A", $eventDate) ?>
                                        </small>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-map-marker-alt me-2" style="color: var(--insurance-blue); font-size: 0.8rem;"></i>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($event['location']) ?>
                                        </small>
                                    </div>

                                    <p class="card-text text-muted small mb-2">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </p>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="event_edit.php?id=<?= $event['id'] ?>" class="btn btn-outline-insurance btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="event_delete.php?id=<?= $event['id'] ?>" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 empty-state">
                                <i class="fas fa-calendar-times fa-2x mb-3"></i>
                                <h4 class="h5">No events found</h4>
                                <p class="text-muted small">Try a different search or add a new event</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Events Modal -->
    <div class="modal fade" id="dayEventsModal" tabindex="-1" aria-labelledby="dayEventsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dayEventsModalLabel">Events for <span id="modalDate"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="day-events-list" id="dayEventsList">
                        <!-- Events will be loaded here via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "../super_admin/footer.php"; ?>

    <script>
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Day click modal handler
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                const formattedDate = new Date(date).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                document.getElementById('modalDate').textContent = formattedDate;

                // Get all events from PHP and parse the JSON
                const allEvents = <?= json_encode($events) ?>;

                // Filter events for this specific day
                const dayEvents = allEvents.filter(event => {
                    const eventDate = event.event_date.split(' ')[0]; // Get just the date part
                    return eventDate === date;
                });

                const eventsList = document.getElementById('dayEventsList');
                eventsList.innerHTML = '';

                if (dayEvents.length > 0) {
                    dayEvents.forEach(event => {
                        // Extract time from event_date (assuming format is "Y-m-d H:i:s")
                        const eventTimeParts = event.event_date.split(' ')[1].split(':');
                        const eventTime = `${eventTimeParts[0]}:${eventTimeParts[1]}`;

                        const eventItem = document.createElement('div');
                        eventItem.className = 'day-event-item';
                        eventItem.innerHTML = `
                        <h6 style="color: var(--insurance-blue);">${event.event_name}</h6>
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-clock me-2" style="color: var(--insurance-blue); font-size: 0.8rem;"></i>
                            <small class="text-muted">${eventTime}</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--insurance-blue); font-size: 0.8rem;"></i>
                            <small class="text-muted">${event.location}</small>
                        </div>
                        <p class="text-muted small">${event.description}</p>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="event_edit.php?id=${event.id}" class="btn btn-outline-insurance btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="event_delete.php?id=${event.id}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    `;
                        eventsList.appendChild(eventItem);
                    });
                } else {
                    eventsList.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-times fa-2x mb-2" style="color: #ccc;"></i>
                        <p class="text-muted">No events scheduled for this day</p>
                    </div>
                `;
                }
            });
        });

        // Search functionality
        document.getElementById('eventSearch').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchQuery = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('search', searchQuery);
                window.location.href = url.toString();
            }
        });

        // Initialize all popovers
        document.addEventListener("DOMContentLoaded", function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
</body>

</html>