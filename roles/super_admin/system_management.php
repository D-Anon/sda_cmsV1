<?php
include "../super_admin/header.php";
include "system_func.php"; // Include the functions for positions and deductions

// Pagination settings
$positionsPerPage = 5; // Number of positions per page
$deductionsPerPage = 5; // Number of deductions per page

// Get the current page for positions
$positionsPage = isset($_GET['positions_page']) ? (int)$_GET['positions_page'] : 1;
$positionsOffset = ($positionsPage - 1) * $positionsPerPage;

// Get the current page for deductions
$deductionsPage = isset($_GET['deductions_page']) ? (int)$_GET['deductions_page'] : 1;
$deductionsOffset = ($deductionsPage - 1) * $deductionsPerPage;

// Fetch paginated data
$positions = getPaginatedPositions($positionsPerPage, $positionsOffset);
$totalPositions = countAllPositions();
$totalPositionsPages = ceil($totalPositions / $positionsPerPage);

$deductions = getPaginatedDeductions($deductionsPerPage, $deductionsOffset);
$totalDeductions = countAllDeductions();
$totalDeductionsPages = ceil($totalDeductions / $deductionsPerPage);
?>

<div class="container mt-5">
    <h1 class="text-center fw-bold">⚙️ System Management</h1>

    <div class="row mt-5 g-3">
        <!-- Positions Section -->
        <div class="col-md-6 d-flex">
            <div class="card shadow-lg flex-fill">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📌 Positions</h5>
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
                                    <?php foreach ($positions as $index => $position) { ?>
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
                        <!-- Pagination Links for Positions -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $positionsPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?positions_page=<?= max(1, $positionsPage - 1) ?>&deductions_page=<?= $deductionsPage ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPositionsPages; $i++) { ?>
                                    <li class="page-item <?= $i == $positionsPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?positions_page=<?= $i ?>&deductions_page=<?= $deductionsPage ?>"><?= $i ?></a>
                                    </li>
                                <?php } ?>
                                <li class="page-item <?= $positionsPage >= $totalPositionsPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?positions_page=<?= min($totalPositionsPages, $positionsPage + 1) ?>&deductions_page=<?= $deductionsPage ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php } else { ?>
                        <p class="text-center text-muted">No positions found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Deductions Section -->
        <div class="col-md-6 d-flex">
            <div class="card shadow-lg flex-fill">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💰 Deductions</h5>
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
                                                    data-amount="<?= number_format($deduction['amount'], 2) ?>"
                                                    data-description="<?= htmlspecialchars($deduction['description'] ?? 'No description available') ?>">
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
                        <!-- Pagination Links for Deductions -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $deductionsPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?positions_page=<?= $positionsPage ?>&deductions_page=<?= max(1, $deductionsPage - 1) ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalDeductionsPages; $i++) { ?>
                                    <li class="page-item <?= $i == $deductionsPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?positions_page=<?= $positionsPage ?>&deductions_page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php } ?>
                                <li class="page-item <?= $deductionsPage >= $totalDeductionsPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?positions_page=<?= $positionsPage ?>&deductions_page=<?= min($totalDeductionsPages, $deductionsPage + 1) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php } else { ?>
                        <p class="text-center text-muted">No deductions found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Position View Modal -->
<div class="modal fade" id="viewPositionModal" tabindex="-1" aria-labelledby="viewPositionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPositionModalLabel">Position Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Position Name:</strong> <span id="positionName"></span></p>
                <p><strong>Salary:</strong> ₱<span id="positionSalary"></span></p>
                <p><strong>Description:</strong></p>
                <p id="positionDescription"></p>
                <hr>
                <h6>Associated Deductions:</h6>
                <ul id="positionDeductionsList" class="list-group">
                    <!-- Deductions will be dynamically added here -->
                </ul>
                <p class="mt-3"><strong>Total Deductions:</strong> ₱<span id="totalDeductions">0.00</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Deduction View Modal -->
<div class="modal fade" id="viewDeductionModal" tabindex="-1" aria-labelledby="viewDeductionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDeductionModalLabel">Deduction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Deduction Name:</strong> <span id="deductionName"></span></p>
                <p><strong>Amount:</strong> ₱<span id="deductionAmount"></span></p>
                <p><strong>Description:</strong></p>
                <p id="deductionDescription"></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Position View Modal
        const positionButtons = document.querySelectorAll('.view-position-btn');
        positionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const positionId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const salary = this.getAttribute('data-salary');
                const description = this.getAttribute('data-description');

                // Populate position details
                document.getElementById('positionName').textContent = name;
                document.getElementById('positionSalary').textContent = salary;
                document.getElementById('positionDescription').textContent = description;

                // Fetch and populate deductions
                const deductionsList = document.getElementById('positionDeductionsList');
                const totalDeductionsElement = document.getElementById('totalDeductions');
                deductionsList.innerHTML = '<li class="list-group-item text-center">Loading...</li>'; // Show loading message
                totalDeductionsElement.textContent = '0.00'; // Reset total deductions

                fetch(`system_posdec.php?position_id=${positionId}`)
                    .then(response => response.json())
                    .then(data => {
                        deductionsList.innerHTML = ''; // Clear loading message
                        let totalDeductions = 0;

                        if (data.length > 0) {
                            data.forEach(deduction => {
                                const listItem = document.createElement('li');
                                listItem.className = 'list-group-item';
                                listItem.textContent = `${deduction.deduction_name} - ₱${parseFloat(deduction.amount).toFixed(2)}`;
                                deductionsList.appendChild(listItem);

                                // Add to total deductions
                                totalDeductions += parseFloat(deduction.amount);
                            });
                        } else {
                            deductionsList.innerHTML = '<li class="list-group-item text-center">No deductions found.</li>';
                        }

                        // Update total deductions
                        totalDeductionsElement.textContent = totalDeductions.toFixed(2);
                    })
                    .catch(error => {
                        console.error('Error fetching deductions:', error);
                        deductionsList.innerHTML = '<li class="list-group-item text-center text-danger">Failed to load deductions.</li>';
                    });
            });
        });

        // Handle Deduction View Modal
        const deductionButtons = document.querySelectorAll('.view-deduction-btn');
        deductionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const name = this.getAttribute('data-name');
                const amount = this.getAttribute('data-amount');
                const description = this.getAttribute('data-description');

                // Populate the modal with deduction details
                document.getElementById('deductionName').textContent = name;
                document.getElementById('deductionAmount').textContent = amount;
                document.getElementById('deductionDescription').textContent = description;
            });
        });
    });
</script>



<?php include "../super_admin/footer.php"; ?>