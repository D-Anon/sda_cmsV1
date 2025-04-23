<?php
include 'includes/db.php';

// Get first and last day of selected month
$firstDayOfMonth = date('Y-m-01', strtotime($currentMonth));
$lastDayOfMonth = date('Y-m-t', strtotime($currentMonth));

$stmt = $conn->prepare("
    SELECT event_name as title, event_date, description 
    FROM events 
    WHERE event_date BETWEEN ? AND ?
    ORDER BY event_date ASC
");
$stmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate calendar for current month only
$monthName = date('F Y', strtotime($currentMonth));
$daysInMonth = date('t', strtotime($currentMonth));
$firstDay = date('N', strtotime($firstDayOfMonth));

// Rest of your calendar generation code remains similar but for single month...
?>
