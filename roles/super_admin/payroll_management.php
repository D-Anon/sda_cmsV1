<?php
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

<!-- [REST OF THE HTML/JAVASCRIPT CODE REMAINS UNCHANGED] -->

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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-coins"></i> Total Gross Pay</h5>
                    <h2 class="display-6 fw-bold">₱<?= number_format($totalGross + array_sum(array_column($payrollData, 'total_bonus')), 2) ?></h2>
                    <small>(Gross Salary + Bonuses)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Total Deductions</h5>
                    <h2 class="display-6 fw-bold">₱<?= number_format($totalDeductions, 2) ?></h2>
                    <small>&nbsp;</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-wallet"></i> Total Net Pay</h5>
                    <h2 class="display-6 fw-bold">₱<?= number_format($totalNet, 2) ?></h2>
                    <small>&nbsp;</small>
                </div>
            </div>
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
                                <td class="text-end"><?= number_format($payroll['total_hours'] ?? 0, 2) ?></td> <!-- Total Hours -->
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

<!-- Enhanced Payslip Modal with Error Handling -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- Loading State -->
            <div class="modal-loading">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Loading Payslip...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Generating payslip details...</p>
                </div>
            </div>

            <!-- Content State (initially hidden) -->
            <div class="modal-payslip-content d-none">
                <div class="modal-header bg-primary text-white sticky-top">
                    <h5 class="modal-title" id="payslipModalLabel"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="payslip-modal-body">
                    <!-- Content will be inserted here by JavaScript -->
                </div>
                <div class="modal-footer bg-light sticky-bottom">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-success" onclick="printModalContent()">
                        <i class="fas fa-print me-1"></i> Print Payslip
                    </button>
                    <button type="button" class="btn btn-primary" onclick="downloadPayslipPDF()">
                        <i class="fas fa-file-pdf me-1"></i> Save as PDF
                    </button>
                </div>
            </div>

            <!-- Error State (initially hidden) -->
            <div class="modal-error d-none">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Error Loading Payslip</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="error-message">Could not load payslip details. Please try again.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="retryLoadPayslip()">
                        <i class="fas fa-sync-alt me-1"></i> Retry
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styling for the payslip */
    .payslip-header {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 1.5rem;
    }
    .payslip-company {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .payslip-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #3498db;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Sleek scrollbar styles */
    .modal-body::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .modal-body {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
        overflow-y: auto;
        max-height: 70vh;
    }
    
    .payslip-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
    }
    .payslip-section-title {
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .payslip-table {
        width: 100%;
    }
    .payslip-table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .payslip-total {
        font-size: 1.1rem;
        font-weight: 700;
    }
    .payslip-net-pay {
        font-size: 1.5rem;
        font-weight: 700;
        color: #27ae60;
    }
    .payslip-signature {
        margin-top: 3rem;
        padding-top: 1rem;
        border-top: 1px dashed #dee2e6;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #payslip-modal-body, #payslip-modal-body * {
            visibility: visible;
        }
        #payslip-modal-body {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }
        .modal-footer {
            display: none !important;
        }
    }
</style>

<script>
// Enhanced JavaScript for the payslip modal with robust error handling
document.addEventListener('DOMContentLoaded', function() {
    const payslipModal = document.getElementById('payslipModal');
    let currentPayrollData = null;
    
    if (payslipModal) {
        payslipModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const payrollJson = button.getAttribute('data-payroll');
            
            // Show loading state
            showModalLoadingState();
            
            try {
                // Validate JSON exists
                if (!payrollJson || payrollJson.trim() === '') {
                    throw new Error("No payroll data provided");
                }
                
                // Parse and validate JSON
                currentPayrollData = JSON.parse(payrollJson);
                
                // Validate payroll data structure
                if (!currentPayrollData || typeof currentPayrollData !== 'object') {
                    throw new Error("Invalid payroll data format");
                }
                
                // Ensure required fields exist
                if (!currentPayrollData.hasOwnProperty('employee_id') || 
                    !currentPayrollData.hasOwnProperty('full_name')) {
                    throw new Error("Incomplete payroll data");
                }
                
                // Load the content if validation passes
                loadPayslipContent(currentPayrollData);
            } catch (e) {
                console.error("Error loading payslip:", e);
                showModalErrorState(e.message || "Error loading payslip details. Please try again.");
            }
        });
        
        // Reset modal when closed
        payslipModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('payslip-modal-body').innerHTML = '';
        });
    }
});

function showModalLoadingState() {
    document.querySelector('.modal-loading').classList.remove('d-none');
    document.querySelector('.modal-payslip-content').classList.add('d-none');
    document.querySelector('.modal-error').classList.add('d-none');
}

function showModalErrorState(message) {
    if (message) {
        document.getElementById('error-message').textContent = message;
    }
    document.querySelector('.modal-loading').classList.add('d-none');
    document.querySelector('.modal-payslip-content').classList.add('d-none');
    document.querySelector('.modal-error').classList.remove('d-none');
}

