<?php
include "../super_admin/system_companyfunc.php";
include "../super_admin/header.php";
$activePage = 'company';

// Handle delete request
if (isset($_POST['delete_company_id'])) {
    $companyId = $_POST['delete_company_id'];

    // Delete the company from the database
    $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
    if ($stmt->execute([$companyId])) {
        $deleteMessage = "Company deleted successfully.";
    } else {
        $deleteError = "Failed to delete the company. Please try again.";
    }
}

function listCompanies() {
    global $conn;

    // Use DISTINCT to ensure unique rows are fetched
    $stmt = $conn->prepare("SELECT DISTINCT id, name, address, phone, created_at FROM companies ORDER BY created_at DESC");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Management</title>
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        .company-card {
            border-radius: 12px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.05);
        }

        .company-header {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
        }

        .btn-insurance {
            background: var(--insurance-blue);
            color: white;
            border: none;
            transition: all 0.2s;
        }

        .btn-insurance:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(42, 63, 84, 0.1);
        }

        .btn-outline-insurance {
            border: 1px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
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

        .empty-state-icon {
            color: rgba(42, 63, 84, 0.2);
        }

        .company-phone {
            color: var(--accent-sky);
            font-weight: 500;
        }

        .company-date {
            color: var(--text-primary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">
                    <i class="fas fa-building me-2" style="color: var(--insurance-blue);"></i>Company Management
                </h2>
                <p class="text-muted mb-0 mt-1">Manage partner organizations and their details</p>
            </div>
            <a href="system_companyadd.php" class="btn btn-insurance">
                <i class="fas fa-plus me-2"></i>Add Company
            </a>
        </div>

        <!-- Display success or error messages -->
        <?php if (isset($deleteMessage)): ?>
            <div id="deleteMessage" class="alert alert-success text-center">
                <?= htmlspecialchars($deleteMessage) ?>
            </div>
        <?php elseif (isset($deleteError)): ?>
            <div id="deleteError" class="alert alert-danger text-center">
                <?= htmlspecialchars($deleteError) ?>
            </div>
        <?php endif; ?>

        <div class="card company-card">
            <div class="card-header company-header py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Company List</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="companyTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count(listCompanies()) > 0): ?>
                                <?php foreach(listCompanies() as $company): ?>
                                <tr>
                                    <td class="fw-medium"><?= htmlspecialchars($company['name']) ?></td>
                                    <td><?= htmlspecialchars($company['address']) ?></td>
                                    <td class="company-phone"><?= htmlspecialchars($company['phone']) ?></td>
                                    <td class="company-date"><?= date('M d, Y', strtotime($company['created_at'])) ?></td>
                                    <td>
                                        <a href="system_companyedit.php?id=<?= $company['id'] ?>" class="btn btn-outline-insurance btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="delete_company_id" value="<?= $company['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-building empty-state-icon fa-3x mb-3"></i>
                                        <p class="text-muted">No companies found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Automatically hide the success or error message after 2 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const deleteMessage = document.getElementById('deleteMessage');
            const deleteError = document.getElementById('deleteError');

            if (deleteMessage) {
                setTimeout(() => {
                    deleteMessage.style.display = 'none';
                }, 2000); // 2 seconds
            }

            if (deleteError) {
                setTimeout(() => {
                    deleteError.style.display = 'none';
                }, 2000); // 2 seconds
            }
        });

        // Initialize DataTable
        $(document).ready(function() {
            $('#companyTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ],
                "language": {
                    "search": "<i class='fas fa-search'></i>",
                    "searchPlaceholder": "Search companies...",
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i>",
                        "next": "<i class='fas fa-chevron-right'></i>"
                    }
                }
            });
        });
    </script>

</body>
</html>
<?php include "../super_admin/footer.php"; ?>