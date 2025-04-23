<?php
require '../super_admin/db.php'; // Ensure database connection

/**
 * Log Time In based on the time of day
 * @param int $user_id
 * @return string
 */
function logTimeIn($user_id) {
    global $conn;

    try {
        $current_time = date('H:i:s'); 
        $column = getTimeColumn($current_time, true); 

        if (!$column) {
            return "Invalid time for Time In.";
        }

        // Verify employee exists in users table
        $sql = "SELECT id FROM users WHERE employee_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $employeeExists = $stmt->fetchColumn();

        if (!$employeeExists) {
            return "Error: Employee ID does not exist!";
        }

        // Check if there's an existing log for today
        $sql = "SELECT id, am_time_in, am_time_out, pm_time_in, pm_time_out, ot_time_in, ot_time_out 
                FROM time_logs 
                WHERE employee_id = :user_id AND DATE(check_in) = CURDATE()";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$log) {
            // No record exists for today, create a new one
            $sql = "INSERT INTO time_logs (employee_id, check_in, $column) VALUES (:user_id, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Record exists, update only if the column is empty
            if (empty($log[$column])) {
                $sql = "UPDATE time_logs SET $column = NOW() WHERE id = :log_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':log_id', $log['id'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                return "You have already timed in for this period!";
            }
        }

        return "Time In Successful!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Log Time Out based on the time of day
 * @param int $user_id
 * @return string
 */
function logTimeOut($user_id) {
    global $conn;

    try {
        $current_time = date('H:i:s');
        $column = getTimeColumn($current_time, false); // Determine the correct column for Time Out

        if (!$column) {
            return "Invalid time for Time Out.";
        }

        // Find the last recorded time-in for today
        $sql = "SELECT id, am_time_in, am_time_out, pm_time_in, pm_time_out, ot_time_in, ot_time_out 
                FROM time_logs 
                WHERE employee_id = :user_id 
                AND DATE(check_in) = CURDATE() 
                AND $column IS NULL 
                ORDER BY check_in DESC 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($log) {
            // Update the Time Out column
            $sql = "UPDATE time_logs SET $column = NOW(), check_out = NOW() WHERE id = :log_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':log_id', $log['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Calculate total hours worked for the day
            $totalHours = 0;
            $totalHours += calculateHours($log['am_time_in'], $log['am_time_out']);
            $totalHours += calculateHours($log['pm_time_in'], $log['pm_time_out']);
            $totalHours += calculateHours($log['ot_time_in'], $log['ot_time_out']);

            // Update the total_hours column
            $sql = "UPDATE time_logs SET total_hours = :total_hours WHERE id = :log_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':total_hours', $totalHours, PDO::PARAM_STR);
            $stmt->bindParam(':log_id', $log['id'], PDO::PARAM_INT);
            $stmt->execute();

            return "Time Out Successful! Total hours updated.";
        } else {
            return "No valid Time-In record found!";
        }
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Fetch all time logs for display
 * @return array
 */
function getTimeLogs() {
    global $conn;

    try {
        $sql = "SELECT 
                    u.employee_id AS emp_id, 
                    CONCAT(u.fname, ' ', u.lname) AS name,
                    DATE(tl.check_in) AS date,
                    tl.am_time_in,
                    tl.am_time_out,
                    tl.pm_time_in,
                    tl.pm_time_out,
                    tl.ot_time_in,
                    tl.ot_time_out,
                    IFNULL(tl.total_hours, 0) AS total_hours
                FROM time_logs tl
                INNER JOIN users u ON tl.employee_id = u.employee_id 
                ORDER BY tl.check_in DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching time logs: " . $e->getMessage();
        return [];
    }
}


function calculateHours($start, $end) {
    if (!$start || !$end) {
        return 0; // Return 0 if either start or end time is null
    }

    $startTime = strtotime($start);
    $endTime = strtotime($end);

    if ($endTime <= $startTime) {
        return 0; // Return 0 if end time is earlier than or equal to start time
    }

    $diffInSeconds = $endTime - $startTime;
    $hours = $diffInSeconds / 3600; // Convert seconds to hours

    return round($hours, 2); // Round to 2 decimal places
}

/**
 * Determine the appropriate column for Time In or Time Out based on the current time
 * @param string $current_time The current time in 'H:i:s' format
 * @param bool $isTimeIn Whether the action is Time In (true) or Time Out (false)
 * @return string|null The column name or null if the time is invalid
 */
function getTimeColumn($current_time, $isTimeIn) {
    $hour = (int)date('H', strtotime($current_time)); // Extract the hour from the current time

    if ($hour < 12) {
        // AM Period
        return $isTimeIn ? 'am_time_in' : 'am_time_out';
    } elseif ($hour >= 12 && $hour < 18) {
        // PM Period
        return $isTimeIn ? 'pm_time_in' : 'pm_time_out';
    } elseif ($hour >= 18) {
        // OT Period
        return $isTimeIn ? 'ot_time_in' : 'ot_time_out';
    }

    return null; // Invalid time
}
?>
