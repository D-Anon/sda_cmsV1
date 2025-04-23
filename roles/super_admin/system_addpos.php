<?php
ob_start();
include "system_func.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = trim($_POST['position_name']);
    $description = trim($_POST['description']);
    $salary = floatval($_POST['salary']);
    $deductions = json_decode($_POST['deductions'], true) ?? [];
    $customDeductions = json_decode($_POST['custom_deductions'], true) ?? [];

    try {
        $conn->beginTransaction();

        // Insert the position into the `positions` table
        $stmt = $conn->prepare("INSERT INTO positions (position_name, description, salary) VALUES (:position_name, :description, :salary)");
        $stmt->bindParam(':position_name', $position_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':salary', $salary);
        $stmt->execute();

        // Get the ID of the newly created position
        $positionId = $conn->lastInsertId();

        // Insert predefined deductions into the `position_deductions` table
        foreach ($deductions as $deductionId) {
            $stmt = $conn->prepare("INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)");
            $stmt->bindParam(':position_id', $positionId);
            $stmt->bindParam(':deduction_id', $deductionId);
            $stmt->execute();
        }

        // Insert custom deductions into the `deductions` table and link them in `position_deductions`
        foreach ($customDeductions as $customDeduction) {
            $stmt = $conn->prepare("INSERT INTO deductions (deduction_name, amount) VALUES (:deduction_name, :amount)");
            $stmt->bindParam(':deduction_name', $customDeduction['name']);
            $stmt->bindParam(':amount', $customDeduction['amount']);
            $stmt->execute();

            // Get the ID of the newly created custom deduction
            $deductionId = $conn->lastInsertId();

            // Link the custom deduction to the position in `position_deductions`
            $stmt = $conn->prepare("INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)");
            $stmt->bindParam(':position_id', $positionId);
            $stmt->bindParam(':deduction_id', $deductionId);
            $stmt->execute();
        }

        $conn->commit();
        header('Location: system_management.php?success=Position added successfully');
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

$allDeductions = getAllDeductions();
include "../super_admin/header.php";
ob_end_flush();
?>

<style>
    .dropdown-wrapper {
        position: relative;
    }

    .dropdown-caret {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: #6c757d;
    }

    .deduction-select {
        padding-right: 30px;
        appearance: none;
    }
</style>

<div class="container mt-5">
    <h1 class="text-center">Add Position</h1>
    <?php if (isset($error)) {
        echo "<div class='alert alert-danger'>$error</div>";
    } ?>
    <form action="system_addpos.php" method="POST">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <input type="text" class="form-control" id="position_name" name="position_name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="salary" class="form-label">Salary</label>
            <input type="number" class="form-control" id="salary" name="salary" step="0.01" required>
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
                    </tbody>
                </table>
            </div>
            <p class="mt-2">Total Deductions: ₱<span id="totalDeductions">0.00</span></p>
            <input type="hidden" id="deductionsInput" name="deductions">
            <input type="hidden" id="customDeductionsInput" name="custom_deductions">
        </div>
        <button type="submit" class="btn btn-primary">Add Position</button>
        <a href="system_management.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addDeductionRowButton = document.getElementById('addDeductionRow');
        const deductionsTableBody = document.getElementById('deductionsTableBody');
        const totalDeductionsElement = document.getElementById('totalDeductions');
        const deductionsInput = document.getElementById('deductionsInput');
        const customDeductionsInput = document.getElementById('customDeductionsInput');
        let totalDeductions = 0;

        const deductions = <?php echo json_encode($allDeductions); ?>;

        function updateDeductionsInput() {
            const deductionIds = [];
            const customDeductions = [];

            deductionsTableBody.querySelectorAll('tr').forEach(row => {
                const selectElement = row.querySelector('.deduction-select');
                const amountField = row.querySelector('.deduction-amount');

                if (selectElement.value.startsWith('custom_')) {
                    customDeductions.push({
                        name: selectElement.options[selectElement.selectedIndex].text.split(' (₱')[0],
                        amount: parseFloat(amountField.value)
                    });
                } else {
                    deductionIds.push(selectElement.value);
                }
            });

            deductionsInput.value = JSON.stringify(deductionIds);
            customDeductionsInput.value = JSON.stringify(customDeductions);
        }

        function addDeductionRow() {
            const row = document.createElement('tr');

            let options = `
            <option value="custom">Custom Deduction</option>
            ${deductions.map(d => `
                <option value="${d.id}" data-amount="${d.amount}">
                    ${d.deduction_name} (₱${parseFloat(d.amount).toFixed(2)})
                </option>
            `).join('')}
        `;

            row.innerHTML = `
            <td>
                <div class="dropdown-wrapper position-relative">
                    <select class="form-control deduction-select">
                        <option value="" disabled selected>Select Deduction</option>
                        ${options}
                    </select>
                    <i class="fas fa-caret-down dropdown-caret"></i> 
                </div>
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

            row.querySelector('.remove-deduction-row').addEventListener('click', function() {
                const amount = parseFloat(row.querySelector('.deduction-amount').value) || 0;
                totalDeductions -= amount;
                totalDeductionsElement.textContent = totalDeductions.toFixed(2);
                row.remove();
                updateDeductionsInput();
            });

            row.querySelector('.deduction-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const amountField = row.querySelector('.deduction-amount');

                if (this.value === "custom") {
                    // Handle custom deduction logic
                } else {
                    const amount = parseFloat(selectedOption.getAttribute('data-amount')) || 0;
                    totalDeductions += amount - (parseFloat(amountField.value) || 0);
                    totalDeductionsElement.textContent = totalDeductions.toFixed(2);
                    amountField.value = amount.toFixed(2);
                    updateDeductionsInput();
                }
            });
        }

        addDeductionRowButton.addEventListener('click', addDeductionRow);
    });
</script>

<?php include "../super_admin/footer.php"; ?>