<?php
ob_start(); // Start output buffering
require_once "event_func.php";
include "../super_admin/header.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

if (isset($_GET['id'])) {
    $event = getEvent($_GET['id']);
    if (!$event) {
        die("Event not found.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $description = $_POST['description'];

    $updateSuccess = updateEvent($event_id, $event_name, $event_date, $location, $description);

    if ($updateSuccess) {
        header("Location: event_management.php?success=updated");
        exit();
    } else {
        $error = "Error updating event.";
    }
}
ob_end_flush(); // Send output and turn off buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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

        .card {
            border-radius: 12px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 24px rgba(42, 63, 84, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }

        .btn-primary {
            background: var(--insurance-blue);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.15);
        }

        .btn-outline-secondary {
            border: 2px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-outline-secondary:hover {
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
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-edit me-2"></i>Edit Event</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">

                            <div class="mb-4">
                                <label class="form-label" style="color: var(--insurance-blue);">Event Name</label>
                                <input type="text" name="event_name" class="form-control" value="<?= htmlspecialchars($event['event_name']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" style="color: var(--insurance-blue);">Event Date</label>
                                <input type="datetime-local" name="event_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" style="color: var(--insurance-blue);">Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($event['location']) ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label" style="color: var(--insurance-blue);">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($event['description']) ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Event
                                </button>
                                <a href="event_management.php" class="btn btn-outline-secondary">
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
