<?php
session_start();
include "../intern/header.php";
include "../intern/db.php";

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$current_week = date('W');
$current_month = date('n');
$current_year = date('Y');

// Get time tracking statistics
try {
    // Weekly hours
    $weekly_hours = $conn->prepare("
        SELECT SUM(TIMESTAMPDIFF(HOUR, check_in, check_out)) AS hours
        FROM time_logs
        WHERE employee_id = ?
        AND YEAR(check_in) = ?
        AND WEEK(check_in) = ?
    ");
    $weekly_hours->execute([$user_id, $current_year, $current_week]);
    $weekly_hours = $weekly_hours->fetchColumn();

    // Monthly hours
    $monthly_hours = $conn->prepare("
        SELECT SUM(TIMESTAMPDIFF(HOUR, check_in, check_out)) AS hours
        FROM time_logs
        WHERE employee_id = ?
        AND YEAR(check_in) = ?
        AND MONTH(check_in) = ?
    ");
    $monthly_hours->execute([$user_id, $current_year, $current_month]);
    $monthly_hours = $monthly_hours->fetchColumn();

    // Task statistics (modified to work without status column)
    $task_stats = [
        'total' => 0,
        'completed' => 0,
        'in_progress' => 0,
        'not_started' => 0
    ];
    
    // Get total tasks assigned
    $total_tasks = $conn->prepare("
        SELECT COUNT(*) 
        FROM task_members 
        WHERE user_id = ?
    ");
    $total_tasks->execute([$user_id]);
    $task_stats['total'] = $total_tasks->fetchColumn();

    // If you have a way to determine task status, add those queries here
    // For example, if you track completion in another table:
    // $completed_tasks = $conn->prepare("SELECT COUNT(*) FROM task_completion WHERE user_id = ?");
    // $completed_tasks->execute([$user_id]);
    // $task_stats['completed'] = $completed_tasks->fetchColumn();

} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>");
}
?>

<div class="container py-5">
    <h2 class="mb-4 text-primary"><i class="fas fa-tachometer-alt me-2"></i>Employee Dashboard</h2>

    <div class="row">
        <!-- Time Tracking Cards -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Time Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Weekly Hours</h6>
                                    <h2 class="text-primary"><?= $weekly_hours ?: '0' ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Monthly Hours</h6>
                                    <h2 class="text-info"><?= $monthly_hours ?: '0' ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <canvas id="hoursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Statistics Cards -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border-primary shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Total Assigned Tasks</h6>
                                    <h2 class="text-primary"><?= $task_stats['total'] ?: '0' ?></h2>
                                </div>
                            </div>
                        </div>
                        <!-- Add more task status cards if you have the data -->
                        <!--
                        <div class="col-md-4">
                            <div class="card border-warning shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Ongoing</h6>
                                    <h2 class="text-warning"><?= $task_stats['in_progress'] ?: '0' ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">Completed</h6>
                                    <h2 class="text-success"><?= $task_stats['completed'] ?: '0' ?></h2>
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Hours Chart
new Chart(document.getElementById('hoursChart'), {
    type: 'bar',
    data: {
        labels: ['Weekly Hours', 'Monthly Hours'],
        datasets: [{
            label: 'Working Hours',
            data: [<?= $weekly_hours ?: 0 ?>, <?= $monthly_hours ?: 0 ?>],
            backgroundColor: ['#4e73df', '#36b9cc'], // Primary and Info colors
            borderColor: ['#2e59d9', '#2c9faf'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f8f9fa' },
                ticks: { color: '#6c757d' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#6c757d' }
            }
        }
    }
});

// Tasks Chart - Uncomment when you have status data
/*
new Chart(document.getElementById('tasksChart'), {
    type: 'doughnut',
    data: {
        labels: ['Not Started', 'In Progress', 'Completed'],
        datasets: [{
            data: [
                <?= $task_stats['not_started'] ?: 0 ?>,
                <?= $task_stats['in_progress'] ?: 0 ?>,
                <?= $task_stats['completed'] ?: 0 ?>
            ],
            backgroundColor: ['#e74a3b', '#f6c23e', '#1cc88a']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
*/
</script>

<style>
.card {
    border-radius: 10px;
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
canvas {
    margin-top: 1.5rem;
    max-height: 300px;
    border-radius: 8px;
}
.text-info {
    color: #36b9cc !important;
}
.border-primary {
    border-left: 4px solid #4e73df !important;
}
.border-info {
    border-left: 4px solid #36b9cc !important;
}
</style>