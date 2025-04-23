<?php
session_start();
include "../intern/db.php";

if (!isset($_SESSION['user_id'])) {
    die('<div class="alert alert-danger">Not authenticated</div>');
}

$task_id = $_GET['task_id'] ?? 0;
$user_id = $_SESSION['user_id'];

try {
    // Verify the user is assigned to this task
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM task_members 
        WHERE task_id = ? AND user_id = ?
    ");
    $stmt->execute([$task_id, $user_id]);
    
    if ($stmt->fetchColumn() == 0) {
        die('<div class="alert alert-danger">You are not authorized to view this task</div>');
    }

    // Get basic task details
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        die('<div class="alert alert-danger">Task not found</div>');
    }

    // Get task creator (assuming creator is the leader in task_members)
    $stmt = $conn->prepare("
        SELECT u.fname, u.lname 
        FROM task_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.task_id = ? AND tm.is_leader = 1
        LIMIT 1
    ");
    $stmt->execute([$task_id]);
    $creator = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all members assigned to this task
    $stmt = $conn->prepare("
        SELECT u.id, u.fname, u.lname, u.profile_picture, tm.is_leader
        FROM task_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.task_id = ?
    ");
    $stmt->execute([$task_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display task details
    echo '<div class="row mb-4">';
    echo '<div class="col-md-8">';
    echo '<h3 class="mb-2">' . htmlspecialchars($task['task_name']) . '</h3>';
    
    if (!empty($creator)) {
        echo '<p class="text-muted mb-1">';
        echo '<i class="fas fa-user-shield me-2"></i> Task Leader: ' . 
             htmlspecialchars($creator['fname'] . ' ' . $creator['lname']);
        echo '</p>';
    }
    echo '</div>';
    
    echo '<div class="col-md-4 text-end">';
    if ($task['deadline']) {
        $deadline = new DateTime($task['deadline']);
        $today = new DateTime();
        $isOverdue = $deadline < $today;
        
        echo '<div class="' . ($isOverdue ? 'alert alert-danger py-1 px-2 d-inline-block' : 'alert alert-light py-1 px-2 d-inline-block') . '">';
        echo '<i class="fas fa-calendar-day me-2"></i>';
        echo '<strong>Deadline:</strong> ' . $deadline->format('F j, Y');
        if ($isOverdue) {
            echo ' <span class="badge bg-danger ms-2">OVERDUE</span>';
        }
        echo '</div>';
    }
    echo '</div>';
    echo '</div>'; // End row

    echo '<div class="card mb-4">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title"><i class="fas fa-align-left me-2"></i>Description</h5>';
    echo '<div class="card-text">' . nl2br(htmlspecialchars($task['description'])) . '</div>';
    echo '</div>';
    echo '</div>';

    // Display assigned members
    if (!empty($members)) {
        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title"><i class="fas fa-users me-2"></i>Team Members</h5>';
        echo '<div class="d-flex flex-wrap gap-3">';
        
        foreach ($members as $member) {
            echo '<div class="d-flex align-items-center">';
            echo '<div class="me-2">';
            echo '<img src="' . ($member['profile_picture'] ? htmlspecialchars($member['profile_picture']) : '../../assets/img/default-profile.png') . '" 
                 class="rounded-circle" width="40" height="40" alt="Profile">';
            echo '</div>';
            echo '<div>';
            echo '<div class="fw-bold">' . htmlspecialchars($member['fname'] . ' ' . $member['lname']) . '</div>';
            if ($member['is_leader']) {
                echo '<span class="badge bg-primary">Leader</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>'; // End flex
        echo '</div>'; // End card-body
        echo '</div>'; // End card
    }

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading task details: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>