function showModalContentState() {
    document.querySelector('.modal-loading').classList.add('d-none');
    document.querySelector('.modal-payslip-content').classList.remove('d-none');
    document.querySelector('.modal-error').classList.add('d-none');
}

function retryLoadPayslip() {
    if (currentPayrollData) {
        showModalLoadingState();
        setTimeout(() => {
            loadPayslipContent(currentPayrollData);
        }, 500);
    } else {
        showModalErrorState("No payroll data available to retry");
    }
}

function loadPayslipContent(payroll) {
    try {
        // Create a safe payroll object with default values
        const safePayroll = {
            id: payroll.id || 0,
            employee_id: payroll.employee_id || 'N/A',
            full_name: payroll.full_name || 'Unknown Employee',
            position_name: payroll.position_name || 'Not Assigned',
            total_hours: parseFloat(payroll.total_hours) || 0,
            regular_hours: parseFloat(payroll.regular_hours) || 0,
            overtime_hours: parseFloat(payroll.overtime_hours) || 0,
            hourly_rate: parseFloat(payroll.hourly_rate) || 0,
            gross_salary: parseFloat(payroll.gross_salary) || 0,
            total_bonus: parseFloat(payroll.total_bonus) || 0,
            total_deductions: parseFloat(payroll.total_deductions) || 0,
            net_pay: parseFloat(payroll.net_pay) || 0,
            deductions: Array.isArray(payroll.deductions) ? payroll.deductions : []
        };

        const startDate = '<?= $start_date ?>';
        const endDate = '<?= $end_date ?>';
        
        // Format dates
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const formattedStartDate = new Date(startDate + 'T00:00:00').toLocaleDateString('en-US', options);
        const formattedEndDate = new Date(endDate + 'T00:00:00').toLocaleDateString('en-US', options);
        const formattedProcessDate = new Date().toLocaleDateString('en-US', options);
        
        // Set modal title
        document.getElementById('payslipModalLabel').textContent = `Payslip: ${safePayroll.full_name}`;
        
        // Build payslip content with proper escaping
        const content = `
            <div class="payslip-container">
                <!-- Company Header -->
                <div class="text-center payslip-header mb-4">
                    <div class="payslip-company">${htmlspecialchars('Your Company Name')}</div>
                    <div class="text-muted mb-2">123 Business Address, City, Country</div>
                    <div class="payslip-title">PAYSLIP</div>
                    <div class="text-muted small">${formattedStartDate} - ${formattedEndDate}</div>
                </div>
                
                <!-- Employee and Pay Period Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light py-2">
                                <strong>Employee Information</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Name:</strong> ${htmlspecialchars(safePayroll.full_name)}</p>
                                <p class="mb-1"><strong>Employee ID:</strong> ${htmlspecialchars(safePayroll.employee_id)}</p>
                                <p class="mb-1"><strong>Position:</strong> ${htmlspecialchars(safePayroll.position_name)}</p>
                                <p class="mb-0"><strong>Pay Date:</strong> ${formattedProcessDate}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light py-2">
                                <strong>Pay Period Details</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Pay Period:</strong> ${formattedStartDate} - ${formattedEndDate}</p>
                                <p class="mb-1"><strong>Total Hours Worked:</strong> ${safePayroll.total_hours.toFixed(2)} hrs</p>
                                <p class="mb-1"><strong>Regular Hours:</strong> ${safePayroll.regular_hours.toFixed(2)} hrs</p>
                                <p class="mb-0"><strong>Overtime Hours:</strong> ${safePayroll.overtime_hours.toFixed(2)} hrs</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Earnings and Deductions -->
                <div class="row">
                    <!-- Earnings -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white py-2">
                                <strong><i class="fas fa-coins me-1"></i> Earnings</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm payslip-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Hourly Rate</td>
                                            <td class="text-end">₱${safePayroll.hourly_rate.toFixed(2)}/hr</td>
                                        </tr>
                                        <tr>
                                            <td>Regular Pay (${safePayroll.regular_hours.toFixed(2)} hrs)</td>
                                            <td class="text-end">₱${(safePayroll.regular_hours * safePayroll.hourly_rate).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td>Overtime Pay (${safePayroll.overtime_hours.toFixed(2)} hrs @ 1.5x)</td>
                                            <td class="text-end">₱${(safePayroll.overtime_hours * safePayroll.hourly_rate * 1.5).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td>Bonus</td>
                                            <td class="text-end">₱${safePayroll.total_bonus.toFixed(2)}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <th class="payslip-total">Total Earnings</th>
                                            <th class="text-end payslip-total">₱${(safePayroll.gross_salary + safePayroll.total_bonus).toFixed(2)}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Deductions -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-danger text-white py-2">
                                <strong><i class="fas fa-file-invoice-dollar me-1"></i> Deductions</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm payslip-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${safePayroll.deductions.length > 0 ? 
                                            safePayroll.deductions.map(d => `
                                                <tr>
                                                    <td>${htmlspecialchars(d.deduction_name || 'Unknown Deduction')}</td>
                                                    <td class="text-end">₱${parseFloat(d.amount || 0).toFixed(2)}</td>
                                                </tr>
                                            `).join('') : 
                                            '<tr><td colspan="2" class="text-center text-muted">No deductions</td></tr>'
                                        }
                                        <tr class="table-danger">
                                            <th class="payslip-total">Total Deductions</th>
                                            <th class="text-end payslip-total">₱${safePayroll.total_deductions.toFixed(2)}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Net Pay -->
                <div class="card shadow-sm bg-light mb-4">
                    <div class="card-body text-center py-3">
                        <h5 class="card-title text-uppercase text-muted mb-1">Net Pay</h5>
                        <h2 class="payslip-net-pay mb-0">₱${safePayroll.net_pay.toFixed(2)}</h2>
                        <small class="text-muted">${numberToWords(safePayroll.net_pay)} pesos only</small>
                    </div>
                </div>
                
                <!-- Signature -->
                <div class="row payslip-signature">
                    <div class="col-md-6">
                        <p class="mb-1">_________________________</p>
                        <p class="mb-0"><strong>Employee Signature</strong></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1">_________________________</p>
                        <p class="mb-0"><strong>Authorized Signatory</strong></p>
                    </div>
                    <div class="col-12 text-center mt-2">
                        <small class="text-muted">I acknowledge receipt of this payslip and the amount stated.</small>
                    </div>
                </div>
                
                <!-- Footer Note -->
                <div class="text-center mt-4 text-muted small">
                    <p>This is a computer-generated document. No signature is required.</p>
                </div>
            </div>
        `;
        
        // Insert content
        document.getElementById('payslip-modal-body').innerHTML = content;
        showModalContentState();
        
    } catch (e) {
        console.error("Error generating payslip content:", e);
        showModalErrorState("Error generating payslip content. Please contact support.");
    }
}

