<?php
include "../super_admin/header.php";
include "system_func.php"; // Include the functions for deductions

// Pagination settings
$deductionsPerPage = 5; // Number of deductions per page
$deductionsPage = isset($_GET['deductions_page']) ? (int)$_GET['deductions_page'] : 1;
$deductionsOffset = ($deductionsPage - 1) * $deductionsPerPage;

// Fetch paginated data
$deductions = getPaginatedDeductions($deductionsPerPage, $deductionsOffset);
$totalDeductions = countAllDeductions();
$totalDeductionsPages = ceil($totalDeductions / $deductionsPerPage);
?>

<div class="container mt-5">
    <h1 class="text-center fw-bold">💰 Deductions</h1>
    <div class="card shadow-lg mt-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Deductions</h5>
            <a href="system_addduc.php" class="btn btn-light btn-sm">➕ Add Deduction</a>
        </div>
        <div class="card-body">
            <?php if (!empty($deductions)) { ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Deduction</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deductions as $index => $deduction) { ?>
                                <tr>
                                    <td><?= $deductionsOffset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($deduction['deduction_name']) ?></td>
                                    <td>₱<?= number_format($deduction['amount'], 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm view-deduction-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewDeductionModal"
                                            data-id="<?= $deduction['id'] ?>"
                                            data-name="<?= htmlspecialchars($deduction['deduction_name']) ?>"
                                            data-amount="<?= number_format($deduction['amount'], 2) ?>">
                                            👁️ View
                                        </button>
                                        <a href="system_editduc.php?id=<?= $deduction['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                        <a href="system_deleteduc.php?id=<?= $deduction['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this deduction?')">🗑️ Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $deductionsPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?deductions_page=<?= max(1, $deductionsPage - 1) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalDeductionsPages; $i++) { ?>
                            <li class="page-item <?= $i == $deductionsPage ? 'active' : '' ?>">
                                <a class="page-link" href="?deductions_page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= $deductionsPage >= $totalDeductionsPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?deductions_page=<?= min($totalDeductionsPages, $deductionsPage + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php } else { ?>
                <p class="text-center text-muted">No deductions found.</p>
            <?php } ?>
        </div>
    </div>
</div>

<!-- View Deduction Modal -->
<div class="modal fade" id="viewDeductionModal" tabindex="-1" aria-labelledby="viewDeductionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewDeductionModalLabel">Deduction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Deduction Name:</strong> <span id="deductionName"></span></p>
                <p><strong>Amount:</strong> ₱<span id="deductionAmount"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate the modal with deduction details
    document.querySelectorAll('.view-deduction-btn').forEach(button => {
        button.addEventListener('click', function () {
            const deductionName = this.getAttribute('data-name');
            const deductionAmount = this.getAttribute('data-amount');

            document.getElementById('deductionName').textContent = deductionName;
            document.getElementById('deductionAmount').textContent = deductionAmount;
        });
    });
</script>

<?php include "../super_admin/footer.php"; ?>