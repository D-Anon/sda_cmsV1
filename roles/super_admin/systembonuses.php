<?php
include "../super_admin/system_bonus_func.php";
include "../super_admin/db.php"; // Include database connection

// Handle delete request
if (isset($_GET['delete_id']) && isset($_GET['delete_action'])) { // Check for delete_action flag
    $deleteId = $_GET['delete_id'];

    if (deleteBonus($conn, $deleteId)) {
        // Redirect back to the bonuses page with a success message
        header("Location: bonus.php?success=1");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: bonus.php?error=" . urlencode("Error deleting bonus.") . "&delete_action=1");
        exit();
    }
}

// Pagination settings
$bonusesPerPage = 5; // Number of bonuses per page
$bonusesPage = isset($_GET['bonuses_page']) ? (int)$_GET['bonuses_page'] : 1;
$bonusesOffset = ($bonusesPage - 1) * $bonusesPerPage;

// Fetch paginated data
$bonuses = getPaginatedBonuses($bonusesPerPage, $bonusesOffset);
$totalBonuses = countAllBonuses();
$totalBonusesPages = ceil($totalBonuses / $bonusesPerPage);

include "../super_admin/header.php";
?>

<div class="container mt-5">
    <h1 class="text-center fw-bold">💰 Bonus Management</h1>
    <div class="card shadow-lg mt-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bonuses</h5>
            <a href="system_bonus_add.php" class="btn btn-light btn-sm">➕ Add Bonus</a>
        </div>
        <div class="card-body">
            <!-- Display success or error messages -->
            <?php if (isset($_GET['success'])) { ?>
                <div id="notification" class="alert alert-success">Bonus deleted successfully.</div>
            <?php } elseif (isset($_GET['error']) && isset($_GET['delete_action'])) { ?>
                <div id="notification" class="alert alert-danger"><?= htmlspecialchars($_GET['error']); ?></div>
            <?php } ?>
            
            <?php if (!empty($bonuses)) { ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Period</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bonuses as $index => $bonus) {
                                // Fetch employee details using employee_id
                                $query = "SELECT fname, mname, lname, suffix FROM users WHERE employee_id = :employee_id";
                                $stmt = $conn->prepare($query);
                                $stmt->bindParam(':employee_id', $bonus['employee_id']);
                                $stmt->execute();
                                $employee = $stmt->fetch(PDO::FETCH_ASSOC);

                                // Combine the employee's full name
                                $fullName = $employee
                                    ? trim("{$employee['fname']} {$employee['mname']} {$employee['lname']} {$employee['suffix']}")
                                    : "N/A";

                                // Check if position is NULL and replace with "N/A"
                                $position = $bonus['position'] ?? "N/A";
                            ?>
                                <tr>
                                    <td><?= $bonusesOffset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($bonus['name']) ?></td>
                                    <td>₱<?= number_format($bonus['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($position) ?></td>
                                    <td><?= htmlspecialchars($bonus['start_period']) ?> to <?= htmlspecialchars($bonus['end_period']) ?></td>
                                    <td>
                                        <a href="bonus_edit.php?id=<?= $bonus['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                        <button class="btn btn-danger btn-sm" onclick="showDeleteModal(<?= $bonus['id'] ?>)">🗑️ Delete</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $bonusesPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?bonuses_page=<?= max(1, $bonusesPage - 1) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalBonusesPages; $i++) { ?>
                            <li class="page-item <?= $i == $bonusesPage ? 'active' : '' ?>">
                                <a class="page-link" href="?bonuses_page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= $bonusesPage >= $totalBonusesPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?bonuses_page=<?= min($totalBonusesPages, $bonusesPage + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php } else { ?>
                <p class="text-center text-muted">No bonuses found.</p>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this bonus?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    function showDeleteModal(bonusId) {
        // Set the delete link in the modal
        const deleteLink = document.getElementById('confirmDeleteBtn');
        deleteLink.href = `bonus.php?delete_id=${bonusId}&delete_action=1`;

        // Show the modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Automatically hide the notification after 2 seconds and remove query parameters
    document.addEventListener('DOMContentLoaded', function () {
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.display = 'none';

                // Remove query parameters from the URL
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                url.searchParams.delete('error');
                url.searchParams.delete('delete_action');
                window.history.replaceState({}, document.title, url.toString());
            }, 2000); // 2 seconds
        }
    });
</script>

<?php include "../super_admin/footer.php"; ?>