function printModalContent() {
    try {
        const content = document.getElementById('payslip-modal-body').innerHTML;
        if (!content) {
            throw new Error("No content available to print");
        }
        
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payslip</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    @page { size: auto; margin: 5mm; }
                    @media print {
                        body { padding: 0; }
                        .payslip-table th { background-color: #f8f9fa !important; }
                    }
                </style>
            </head>
            <body onload="window.print()">
                ${content}
            </body>
            </html>
        `);
        printWindow.document.close();
    } catch (e) {
        console.error("Error printing payslip:", e);
        alert("Error printing payslip: " + e.message);
    }
}

function downloadPayslipPDF() {
    if (!currentPayrollData) {
        alert("No payroll data available to download");
        return;
    }
    // This would require a server-side PDF generation endpoint
    alert("PDF generation would be implemented with a server-side library like TCPDF or Dompdf");
    // Example implementation:
    // window.location.href = `generate_pdf.php?employee_id=${currentPayrollData.id}&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>`;
}

function printPayslip(userId, startDate, endDate) {
    if (!userId) {
        alert('Cannot print: Invalid User ID.');
        return;
    }
    const printUrl = `print_payslip.php?user_id=${userId}&start_date=${startDate}&end_date=${endDate}`;
    window.open(printUrl, '_blank');
}

// Number to words function
function numberToWords(num) {
    const ones = ['','one','two','three','four','five','six','seven','eight','nine','ten',
                  'eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen',
                  'eighteen','nineteen'];
    const tens = ['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
    num = parseFloat(num);
    if (isNaN(num)) return '';
    if (num === 0) return 'zero';
    let result = [];
    let whole = Math.floor(num);
    let fraction = Math.round((num - whole) * 100);
    if (whole >= 1000000) {
        result.push(numberToWords(Math.floor(whole / 1000000)) + ' million');
        whole %= 1000000;
    }
    if (whole >= 1000) {
        result.push(numberToWords(Math.floor(whole / 1000)) + ' thousand');
        whole %= 1000;
    }
    if (whole >= 100) {
        result.push(ones[Math.floor(whole / 100)] + ' hundred');
        whole %= 100;
    }
    if (whole >= 20) {
        result.push(tens[Math.floor(whole / 10)]);
        whole %= 10;
    }
    if (whole > 0) {
        result.push(ones[whole]);
    }
    let output = result.filter(Boolean).join(' ');
    if (fraction > 0) {
        output += ' and ' + fraction + '/100';
    }
    return output;
}

// Basic HTML entity encoding
function htmlspecialchars(str) {
    if (typeof str !== 'string') return str;
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
}
</script>
<?php include "../super_admin/footer.php"; ?>