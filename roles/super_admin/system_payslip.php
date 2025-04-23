<?php
// filepath: c:\xampp\htdocs\sda_cmsV1\roles\super_admin\system_payslip.php

// Include necessary files
include "../super_admin/user_func.php";
include "../super_admin/header.php";
include "system_func.php";
include "../super_admin/bonus_func.php";

// --- Filter and Data Retrieval ---

// Get filter parameters from the URL, providing default values if not set
$search = $_GET['search'] ?? '';
$position_id = $_GET['position'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$sort = $_GET['sort'] ?? 'employee_id';
$order = $_GET['order'] ?? 'asc';

// Validate the sort column to prevent SQL injection and errors
$validSorts = ['employee_id', 'fname', 'lname', 'position_id', 'net_pay'];
$sort = in_array($sort, $validSorts) ? $sort : 'employee_id';
$order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

// Get all available positions for the filter dropdown
$positions = getAllPositions();

// --- Database Query Preparation ---

// Build the base SQL query to select active users
$query = "SELECT * FROM users WHERE status = 'active'";
$params = [];
$types = '';

// Apply filters to the query
if (!empty($search)) {
    $query .= " AND (fname LIKE ? OR lname LIKE ? OR employee_id LIKE ?)";
    $searchTerm = "%$search%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
}

if (!empty($position_id) && $position_id != 'all') {
    $query .= " AND position_id = ?";
    array_push($params, $position_id);
}

// Add sorting to the query
$query .= " ORDER BY $sort $order";

// --- Execute Query and Fetch Employees ---

$stmt = $conn->prepare($query);
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
}
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Payroll Calculation ---

$payrollData = [];
foreach ($employees as $employee) {
    $data = calculateEmployeePayroll($employee['id'], $start_date, $end_date);
    if ($data) {
        $payrollData[] = $data;
    }
}

// --- Calculate Summary Totals ---

$totalGross = array_sum(array_column($payrollData, 'gross_salary'));
$totalDeductions = array_sum(array_column($payrollData, 'total_deductions'));
$totalNet = array_sum(array_column($payrollData, 'net_pay'));
$totalHours = array_sum(array_column($payrollData, 'total_hours'));

// --- Updated Payroll Calculation Function with total_hours ---

