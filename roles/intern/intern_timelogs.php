<?php
session_start();
include "../intern/header.php";
include "../intern/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Get logged-in user's employee_id from users table
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT employee_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !isset($user['employee_id'])) {
    die('<div class="alert alert-danger">Employee ID not found for this user</div>');
}

$employee_id = $user['employee_id'];
$current_date = date('Y-m-d');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['check_in'])) {
            // Check if already checked in today
            $stmt = $conn->prepare("SELECT id FROM time_logs WHERE employee_id = ? AND DATE(check_in) = ?");
            $stmt->execute([$employee_id, $current_date]);
            
            if ($stmt->fetch()) {
                $message = "You've already checked in today";
                $message_type = "warning";
            } else {
                $stmt = $conn->prepare("INSERT INTO time_logs (employee_id) VALUES (?)");
                $stmt->execute([$employee_id]);
                $message = "Successfully checked in";
                $message_type = "success";
            }
        } 
        elseif (isset($_POST['check_out'])) {
            $stmt = $conn->prepare("UPDATE time_logs SET check_out = CURRENT_TIMESTAMP() WHERE employee_id = ? AND DATE(check_in) = ? AND check_out IS NULL");
            $stmt->execute([$employee_id, $current_date]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Successfully checked out";
                $message_type = "success";
            } else {
                $message = "No active check-in found or already checked out";
                $message_type = "warning";
            }
        }
        elseif (isset($_POST['log_time'])) {
            $type = $_POST['type'];
            $time = $_POST['time'];
            $action = $_POST['action'];
            
            $column = $type . '_time_' . $action;
            $stmt = $conn->prepare("UPDATE time_logs SET $column = ? WHERE employee_id = ? AND DATE(check_in) = ?");
            $stmt->execute([$time, $employee_id, $current_date]);
            
            $message = "Time logged successfully";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get today's time log
$today_log = [];
$stmt = $conn->prepare("SELECT * FROM time_logs WHERE employee_id = ? AND DATE(check_in) = ?");
$stmt->execute([$employee_id, $current_date]);
$today_log = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all time logs for this employee
$time_logs = [];
$stmt = $conn->prepare("SELECT * FROM time_logs WHERE employee_id = ? ORDER BY check_in DESC");
$stmt->execute([$employee_id]);
$time_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate total hours virtually
function calculateTotalHours($log) {
    $total = 0;
    
    // Calculate check-in to check-out hours
    if ($log['check_in'] && $log['check_out']) {
        $check_in = new DateTime($log['check_in']);
        $check_out = new DateTime($log['check_out']);
        $total += ($check_out->getTimestamp() - $check_in->getTimestamp()) / 3600;
    }
    
    // Calculate AM hours
    if ($log['am_time_in'] && $log['am_time_out']) {
        $am_in = new DateTime($log['am_time_in']);
        $am_out = new DateTime($log['am_time_out']);
        $total += ($am_out->getTimestamp() - $am_in->getTimestamp()) / 3600;
    }
    
    // Calculate PM hours
    if ($log['pm_time_in'] && $log['pm_time_out']) {
        $pm_in = new DateTime($log['pm_time_in']);
        $pm_out = new DateTime($log['pm_time_out']);
        $total += ($pm_out->getTimestamp() - $pm_in->getTimestamp()) / 3600;
    }
    
    // Calculate OT hours
    if ($log['ot_time_in'] && $log['ot_time_out']) {
        $ot_in = new DateTime($log['ot_time_in']);
        $ot_out = new DateTime($log['ot_time_out']);
        $total += ($ot_out->getTimestamp() - $ot_in->getTimestamp()) / 3600;
    }
    
    return round($total, 2);
}
?>

<div class="container mt-4">
    <?php if (isset($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Today's Time Log (Employee ID: <?= $employee_id ?>)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-grid gap-2 mb-3">
                        <?php if (!$today_log || $today_log['check_out']): ?>
                            <form method="post">
                                <button type="submit" name="check_in" class="btn btn-success btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Check In
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post">
                                <button type="submit" name="check_out" class="btn btn-danger btn-lg">
                                    <i class="fas fa-sign-out-alt me-2"></i>Check Out
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Current Status</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Check In
                                    <span class="badge bg-<?= $today_log && $today_log['check_in'] ? 'success' : 'secondary' ?>">
                                        <?= $today_log && $today_log['check_in'] ? date('h:i A', strtotime($today_log['check_in'])) : 'Not logged' ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Check Out
                                    <span class="badge bg-<?= $today_log && $today_log['check_out'] ? 'success' : 'secondary' ?>">
                                        <?= $today_log && $today_log['check_out'] ? date('h:i A', strtotime($today_log['check_out'])) : 'Not logged' ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Hours
                                    <span class="badge bg-primary">
                                        <?= $today_log ? calculateTotalHours($today_log) : '0.00' ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Additional Time Logs</h6>
                            <form method="post">
                                <input type="hidden" name="log_time" value="1">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Time Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="am">AM</option>
                                            <option value="pm">PM</option>
                                            <option value="ot">OT (Overtime)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Action</label>
                                        <select name="action" class="form-select" required>
                                            <option value="in">Time In</option>
                                            <option value="out">Time Out</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Time</label>
                                    <input type="datetime-local" class="form-control" name="time" required 
                                           value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Log Time
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Time Log History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>AM In</th>
                            <th>AM Out</th>
                            <th>PM In</th>
                            <th>PM Out</th>
                            <th>OT In</th>
                            <th>OT Out</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($time_logs)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">No time logs found for employee <?= $employee_id ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($time_logs as $log): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($log['check_in'])) ?></td>
                                    <td><?= $log['check_in'] ? date('h:i A', strtotime($log['check_in'])) : '-' ?></td>
                                    <td><?= $log['check_out'] ? date('h:i A', strtotime($log['check_out'])) : '-' ?></td>
                                    <td><?= $log['am_time_in'] ? date('h:i A', strtotime($log['am_time_in'])) : '-' ?></td>
                                    <td><?= $log['am_time_out'] ? date('h:i A', strtotime($log['am_time_out'])) : '-' ?></td>
                                    <td><?= $log['pm_time_in'] ? date('h:i A', strtotime($log['pm_time_in'])) : '-' ?></td>
                                    <td><?= $log['pm_time_out'] ? date('h:i A', strtotime($log['pm_time_out'])) : '-' ?></td>
                                    <td><?= $log['ot_time_in'] ? date('h:i A', strtotime($log['ot_time_in'])) : '-' ?></td>
                                    <td><?= $log['ot_time_out'] ? date('h:i A', strtotime($log['ot_time_out'])) : '-' ?></td>
                                    <td><?= calculateTotalHours($log) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

