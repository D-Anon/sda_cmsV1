<?php
include "../super_admin/user_func.php";
include "../super_admin/header.php";
$activePage = 'user_management';
$roles = getRoles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        .user-management-container {
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-header-gradient {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
        }

        .stats-card {
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(42, 63, 84, 0.1);
        }

        .stats-card-primary {
            background: var(--insurance-blue);
            color: white;
        }

        .stats-card-success {
            background: var(--professional-teal);
            color: white;
        }

        .stats-card-warning {
            background: #F39C12;
            color: white;
        }

        .stats-card-info {
            background: var(--accent-sky);
            color: white;
        }

        .icon-shape {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }

        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.1);
        }

        .table th {
            background: rgba(42, 63, 84, 0.05);
            color: var(--insurance-blue);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table-hover tbody tr:hover {
            background: rgba(42, 63, 84, 0.03);
        }

        .badge-role {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-admin {
            background: rgba(42, 63, 84, 0.1);
            color: var(--insurance-blue);
        }

        .badge-manager {
            background: rgba(26, 188, 156, 0.1);
            color: var(--professional-teal);
        }

        .badge-staff {
            background: rgba(52, 152, 219, 0.1);
            color: var(--accent-sky);
        }

        .badge-active {
            background: rgba(46, 204, 113, 0.1);
            color: #2ECC71;
        }

        .badge-inactive {
            background: rgba(231, 76, 60, 0.1);
            color: #E74C3C;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(42, 63, 84, 0.1);
            color: var(--insurance-blue);
            font-weight: 600;
        }

        .pagination .page-item.active .page-link {
            background: var(--insurance-blue);
            border-color: var(--insurance-blue);
        }

        .pagination .page-link {
            color: var(--insurance-blue);
        }

        .dataTables_filter input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .dataTables_length select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem;
        }
    </style>
</head>
<body class="user-management-container">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0" style="color: var(--insurance-blue);">
                    <i class="fas fa-users-cog me-2"></i>User Management
                </h1>
                <p class="mb-0 text-muted">Manage system users and their access permissions</p>
            </div>
            <a href="user_add.php" class="btn btn-insurance">
                <i class="fas fa-user-plus me-2"></i> Add New User
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4 g-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card stats-card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small">Total Users</div>
                                <div class="h2 mb-0"><?= count(listUsers()) ?></div>
                            </div>
                            <div class="icon-shape">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card stats-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small">Active Users</div>
                                <div class="h2 mb-0"><?= count(array_filter(listUsers(), fn($user) => $user['status'] === 'active')) ?></div>
                            </div>
                            <div class="icon-shape">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card stats-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small">User Roles</div>
                                <div class="h2 mb-0"><?= count($roles) ?></div>
                            </div>
                            <div class="icon-shape">
                                <i class="fas fa-user-tag fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stats-card stats-card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small">Admin Users</div>
                                <div class="h2 mb-0"><?= count(array_filter(listUsers(), fn($user) => $user['role_name'] === 'super_admin')) ?></div>
                            </div>
                            <div class="icon-shape">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header card-header-gradient py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-table me-2"></i>User Records</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-light">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                        <button class="btn btn-sm btn-outline-light">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="table table-hover mb-0" id="userTable">
                        <thead>
                            <tr>
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">User</th>
                                <th class="py-3 px-4">Full Name</th>
                                <th class="py-3 px-4">Position</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = listUsers();
                            if (!empty($users)) {
                                foreach ($users as $user) {
                                    $fullName = "{$user['fname']} {$user['lname']}";
                                    $initials = strtoupper(substr($user['fname'], 0, 1) . substr($user['lname'], 0, 1));
                                    $roleClass = strtolower(str_replace(' ', '-', $user['role_name']));
                                    echo "<tr>";
                                    echo "<td class='px-4'><span class='text-muted'>#{$user['employee_id']}</span></td>";
                                    echo "<td class='px-4'>
                                            <div class='d-flex align-items-center'>
                                                <div class='avatar me-3'>
                                                    {$initials}
                                                </div>
                                                <div>
                                                    <div class='fw-500'>{$user['username']}</div>
                                                    <small class='text-muted'>{$user['email']}</small>
                                                </div>
                                            </div>
                                          </td>";
                                    echo "<td class='px-4 fw-500'>{$fullName}</td>";
                                    echo "<td class='px-4'>{$user['position_name']}</td>";
                                    echo "<td class='px-4'>
                                            <span class='badge text-dark badge-role badge-{$roleClass}'>
                                                <i class='".getRoleIcon($user['role_name'])." me-1'></i>
                                                {$user['role_name']}
                                            </span>
                                          </td>";
                                    echo "<td class='px-4'>
                                            <span class='badge badge-".($user['status'] == 'active' ? 'active' : 'inactive')."'>
                                                <i class='fas fa-circle me-1' style='font-size: 0.5rem;'></i>
                                                ".ucfirst($user['status'])."
                                            </span>
                                          </td>";
                                    echo "<td class='px-4 text-end'>
                                            <div class='d-flex gap-2 justify-content-end'>
                                                <a href='user_view.php?id={$user['id']}' 
                                                   class='btn btn-sm btn-outline-insurance' 
                                                   data-bs-toggle='tooltip' 
                                                   title='View Details'>
                                                    <i class='fas fa-eye'></i>
                                                </a>
                                                <a href='user_update.php?id={$user['id']}' 
                                                   class='btn btn-sm btn-outline-insurance' 
                                                   data-bs-toggle='tooltip' 
                                                   title='Edit User'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                <button class='btn btn-sm btn-outline-danger' 
                                                        onclick='confirmDelete({$user['id']})' 
                                                        data-bs-toggle='tooltip' 
                                                        title='Delete User'>
                                                    <i class='fas fa-trash-alt'></i>
                                                </button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5'>
                                        <div class='py-4'>
                                            <i class='fas fa-user-slash fa-3x text-muted mb-3'></i>
                                            <h5 class='text-muted'>No users found</h5>
                                            <p class='small mb-0'>Start by adding new users to the system</p>
                                        </div>
                                      </td></tr>";
                            }

                            function getRoleIcon($role) {
                                return match($role) {
                                    'Administrator' => 'fas fa-shield-alt',
                                    'Manager' => 'fas fa-user-tie',
                                    default => 'fas fa-user'
                                };
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-circle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-lg bg-danger-soft text-danger rounded-circle me-3">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Delete User Account?</h5>
                            <p class="mb-0">This action cannot be undone and will permanently remove all user data.</p>
                        </div>
                    </div>
                    <div class="alert bg-danger-soft">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-radiation text-danger me-2"></i>
                            <div>
                                <small class="fw-500">Warning:</small>
                                <p class="small mb-0">Deleted accounts cannot be recovered. Proceed with caution.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize DataTable with enhanced options
        $('#userTable').DataTable({
            responsive: true,
            dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-6"i><"col-md-6"p>>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search users...",
                lengthMenu: "Show _MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users found",
                infoFiltered: "(filtered from _MAX_ total users)",
                paginate: {
                    first: "<i class='fas fa-angle-double-left'></i>",
                    last: "<i class='fas fa-angle-double-right'></i>",
                    next: "<i class='fas fa-angle-right'></i>",
                    previous: "<i class='fas fa-angle-left'></i>"
                }
            },
            columnDefs: [
                { orderable: false, targets: -1 },
                { className: 'text-center', targets: [0, 4, 5] }
            ],
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
                $('.dataTables_length select').addClass('form-select');
            }
        });

        // Tooltip initialization
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Row hover effect
        $('#userTable tbody').on('mouseenter', 'tr', function() {
            $(this).addClass('bg-light');
        }).on('mouseleave', 'tr', function() {
            $(this).removeClass('bg-light');
        });
    });

    // Delete confirmation function
    function confirmDelete(userId) {
        $('#confirmModal').modal('show');
        $('#confirmDelete').off('click').on('click', function() {
            window.location.href = 'user_delete.php?id=' + userId;
        });
    }
    </script>
</body>
</html>
<?php include "../super_admin/footer.php"; ?>