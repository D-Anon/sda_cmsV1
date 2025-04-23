<?php
session_start();
include 'includes/db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];

    if (empty($employee_id)) {
        echo "Employee ID is required.";
        exit();
    }

    // Check if the employee exists in the users table
    $check_user_sql = "SELECT id FROM users WHERE id = :employee_id";
    $check_user_stmt = $conn->prepare($check_user_sql);
    $check_user_stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
    $check_user_stmt->execute();
    $user = $check_user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Invalid Employee ID. Please provide a valid ID.";
        exit();
    }

    // Get the latest time log entry for today
    $date = date("Y-m-d");
    $sql = "SELECT * FROM time_logs WHERE employee_id = :employee_id AND DATE(check_in) = :date";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
    $stmt->bindValue(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    $current_time = date("Y-m-d H:i:s");
    $current_hour = (int)date("H");

    if (!$log) {
        // If no log exists for today, determine the period and record the time
        if ($current_hour < 12) {
            // AM Period
            $insert = "INSERT INTO time_logs (employee_id, check_in, am_time_in) VALUES (:employee_id, NOW(), :current_time)";
        } else {
            // PM Period
            $insert = "INSERT INTO time_logs (employee_id, check_in, pm_time_in) VALUES (:employee_id, NOW(), :current_time)";
        }
        $stmt = $conn->prepare($insert);
        $stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmt->bindValue(':current_time', $current_time, PDO::PARAM_STR);
        $stmt->execute();
        echo "Time In recorded successfully.";
    } else {
        // Update the appropriate time slot based on the current time
        if ($current_hour < 12) {
            // AM Period
            if (is_null($log['am_time_out'])) {
                $update = "UPDATE time_logs SET am_time_out = :current_time WHERE id = :id";
            } else {
                echo "You have already completed your AM logs for today.";
                exit();
            }
        } else {
            // PM Period
            if (is_null($log['pm_time_in'])) {
                $update = "UPDATE time_logs SET pm_time_in = :current_time WHERE id = :id";
            } elseif (is_null($log['pm_time_out'])) {
                $update = "UPDATE time_logs SET pm_time_out = :current_time WHERE id = :id";
            } else {
                echo "You have already completed your PM logs for today.";
                exit();
            }
        }

        $stmt = $conn->prepare($update);
        $stmt->bindValue(':current_time', $current_time, PDO::PARAM_STR);
        $stmt->bindValue(':id', $log['id'], PDO::PARAM_INT);
        $stmt->execute();

        echo "Time log updated successfully.";
    }
}
?>