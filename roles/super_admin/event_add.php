<?php
require_once "event_func.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date']; // Includes both date and time
    $location = $_POST['location'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];

    if (createEvent($event_name, $event_date, $location, $description, $created_by)) {
        header("Location: event_management.php?success=Event created successfully");
        exit();
    } else {
        $message = "Failed to create event";
    }
}

include "../super_admin/header.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .event-form-card {
            border-radius: 12px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 24px rgba(42, 63, 84, 0.1);
        }

        .card-header-custom {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }

        .btn-insurance {
            background: var(--insurance-blue);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-insurance:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.15);
        }

        .btn-outline-insurance {
            border: 2px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--accent-sky);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .alert-info {
            background-color: rgba(26, 188, 156, 0.1);
            border-color: rgba(26, 188, 156, 0.3);
            color: var(--text-primary);
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card event-form-card">
                    <div class="card-header card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Create New Event</h3>
                            <a href="event_management.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Events
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-4">
                                <label class="form-label fw-medium" style="color: var(--insurance-blue);">
                                    <i class="fas fa-heading me-2"></i>Event Name
                                </label>
                                <input type="text" name="event_name" class="form-control" required>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium" style="color: var(--insurance-blue);">
                                        <i class="fas fa-calendar-day me-2"></i>Event Date & Time
                                    </label>
                                    <input type="datetime-local" name="event_date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium" style="color: var(--insurance-blue);">
                                        <i class="fas fa-map-marker-alt me-2"></i>Location
                                    </label>
                                    <input type="text" name="location" class="form-control">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium" style="color: var(--insurance-blue);">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-insurance">
                                    <i class="fas fa-save me-2"></i>Create Event
                                </button>
                                <a href="event_management.php" class="btn btn-outline-insurance">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>