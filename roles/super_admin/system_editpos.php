<?php
ob_start(); // Start output buffering

include "system_func.php";
include "../super_admin/header.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: system_management.php?error=Invalid position ID');
    exit();
}

$position_id = intval($_GET['id']);
$position = getPositionById($position_id);
$allDeductions = getAllDeductions(); // Fetch all available deductions
$positionDeductions = getDeductionsByPosition($position_id); // Fetch deductions for the position

if (!$position) {
    header('Location: system_management.php?error=Position not found');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = trim($_POST['position_name']);
    $description = trim($_POST['description']);
    $salary = floatval($_POST['salary']);
    $deductions = json_decode($_POST['deductions'], true) ?? [];
    $customDeductions = json_decode($_POST['custom_deductions'], true) ?? [];

    try {
        $conn->beginTransaction();

        // Update the position details
        if (!updatePosition($position_id, $position_name, $description, $salary)) {
            throw new Exception("Failed to update position.");
        }

        // Clear existing deductions for the position
        $stmt = $conn->prepare("DELETE FROM position_deductions WHERE position_id = :position_id");
        $stmt->execute([':position_id' => $position_id]);

        // Add updated deductions
        foreach ($deductions as $deductionId) {
            $stmt = $conn->prepare("INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)");
            $stmt->execute([
                ':position_id' => $position_id,
                ':deduction_id' => $deductionId
            ]);
        }

        // Add custom deductions
        foreach ($customDeductions as $customDeduction) {
            $stmt = $conn->prepare("INSERT INTO deductions (deduction_name, amount) VALUES (:deduction_name, :amount)");
            $stmt->execute([
                ':deduction_name' => $customDeduction['name'],
                ':amount' => $customDeduction['amount']
            ]);

            // Link the custom deduction to the position
            $deductionId = $conn->lastInsertId();
            $stmt = $conn->prepare("INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)");
            $stmt->execute([
                ':position_id' => $position_id,
                ':deduction_id' => $deductionId
            ]);
        }

        $conn->commit();
        header('Location: system_management.php?success=Position updated successfully');
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}
ob_end_flush(); // End output buffering
?>

<div class="container mt-5">
    <h1 class="text-center">Edit Position</h1>
    <?php if (isset($error)) {
        echo "<div class='alert alert-danger'>$error</div>";
    } ?>
    <form action="system_editpos.php?id=<?php echo $position_id; ?>" method="POST">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <input type="text" class="form-control" id="position_name" name="position_name" value="<?php echo htmlspecialchars($position['position_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($position['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="salary" class="form-label">Salary</label>
            <input type="number" class="form-control" id="salary" name="salary" step="0.01" value="<?php echo htmlspecialchars($position['salary']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="deductions" class="form-label">Deductions</label>
            <div class="text-center">
                <button type="button" class="btn btn-primary btn-sm" id="addDeductionRow">
                    <i class="fas fa-plus"></i> Add Deduction
                </button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm table-hover" id="deductionsTable">
                    <thead>
                        <tr>
                            <th>Deduction Name</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="deductionsTableBody">
                        <?php foreach ($positionDeductions as $deduction) { ?>
                            <?php if (isset($deduction['deduction_id'])) { ?>
                                <tr>
                                    <td>
                                        <select class="form-control deduction-select">
                                            <option value="" disabled>Select Deduction</option>
                                            <?php foreach ($allDeductions as $d) { ?>
                                                <option value="<?php echo $d['id']; ?>" data-amount="<?php echo $d['amount']; ?>" <?php echo $d['id'] == $deduction['deduction_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($d['deduction_name']); ?> (₱<?php echo number_format($d['amount'], 2); ?>)
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control deduction-amount" step="0.01" value="<?php echo htmlspecialchars($deduction['amount']); ?>" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-deduction-row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <p class="mt-3"><strong>Total Deductions:</strong> ₱<span id="totalDeductions">0.00</span></p>
            <input type="hidden" id="deductionsInput" name="deductions">
            <input type="hidden" id="customDeductionsInput" name="custom_deductions">
        </div>
        <!-- Add the Update button -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">Update Position</button>
            <a href="system_management.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deductionsTableBody = document.getElementById('deductionsTableBody');
        const deductionsInput = document.getElementById('deductionsInput');
        const customDeductionsInput = document.getElementById('customDeductionsInput');
        const totalDeductionsElement = document.getElementById('totalDeductions');

        function updateDeductionsInput() {
            const deductionIds = [];
            const customDeductions = [];
            let totalDeductions = 0;

            deductionsTableBody.querySelectorAll('tr').forEach(row => {
                const selectElement = row.querySelector('.deduction-select');
                const amountField = row.querySelector('.deduction-amount');
                const amount = parseFloat(amountField.value) || 0;

                if (selectElement.value.startsWith('custom_')) {
                    customDeductions.push({
                        name: selectElement.options[selectElement.selectedIndex].text.split(' (₱')[0],
                        amount: amount
                    });
                } else {
                    deductionIds.push(selectElement.value);
                }

                totalDeductions += amount;
            });

            deductionsInput.value = JSON.stringify(deductionIds);
            customDeductionsInput.value = JSON.stringify(customDeductions);
            totalDeductionsElement.textContent = totalDeductions.toFixed(2);
        }

        function addRemoveDeductionListener(row) {
            const removeButton = row.querySelector('.remove-deduction-row');
            removeButton.addEventListener('click', function() {
                row.remove();
                updateDeductionsInput();
            });
        }

        // Attach event listeners to existing rows
        deductionsTableBody.querySelectorAll('tr').forEach(row => {
            addRemoveDeductionListener(row);
        });

        // Add a new deduction row
        document.getElementById('addDeductionRow').addEventListener('click', function() {
            const row = document.createElement('tr');

            let options = `
            <option value="" disabled>Select Deduction</option>
            <option value="custom">Custom Deduction</option>
            ${<?php echo json_encode($allDeductions); ?>.map(d => `
                <option value="${d.id}" data-amount="${d.amount}">
                    ${d.deduction_name} (₱${parseFloat(d.amount).toFixed(2)})
                </option>
            `).join('')}
        `;

            row.innerHTML = `
            <td>
                <select class="form-control deduction-select">
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" class="form-control deduction-amount" step="0.01" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-deduction-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

            deductionsTableBody.appendChild(row);

            // Attach event listener to the new row
            addRemoveDeductionListener(row);

            // Update deductions input when a new row is added
            row.querySelector('.deduction-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const amountField = row.querySelector('.deduction-amount');

                if (this.value === "custom") {
                    amountField.removeAttribute('readonly');
                    amountField.value = '';
                } else {
                    amountField.setAttribute('readonly', true);
                    amountField.value = parseFloat(selectedOption.getAttribute('data-amount')).toFixed(2);
                }

                updateDeductionsInput();
            });

            // Update total deductions when the amount changes
            row.querySelector('.deduction-amount').addEventListener('input', updateDeductionsInput);
        });

        // Initial calculation of total deductions
        updateDeductionsInput();
    });
</script>

<?php include "../super_admin/footer.php"; ?>