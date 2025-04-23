<?php
// Start session
session_start();

// Include database connection
require_once "db.php";

// Function to mark attendance
function markAttendance($user_id, $status, $date) {
    global $conn;

    try {
        $sql = "INSERT INTO attendance (user_id, status, date) 
                VALUES (:user_id, :status, :date)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':status' => $status,
            ':date' => $date
        ]);
        return "Attendance marked successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get attendance record of a specific user
function getAttendance($user_id) {
    global $conn;

    try {
        $sql = "SELECT * FROM attendance WHERE user_id = :user_id ORDER BY date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Function to update an attendance record
function updateAttendance($attendance_id, $status) {
    global $conn;

    try {
        $sql = "UPDATE attendance SET status = :status WHERE id = :attendance_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':attendance_id' => $attendance_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to delete an attendance record
function deleteAttendance($attendance_id) {
    global $conn;

    try {
        $sql = "DELETE FROM attendance WHERE id = :attendance_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':attendance_id' => $attendance_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to list all attendance records
function listAttendance() {
    global $conn;

    try {
        $sql = "SELECT attendance.*, users.full_name AS employee_name FROM attendance 
                JOIN users ON attendance.user_id = users.id ORDER BY date DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
