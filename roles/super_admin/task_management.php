<?php
include '../super_admin/task_func.php';
$tasks = $taskManager->getAllTasks();
include '../super_admin/header.php';
?>

<style>
    :root {
        --insurance-blue: #2A3F54;
        --professional-teal: #1ABC9C;
        --trustworthy-navy: #0F1C2D;
        --accent-sky: #3498DB;
        --text-primary: #4A6572;
    }

    .btn-insurance-primary {
        background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
        border: none;
        transition: all 0.3s ease;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
    }
    
    .btn-insurance-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(42, 63, 84, 0.25);
        color: white;
    }
    
    .task-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(42, 63, 84, 0.1);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .task-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 32px rgba(42, 63, 84, 0.1);
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
        color: white;
        padding: 1.25rem;
        border-bottom: none;
    }
    
    .description-text {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--text-primary);
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .member-list-modal .modal-header {
        background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
        color: white;
        border-bottom: none;
        padding: 1.25rem;
    }
    
    .member-item {
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .member-item:last-child {
        border-bottom: none;
    }
    
    .member-item:hover {
        background-color: #f8f9fa;
    }
    
    .view-members-btn {
        color: var(--accent-sky) !important;
        transition: all 0.2s;
        font-weight: 500;
        padding: 0;
        border: none;
        background: none;
    }
    
    .view-members-btn:hover {
        color: var(--professional-teal) !important;
        transform: translateX(3px);
    }

    .deadline-badge {
        background: #E8F4F1;
        border-left: 4px solid var(--professional-teal);
        color: var(--insurance-blue);
        padding: 1rem;
        border-radius: 8px;
        margin: 1.25rem 0;
    }

    .task-meta-icon {
        color: var(--insurance-blue);
        width: 20px;
        text-align: center;
    }

    .btn-outline-insurance {
        border: 2px solid var(--insurance-blue);
        color: var(--insurance-blue);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }

    .btn-outline-insurance:hover {
        background: var(--insurance-blue);
        color: white;
        border-color: var(--insurance-blue);
    }

    .btn-archive {
        background: var(--professional-teal);
        color: white;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        border: 2px solid var(--professional-teal);
    }

    .btn-archive:hover {
        background: #16a085;
        border-color: #16a085;
        color: white;
    }
</style>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold mb-0" style="color: var(--insurance-blue);">
            <i class="fas fa-tasks me-2"></i>Policy Task Management
        </h2>
        <button class="btn btn-insurance-primary" onclick="location.href='task_add.php'">
            <i class="fas fa-plus me-2"></i>Create New Task
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($tasks as $task): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="task-card card h-100">
                <div class="card-header card-header-custom">
                    <h5 class="card-title mb-0"><?= htmlspecialchars($task['task_name']) ?></h5>
                </div>
                <div class="card-body">
                    <p class="card-text description-text mb-4">
                        <?= htmlspecialchars($task['description']) ?>
                    </p>

                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-shield task-meta-icon"></i>
                        <span class="text-dark fs-6"><?= htmlspecialchars($task['leader_fname'] . ' ' . $task['leader_lname']) ?></span>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-users task-meta-icon"></i>
                        <button class="btn btn-link view-members-btn p-0 text-decoration-none" 
                                data-members="<?= htmlspecialchars($task['member_names']) ?>" 
                                data-bs-toggle="modal" 
                                data-bs-target="#membersModal">
                            View Team (<?= count(explode(',', $task['member_names'])) ?>)
                        </button>
                    </div>

                    <div class="deadline-badge">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <span class="fw-semibold">
                                <?= date('M d, Y', strtotime($task['deadline'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between border-top pt-3 mt-3">
                        <a href="task_edit.php?id=<?= $task['id'] ?>" class="btn btn-outline-insurance btn-sm">
                            <i class="fas fa-edit me-2"></i>Modify
                        </a>
                        <button class="btn btn-archive btn-sm delete-task" data-id="<?= $task['id'] ?>">
                            <i class="fas fa-archive me-2"></i>Archive
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Delete/Archive Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: var(--insurance-blue);">
                    <i class="fas fa-archive me-2"></i>Confirm Archival
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-dark">
                    Are you sure you want to archive this task? Archived tasks can be restored from the archive section.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-insurance-primary" id="confirmDelete">Confirm Archive</button>
            </div>
        </div>
    </div>
</div>

<!-- Team Members Modal -->
<div class="modal fade member-list-modal" id="membersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: var(--insurance-blue);">
                    <i class="fas fa-users me-2"></i>Assigned Team
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="membersList">
                <!-- Members populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-insurance-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Archive Task Handler
    document.querySelectorAll('.delete-task').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDelete').onclick = () => {
                window.location.href = `task_delete.php?id=${taskId}`;
            };
            modal.show();
        });
    });

    // Team Members Modal Handler
    const membersModal = document.getElementById('membersModal');
    const membersList = document.getElementById('membersList');
    
    membersModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const members = button.getAttribute('data-members').split(',');
        
        membersList.innerHTML = members.map(member => `
            <div class="member-item">
                <i class="fas fa-user-check" style="color: var(--professional-teal);"></i>
                <span>${member.trim()}</span>
            </div>
        `).join('');
    });
});
</script>

<?php include '../super_admin/footer.php'; ?>