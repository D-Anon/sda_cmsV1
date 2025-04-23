<?php
include "../super_admin/user_func.php";

// Check if the user ID is provided
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Confirm deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
        // Call the deleteUser function
        if (deleteUser($user_id)) {
            header('Location: user_management.php?success=User deleted successfully');
            exit();
        } else {
            header('Location: user_management.php?error=Failed to delete user');
            exit();
        }
    } else {
        // Show confirmation prompt
        echo "<script>
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = 'user_delete.php?confirm=true&id=$user_id';
            } else {
                window.location.href = 'user_management.php';
            }
        </script>";
    }
} else {
    // Redirect back if no user ID is provided
    header('Location: user_management.php?error=No user ID provided');
    exit();
}
?>