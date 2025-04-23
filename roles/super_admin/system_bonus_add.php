<?php
include "../super_admin/system_bonus_func.php";
include "../super_admin/db.php"; // Include database connection

// Fetch employees and positions
$employees = getEmployees();
$positions = getPositions();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = $_POST['name'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $selectedEmployee = !empty($_POST['selected_employee']) ? $_POST['selected_employee'] : null;
    $selectedPosition = !empty($_POST['selected_position']) ? $_POST['selected_position'] : null;
    $startPeriod = $_POST['start_period'] ?? '';
    $endPeriod = $_POST['end_period'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Bonus name is required.";
    }
    if (empty($amount) || $amount <= 0) {
        $errors[] = "A valid bonus amount is required.";
    }
    if (empty($startPeriod)) {
        $errors[] = "Start period is required.";
    }
    if (empty($endPeriod)) {
        $errors[] = "End period is required.";
    }

    // Either employee or position must be selected (but not necessarily both)
    if (empty($selectedEmployee) && empty($selectedPosition)) {
        $errors[] = "Either an employee or a position must be selected.";
    }

    // Check if the selected employee exists in the database
    if (!empty($selectedEmployee)) {
        $query = "SELECT employee_id FROM users WHERE employee_id = :employee_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':employee_id', $selectedEmployee);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            $errors[] = "Selected employee does not exist.";
        }
    }

    if (empty($errors)) {
        try {
            // Insert data into the database with proper NULL handling
            $query = "INSERT INTO bonus (name, amount, employee_id, position, start_period, end_period) 
                      VALUES (:name, :amount, :employee_id, :position, :start_period, :end_period)";
            $stmt = $conn->prepare($query);
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':amount', $amount);
            
            // Proper NULL handling for employee_id
            if (empty($selectedEmployee)) {
                $stmt->bindValue(':employee_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':employee_id', $selectedEmployee);
            }
            
            // Proper NULL handling for position
            if (empty($selectedPosition)) {
                $stmt->bindValue(':position', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':position', $selectedPosition);
            }
            
            $stmt->bindParam(':start_period', $startPeriod);
            $stmt->bindParam(':end_period', $endPeriod);
            
            $stmt->execute();

            // Redirect to the bonuses page with a success message
            header("Location: bonus.php?success=1");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error saving bonus: " . $e->getMessage();
            // Log the full error for debugging
            error_log("Bonus insertion error: " . $e->getMessage());
            error_log("Attempted values - Name: $name, Amount: $amount, Employee: " . ($selectedEmployee ?? 'NULL') . 
                     ", Position: " . ($selectedPosition ?? 'NULL'));
        }
    }
}
include "../super_admin/header.php"; ?>

<div class="container mt-4">
    <h3 class="text-center fw-semibold h1">Add Bonus</h3>

    <!-- Display error messages if any -->
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form action="bonus_add.php" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Bonus Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter bonus name">
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter bonus amount">
            </div>
        </div>

        <!-- Employee Selection -->
        <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <div class="input-group">
                <input type="text" class="form-control" id="selectedEmployee" name="selected_employee" placeholder="Select Employee" readonly>
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#employeeModal">
                    <i class="bi bi-person"></i>
                </button>
                <button class="btn btn-outline-danger" type="button" id="removeEmployee">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>

        <!-- Position Selection -->
        <div class="mb-3">
            <label class="form-label">Select Position</label>
            <div class="input-group">
                <input type="text" class="form-control" id="selectedPosition" name="selected_position" placeholder="Select Position" readonly>
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#positionModal">
                    <i class="bi bi-briefcase"></i>
                </button>
                <button class="btn btn-outline-danger" type="button" id="removePosition">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>

        <!-- Period -->
        <div class="mb-3">
            <label class="form-label">Period</label>
            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <label for="startPeriod" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startPeriod" name="start_period">
                </div>
                <div class="flex-grow-1">
                    <label for="endPeriod" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endPeriod" name="end_period">
                </div>
            </div>
        </div>

        <!-- Save and Cancel Buttons -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-save"></i> Save
            </button>
            <a href="bonus.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="employeeSearch" placeholder="Search employee...">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <?php foreach ($employees as $employee) {
                                $fullName = trim("{$employee['fname']} {$employee['mname']} {$employee['lname']} {$employee['suffix']}");
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($employee['employee_id']); ?></td>
                                    <td><?= htmlspecialchars($fullName); ?></td>
                                    <td>
                                        <input class="form-check-input employee-radio" type="radio" name="employeeRadio"
                                            value="<?= htmlspecialchars($employee['employee_id']); ?>"
                                            data-name="<?= htmlspecialchars($fullName); ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" id="addEmployee">Add</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Position Modal -->
<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="positionSearch" placeholder="Search position...">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Position Name</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody id="positionTableBody">
                            <?php foreach ($positions as $position) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($position['position_name']); ?></td>
                                    <td>
                                        <input class="form-check-input position-radio" type="radio" name="positionRadio"
                                            value="<?= htmlspecialchars($position['position_name']); ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" id="addPosition">Add</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Search functionality for Employee Modal
    document.getElementById('employeeSearch').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTableBody tr');
        rows.forEach(row => {
            const fullName = row.cells[1].textContent.toLowerCase();
            const employeeId = row.cells[0].textContent.toLowerCase();
            if (fullName.includes(searchValue) || employeeId.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Search functionality for Position Modal
    document.getElementById('positionSearch').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#positionTableBody tr');
        rows.forEach(row => {
            const positionName = row.cells[0].textContent.toLowerCase();
            if (positionName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Add Employee Selection
    document.getElementById('addEmployee').addEventListener('click', function () {
        const selectedRadio = document.querySelector('.employee-radio:checked');
        if (selectedRadio) {
            const selectedEmployee = document.getElementById('selectedEmployee');
            selectedEmployee.value = selectedRadio.value; // Set the employee_id
            selectedEmployee.dispatchEvent(new Event('input')); // Trigger input event
            bootstrap.Modal.getInstance(document.getElementById('employeeModal')).hide();
        } else {
            alert("Please select an employee.");
        }
    });

    // Add Position Selection
    document.getElementById('addPosition').addEventListener('click', function () {
        const selectedRadio = document.querySelector('.position-radio:checked');
        if (selectedRadio) {
            const selectedPosition = document.getElementById('selectedPosition');
            selectedPosition.value = selectedRadio.value;
            selectedPosition.dispatchEvent(new Event('input')); // Trigger input event
            bootstrap.Modal.getInstance(document.getElementById('positionModal')).hide();
        }
    });

    // Remove Employee Selection
    document.getElementById('removeEmployee').addEventListener('click', function () {
        const employeeField = document.getElementById('selectedEmployee');
        const positionField = document.getElementById('selectedPosition');
        employeeField.value = ''; // Clear employee field
        employeeField.dispatchEvent(new Event('input')); // Trigger input event
        positionField.disabled = false; // Enable position field
    });

    // Remove Position Selection
    document.getElementById('removePosition').addEventListener('click', function () {
        const positionField = document.getElementById('selectedPosition');
        const employeeField = document.getElementById('selectedEmployee');
        positionField.value = ''; // Clear position field
        positionField.dispatchEvent(new Event('input')); // Trigger input event
        employeeField.disabled = false; // Enable employee field
    });
</script>
<?php include "../super_admin/footer.php"; ?>