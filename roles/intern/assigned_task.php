<?php
session_start();
include "../intern/header.php";
include "../intern/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch intern's tasks from database - UPDATED QUERY
try {
    $stmt = $conn->prepare("
        SELECT t.*, u.fname as creator_fname, u.lname as creator_lname 
        FROM tasks t
        JOIN task_members tm ON t.id = tm.task_id
        JOIN users u ON tm.user_id = u.id  /* Changed from t.created_by to tm.user_id */
        WHERE tm.user_id = :user_id
        ORDER BY t.deadline ASC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching tasks: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tasks me-2"></i>My Assigned Tasks</h2>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Task Name</th>
                            <th>Description</th>
                            <th>Deadline</th>
                            <th>Assigned By</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No tasks assigned to you yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $index => $task): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($task['description'], 0, 50)); ?><?php echo strlen($task['description']) > 50 ? '...' : ''; ?></td>
                                    <td>
                                        <?php if ($task['deadline']): ?>
                                            <?php 
                                                $deadline = new DateTime($task['deadline']);
                                                $today = new DateTime();
                                                $isOverdue = $deadline < $today;
                                            ?>
                                            <span class="<?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                                <?php echo $deadline->format('M j, Y'); ?>
                                                <?php if ($isOverdue): ?>
                                                    <i class="fas fa-exclamation-circle ms-1"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['creator_fname'] . ' ' . $task['creator_lname']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary task-detail" 
                                                data-bs-toggle="modal" data-bs-target="#taskDetailModal"
                                                data-task-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Task Detail Modal -->
<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taskDetailModalLabel">Task Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="taskDetailContent">
                <!-- Content loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load task details when modal is shown
    $('#taskDetailModal').on('show.bs.modal', function(e) {
        var taskId = $(e.relatedTarget).data('task-id');
        $('#taskDetailContent').load('../intern/task_detail.php?task_id=' + taskId);
    });
});
</script>