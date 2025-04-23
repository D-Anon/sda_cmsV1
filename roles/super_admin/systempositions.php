<?php
include "../super_admin/header.php";
include "system_func.php"; // Include the functions for positions and deductions

// Pagination settings
$positionsPerPage = 5; // Number of positions per page
$positionsPage = isset($_GET['positions_page']) ? (int)$_GET['positions_page'] : 1;
$positionsOffset = ($positionsPage - 1) * $positionsPerPage;

// Fetch paginated data
$positions = getPaginatedPositions($positionsPerPage, $positionsOffset);
$totalPositions = countAllPositions();
$totalPositionsPages = ceil($totalPositions / $positionsPerPage);
?>

<div class="container mt-5">
    <h1 class="text-center fw-bold">📌 Positions</h1>
    <div class="card shadow-lg mt-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Positions</h5>
            <a href="system_addpos.php" class="btn btn-light btn-sm">➕ Add Position</a>
        </div>
        <div class="card-body">
            <?php if (!empty($positions)) { ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($positions as $index => $position) {
                                // Fetch deductions for the current position
                                $deductions = getDeductionsByPosition($position['id']);
                            ?>
                                <tr>
                                    <td><?= $positionsOffset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($position['position_name']) ?></td>
                                    <td>₱<?= number_format($position['salary'], 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm view-position-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewPositionModal"
                                            data-id="<?= $position['id'] ?>"
                                            data-name="<?= htmlspecialchars($position['position_name']) ?>"
                                            data-salary="<?= number_format($position['salary'], 2) ?>"
                                            data-description="<?= htmlspecialchars($position['description']) ?>">
                                            👁️ View
                                        </button>
                                        <a href="system_editpos.php?id=<?= $position['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                        <a href="system_deletepos.php?id=<?= $position['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this position?')">🗑️ Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $positionsPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?positions_page=<?= max(1, $positionsPage - 1) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPositionsPages; $i++) { ?>
                            <li class="page-item <?= $i == $positionsPage ? 'active' : '' ?>">
                                <a class="page-link" href="?positions_page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= $positionsPage >= $totalPositionsPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?positions_page=<?= min($totalPositionsPages, $positionsPage + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php } else { ?>
                <p class="text-center text-muted">No positions found.</p>
            <?php } ?>
        </div>
    </div>
</div>

<!-- View Position Modal -->
<div class="modal fade" id="viewPositionModal" tabindex="-1" aria-labelledby="viewPositionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewPositionModalLabel">Position Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Position Name:</strong> <span id="positionName"></span></p>
                <p><strong>Salary:</strong> ₱<span id="positionSalary"></span></p>
                <p><strong>Description:</strong> <span id="positionDescription"></span></p>
            </div>
            <div>
                <?php foreach ($positions as $index => $position) {
                    // Fetch deductions for the current position
                    $deductions = getDeductionsByPosition($position['id']);
                ?>
                    <tr>
                        <td>
                            <?php if (!empty($deductions)) { ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($deductions as $deduction) { ?>
                                        <li><?= htmlspecialchars($deduction['deduction_name']) ?> - ₱<?= number_format($deduction['amount'], 2) ?></li>
                                    <?php } ?>
                                </ul>
                            <?php } else { ?>
                                <!-- <span class="text-muted">No deductions</span> -->
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate the modal with position details
    document.querySelectorAll('.view-position-btn').forEach(button => {
        button.addEventListener('click', function() {
            const positionName = this.getAttribute('data-name');
            const positionSalary = this.getAttribute('data-salary');
            const positionDescription = this.getAttribute('data-description');

            document.getElementById('positionName').textContent = positionName;
            document.getElementById('positionSalary').textContent = positionSalary;
            document.getElementById('positionDescription').textContent = positionDescription;
        });
    });
</script>

<?php include "../super_admin/footer.php"; ?>