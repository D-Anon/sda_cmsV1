<?php
// clock_in_out.php

// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Initialize variables
$employee_id = '';
$message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];

    // Validate employee ID
    if (!empty($employee_id)) {
        // Check if employee exists
        $query = "SELECT id FROM users WHERE employee_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Record check-in or check-out
            $check_in_query = "INSERT INTO time_logs (user_id, check_in) VALUES (?, NOW())";
            $check_out_query = "UPDATE time_logs SET check_out = NOW() WHERE user_id = ? AND check_out IS NULL";

            // Check if user is already checked in
            $check_status_query = "SELECT * FROM time_logs WHERE user_id = ? AND check_out IS NULL";
            $check_status_stmt = $conn->prepare($check_status_query);
            $check_status_stmt->bind_param("i", $employee_id);
            $check_status_stmt->execute();
            $check_status_result = $check_status_stmt->get_result();

            if ($check_status_result->num_rows == 0) {
                // User is not checked in, perform check-in
                $stmt = $conn->prepare($check_in_query);
                $stmt->bind_param("i", $employee_id);
                $stmt->execute();
                $message = "Checked in successfully!";
            } else {
                // User is checked in, perform check-out
                $stmt = $conn->prepare($check_out_query);
                $stmt->bind_param("i", $employee_id);
                $stmt->execute();
                $message = "Checked out successfully!";
            }
        } else {
            $message = "Employee ID not found.";
        }
    } else {
        $message = "Please enter your Employee ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Clock In/Out</title>
</head>
<body>
    <div class="container">
        <h1>Clock In/Out</h1>
        <form method="POST" action="">
            <label for="employee_id">Employee ID:</label>
            <input type="text" id="employee_id" name="employee_id" required>
            <button type="submit">Submit</button>
        </form>
        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
    <script src="js/scripts.js"></script>
</body>
</html>