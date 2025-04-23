<?php
include '../super_admin/task_func.php'; // Include task manager logic

if (isset($_GET['id'])) {
    $taskId = intval($_GET['id']);

    // Optional: Validate if the task exists first
    if ($taskManager->deleteTask($taskId)) {
        // Redirect back to task list after deletion
        header("Location: task_management.php?message=deleted");
        exit();
    } else {
        // Deletion failed
        header("Location: task_management.php?error=delete_failed");
        exit();
    }
} else {
    // Invalid access
    header("Location: task_management.php?error=invalid_request");
    exit();
}
?>