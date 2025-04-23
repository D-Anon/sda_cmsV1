<?php
include "event_func.php";

if (isset($_GET['id'])) {
    $eventId = (int)$_GET['id'];
    
    if (deleteEvent($eventId)) {
        header("Location: event_management.php?success=Event deleted successfully");
    } else {
        header("Location: event_management.php?error=Error deleting event");
    }
} else {
    header("Location: event_management.php");
}
exit();
?>