function calculateEmployeePayroll($user_id, $start_date_input, $end_date_input) {
    global $conn;

    // Date range setup
    $start_datetime = date('Y-m-d 00:00:00', strtotime($start_date_input));
    $end_datetime = date('Y-m-d 23:59:59', strtotime($end_date_input));

    // Get user data
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return null;

    // Get position data
    $position_stmt = $conn->prepare("SELECT * FROM positions WHERE id = ?");
    $position_stmt->execute([$user['position_id']]);
    $position = $position_stmt->fetch(PDO::FETCH_ASSOC);
    $hourly_rate = $position['salary'] ?? 0;

    // Total hours from time_logs
    $hours_stmt = $conn->prepare("
        SELECT SUM(total_hours) AS total_hours 
        FROM time_logs 
        WHERE employee_id = ?
        AND check_in BETWEEN ? AND ?
    ");
    $hours_stmt->execute([$user['employee_id'], $start_datetime, $end_datetime]);
    $hours_data = $hours_stmt->fetch(PDO::FETCH_ASSOC);

    $total_hours = (float)($hours_data['total_hours'] ?? 0);

    // Hours calculations
    $work_days_in_period = 22;
    $regular_hours_limit = 8 * $work_days_in_period;
    $regular_hours = min($total_hours, $regular_hours_limit);
    $overtime_hours = max($total_hours - $regular_hours_limit, 0);

    // Salary calculations
    $regular_earnings = $regular_hours * $hourly_rate;
    $overtime_earnings = $overtime_hours * ($hourly_rate * 1.5);
    $gross_salary = $regular_earnings + $overtime_earnings;

    // Deductions
    $deductions_stmt = $conn->prepare("
        SELECT d.deduction_name, d.amount 
        FROM deductions d
        INNER JOIN position_deductions pd ON d.id = pd.deduction_id
        WHERE pd.position_id = ?
    ");
    $deductions_stmt->execute([$user['position_id']]);
    $deductions_list = $deductions_stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_deductions = array_sum(array_column($deductions_list, 'amount'));

    // Bonuses
    $bonus_stmt = $conn->prepare("
        SELECT SUM(amount) as total_bonus 
        FROM bonus 
        WHERE employee_id = ?
        AND start_period <= ? 
        AND end_period >= ?
    ");
    $bonus_stmt->execute([$user['employee_id'], $end_date_input, $start_date_input]);
    $bonus = $bonus_stmt->fetch(PDO::FETCH_ASSOC);
    $total_bonus = $bonus['total_bonus'] ?? 0;

    // Net pay
    $net_pay = ($gross_salary + $total_bonus) - $total_deductions;

    return [
        'id' => $user['id'],
        'employee_id' => $user['employee_id'],
        'full_name' => "{$user['fname']} {$user['lname']}",
        'position_name' => $position['position_name'] ?? 'Not Assigned',
        'total_hours' => $total_hours,
        'regular_hours' => $regular_hours,
        'overtime_hours' => $overtime_hours,
        'hourly_rate' => $hourly_rate,
        'gross_salary' => $gross_salary,
        'total_bonus' => $total_bonus,
        'total_deductions' => $total_deductions,
        'net_pay' => $net_pay,
        'deductions' => $deductions_list,
    ];
}
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fas fa-file-invoice-dollar"></i> Payroll Management</h2>

    <!-- Filter Card -->
    <div class="card shadow-lg mb-4">
        <div class="card-body">
            <form method="get" action="" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label for="search" class="visually-hidden">Search</label>
                    <input type="text" name="search" id="search" class="form-control"
                           placeholder="Search Name or ID..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label for="position" class="visually-hidden">Position</label>
                    <select name="position" id="position" class="form-select">
                        <option value="all" <?= ($position_id == 'all' || $position_id == '') ? 'selected' : '' ?>>All Positions</option>
                        <?php foreach ($positions as $pos): ?>
                        <option value="<?= $pos['id'] ?>" <?= ($position_id == $pos['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pos['position_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="visually-hidden">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" id="start_date" class="form-control"
                               value="<?= htmlspecialchars($start_date) ?>" title="Start Date">
                        <span class="input-group-text">to</span>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                               value="<?= htmlspecialchars($end_date) ?>" title="End Date">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="export_payroll.php?<?= http_build_query($_GET) ?>"
                      class="btn btn-success w-100" target="_blank">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Pay Period</th>
                            <th>Total Hours</th>
                            <th>Gross Pay (Salary)</th>
                            <th>Bonus</th>
                            <th>Deductions</th>
                            <th class="table-info">Net Pay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payrollData)): ?>
                            <tr>
                                <td colspan="9" class="text-center alert alert-warning">No payroll records found for the selected criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payrollData as $payroll): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($payroll['full_name'] ?? 'N/A') ?> <br>
                                    <small class="text-muted"><?= htmlspecialchars($payroll['employee_id'] ?? 'N/A') ?></small>
                                </td>
                                <td><?= htmlspecialchars($payroll['position_name'] ?? 'Not assigned') ?></td>
                                <td><?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?></td>
                                <td class="text-end"><?= number_format($payroll['total_hours'] ?? 0, 2) ?></td>
                                <td class="text-end">₱<?= number_format($payroll['gross_salary'] ?? 0, 2) ?></td>
                                <td class="text-end">₱<?= number_format($payroll['total_bonus'] ?? 0, 2) ?></td>
                                <td class="text-end">₱<?= number_format($payroll['total_deductions'] ?? 0, 2) ?></td>
                                <td class="fw-bold text-end table-info">₱<?= number_format($payroll['net_pay'] ?? 0, 2) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1" title="View Payslip Details"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payslipModal"
                                            data-payroll='<?= htmlspecialchars(json_encode($payroll), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-success" title="Print Payslip"
                                            onclick="printPayslip(<?= $payroll['id'] ?? 0 ?>, '<?= $start_date ?>', '<?= $end_date ?>')">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include the Payslip Modal -->
<?php include "../components/payslip_modal.php"; ?>

<?php include "../super_admin/footer.php"; ?>