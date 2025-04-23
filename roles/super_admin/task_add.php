<?php
require_once '../super_admin/task_func.php';
$users = $taskManager->getAllUsers();

include "../super_admin/header.php";
$activePage = 'task_management'; // for dashboard.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Task</title>
    <!-- Add Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(42, 63, 84, 0.1);
        }

        h1 {
            color: var(--insurance-blue);
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--professional-teal);
            padding-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            color: var(--insurance-blue);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        textarea,
        select,
        input[type="date"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: border-color 0.2s;
            color: var(--text-primary);
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus,
        input[type="date"]:focus {
            border-color: var(--accent-sky);
            outline: none;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(42, 63, 84, 0.25);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--insurance-blue);
            border: 2px solid var(--insurance-blue);
        }

        .member-modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .member-card {
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .member-card:hover {
            border-color: var(--accent-sky);
            background: #f8f9fa;
        }

        .member-card.selected {
            border-color: var(--professional-teal);
            background: #e8f4f1;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 10px;
        }

        .selected-members {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .selected-member {
            background: var(--professional-teal);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* ... existing styles ... */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Create New Policy Task</h1>

        <form id="taskForm">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="member_ids" id="member_ids">

            <div class="form-group">
                <label for="task_name">Task Name</label>
                <input type="text" id="task_name" name="task_name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" required>
            </div>

            <div class="form-group">
                <label for="leader_id">Task Leader</label>
                <select id="leader_id" name="leader_id" class="form-control" required>
                    <option value="">Select Leader</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['position']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Task Members</label>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#membersModal">
                    Select Team Members
                </button>
                <div class="selected-members" id="selectedMembers"></div>
            </div>

            <div class="form-actions d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Task</button>
                <button type="button" class="btn btn-secondary" onclick="location.href='../super_admin/task_management.php'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Members Modal -->
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content member-modal-content">
                <div class="modal-header" style="background: var(--insurance-blue); color: white;">
                    <h5 class="modal-title">Select Team Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <input type="text" id="memberSearch" placeholder="Search members..." class="form-control mb-3">
                    <div class="row g-3">
                        <?php foreach ($users as $user): ?>
                            <div class="col-md-6">
                                <div class="member-card">
                                    <div class="checkbox-container">
                                        <input type="checkbox"
                                            class="member-checkbox"
                                            value="<?= $user['id'] ?>"
                                            id="member_<?= $user['id'] ?>">
                                        <label for="member_<?= $user['id'] ?>" class="flex-grow-1">
                                            <strong><?= htmlspecialchars($user['full_name']) ?></strong><br>
                                            <small><?= htmlspecialchars($user['position']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for leader search
            $('#leader_id').select2({
                placeholder: "Search for a leader...",
                width: '100%'
            });

            // Member search functionality
            const memberSearch = document.getElementById('memberSearch');
            memberSearch.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.member-card').forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.closest('.col-md-6').style.display =
                        text.includes(query) ? 'block' : 'none';
                });
            });

            // Existing member selection logic
            const checkboxes = document.querySelectorAll('.member-checkbox');
            const selectedMembersDiv = document.getElementById('selectedMembers');
            const memberIdsInput = document.getElementById('member_ids');

            function updateSelectedMembers() {
                const selected = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => {
                        return {
                            id: cb.value,
                            name: cb.parentNode.querySelector('strong').textContent
                        };
                    });

                memberIdsInput.value = selected.map(m => m.id).join(',');
                selectedMembersDiv.innerHTML = selected
                    .map(m => `<span class="selected-member">${m.name}</span>`)
                    .join('');
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectedMembers);
            });

            // Form submission logic
            document.getElementById('taskForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('task_func.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        window.location.href = '../super_admin/task_management.php';
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('An error occurred: ' + error.message);
                }
            });
        });
    </script>
</body>

</html>