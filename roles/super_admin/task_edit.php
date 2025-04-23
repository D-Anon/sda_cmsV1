<?php
require_once '../super_admin/task_func.php';

if (!isset($_GET['id'])) {
    header('Location: tasks.php');
    exit;
}

$taskId = $_GET['id'];
$task = $taskManager->getTaskById($taskId);
$users = $taskManager->getAllUsers();

if (!$task) {
    header('Location: tasks.php');
    exit;
}

include "../super_admin/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Policy Task</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Add your custom styles here */
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(42, 63, 84, 0.1);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            color: var(--insurance-blue);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--accent-sky);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .btn-outline-insurance {
            border: 2px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
        }

        .selected-members {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .member-badge {
            background: var(--professional-teal);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
        }

        .member-badge i {
            margin-right: 0.35rem;
            font-size: 0.75rem;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            border-bottom: none;
            padding: 1.25rem;
        }

        .modal-title {
            font-weight: 500;
        }

        .member-card {
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .member-card:hover {
            border-color: var(--accent-sky);
            background: #f8f9fa;
        }

        .member-card.selected {
            border-color: var(--professional-teal);
            background: #e8f4f1;
        }

        .member-checkbox {
            margin-right: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="form-container container">
        <h1><i class="fas fa-edit me-2"></i>Edit Policy Task</h1>
        
        <form id="taskForm" method="POST" action="task_func.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="task_id" value="<?= $taskId ?>">
            <input type="hidden" name="member_ids" id="member_ids" value="<?= htmlspecialchars($task['member_ids']) ?>">
            
            <div class="form-group">
                <label for="task_name">Task Name</label>
                <input type="text" class="form-control" id="task_name" name="task_name" 
                       value="<?= htmlspecialchars($task['task_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" class="form-control" id="deadline" name="deadline" 
                               value="<?= $task['deadline'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="leader_id">Task Leader</label>
                        <select class="form-control select2-search" id="leader_id" name="leader_id" required>
                            <option value="">Select Leader</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= ($task['leader_id'] == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['position']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Team Members</label>
                <button type="button" class="btn btn-outline-insurance w-100" 
                        data-bs-toggle="modal" data-bs-target="#membersModal">
                    <i class="fas fa-users me-2"></i>Select Members
                </button>
                <div class="selected-members" id="selectedMembers">
                    <?php 
                    $selectedMembers = explode(',', $task['member_ids']);
                    foreach ($users as $user) {
                        if (in_array($user['id'], $selectedMembers)) {
                            echo '<span class="member-badge"><i class="fas fa-user"></i>'.htmlspecialchars($user['full_name']).'</span>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-insurance" 
                        onclick="location.href='../super_admin/task_management.php'">
                    <i class="fas fa-arrow-left me-2"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Task
                </button>
            </div>
        </form>
    </div>

    <!-- Members Selection Modal -->
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-users me-2"></i>Select Team Members</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="memberSearch" class="form-control" placeholder="Search members by name or position...">
                    <div class="modal-body-content">
                        <div class="row" id="membersContainer">
                            <?php foreach ($users as $user): 
                                $isSelected = in_array($user['id'], explode(',', $task['member_ids']));
                            ?>
                            <div class="col-md-6 mb-3 member-item">
                                <div class="member-card <?= $isSelected ? 'selected' : '' ?>" 
                                     onclick="document.getElementById('member_<?= $user['id'] ?>').click()">
                                    <div class="form-check">
                                        <input class="form-check-input member-checkbox" type="checkbox" 
                                               id="member_<?= $user['id'] ?>" value="<?= $user['id'] ?>"
                                               <?= $isSelected ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="member_<?= $user['id'] ?>">
                                            <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($user['position']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>Confirm Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('.select2-search').select2();

        // Update selected members
        function updateSelectedMembers() {
            const selected = Array.from(document.querySelectorAll('.member-checkbox:checked'))
                .map(cb => cb.value);
            document.getElementById('member_ids').value = selected.join(',');
        }

        // Handle member selection
        document.querySelectorAll('.member-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                this.closest('.member-card').classList.toggle('selected', this.checked);
                updateSelectedMembers();
            });
        });

        // Search functionality in the modal
        document.getElementById('memberSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.member-item').forEach(item => {
                const name = item.querySelector('strong').textContent.toLowerCase();
                const position = item.querySelector('small').textContent.toLowerCase();
                item.style.display = (name.includes(query) || position.includes(query)) ? 'block' : 'none';
            });
        });
    });
    </script>
</body>
</html>