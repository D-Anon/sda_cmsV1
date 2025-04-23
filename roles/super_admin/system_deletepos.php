<?php
include "system_func.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: system_management.php?error=Invalid position ID');
    exit();
}

$position_id = intval($_GET['id']);

if (deletePosition($position_id)) {
    header('Location: system_management.php?success=Position deleted successfully');
    exit();
} else {
    header('Location: system_management.php?error=Failed to delete position');
    exit();
}
?>