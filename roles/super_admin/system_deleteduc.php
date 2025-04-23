<?php
include "system_func.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: system_management.php?error=Invalid deduction ID');
    exit();
}

$deduction_id = intval($_GET['id']);

if (deleteDeduction($deduction_id)) {
    header('Location: system_management.php?success=Deduction deleted successfully');
    exit();
} else {
    header('Location: system_management.php?error=Failed to delete deduction');
    exit();
}
?>