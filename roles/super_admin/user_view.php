<?php
require_once "../super_admin/db.php"; // Update with your DB connection


function num2words($num) {
    $ones = array(0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 
                 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 
                 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 
                 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen');
    $tens = array(2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 
                 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety');
    
    $num = number_format($num, 2, '.', '');
    list($whole, $fraction) = explode('.', $num);
    
    $words = array();
    if ($whole >= 1000) {
        $words[] = num2words(floor($whole / 1000)) . ' thousand';
        $whole %= 1000;
    }
    if ($whole >= 100) {
        $words[] = $ones[floor($whole / 100)] . ' hundred';
        $whole %= 100;
    }
    if ($whole > 0) {
        if ($whole < 20) {
            $words[] = $ones[$whole];
        } else {
            $words[] = $tens[floor($whole / 10)];
            $remainder = $whole % 10;
            if ($remainder > 0) $words[] = $ones[$remainder];
        }
    }
    $result = implode(' ', $words);
    if ($fraction != '00') $result .= ' and ' . $fraction . '/100';
    return $result;
}

// ======================
// AJAX Handler
// ======================
if (isset($_GET['action']) && $_GET['action'] == 'get_payslip') {
    header('Content-Type: application/json');
    
    $employee_id = $_GET['employee_id'];
    $month = $_GET['month'];
    $year = $_GET['year'];
    
    // Date calculations
    $month_start = date("$year-$month-01");
    $month_end = date("$year-$month-t");
    
    // Get user data
    $user = getUserDetails($_GET['user_id']);
    $position = getPositionById($user['position_id']);
    $hourly_rate = $position['hourly_rate'] ?? 0;

    // Get daily hours
    $stmt = $conn->prepare("
        SELECT DATE(check_in) as work_date, total_hours,
               am_time_in, am_time_out, pm_time_in, pm_time_out,
               ot_time_in, ot_time_out 
        FROM time_logs 
        WHERE employee_id = ? AND check_in BETWEEN ? AND ?
        ORDER BY check_in
    ");
    $stmt->execute([$employee_id, $month_start, $month_end]);
    $daily_hours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format daily logs
    $formatted_logs = [];
    foreach ($daily_hours as $log) {
        $formatted_logs[] = [
            'work_date' => date('M j, Y', strtotime($log['work_date'])),
            'am_in' => !empty($log['am_time_in']) ? date('h:i A', strtotime($log['am_time_in'])) : '-',
            'am_out' => !empty($log['am_time_out']) ? date('h:i A', strtotime($log['am_time_out'])) : '-',
            'pm_in' => !empty($log['pm_time_in']) ? date('h:i A', strtotime($log['pm_time_in'])) : '-',
            'pm_out' => !empty($log['pm_time_out']) ? date('h:i A', strtotime($log['pm_time_out'])) : '-',
            'ot_in' => !empty($log['ot_time_in']) ? date('h:i A', strtotime($log['ot_time_in'])) : '-',
            'ot_out' => !empty($log['ot_time_out']) ? date('h:i A', strtotime($log['ot_time_out'])) : '-',
            'daily_hours' => (float)$log['total_hours']
        ];
    }

    // Calculate totals
    $total_hours = array_sum(array_column($daily_hours, 'total_hours'));
    $regular_hours_limit = 8 * 22;
    $regular_hours = min($total_hours, $regular_hours_limit);
    $overtime_hours = max($total_hours - $regular_hours_limit, 0);
    $regular_earnings = $regular_hours * $hourly_rate;
    $overtime_earnings = $overtime_hours * ($hourly_rate * 1.5);
    $gross_salary = $regular_earnings + $overtime_earnings;

    // Get deductions and bonuses
    $deductions = getDeductionsByPosition($user['position_id']);
    $total_deductions = array_sum(array_column($deductions, 'amount'));
    
    $bonus_stmt = $conn->prepare("SELECT SUM(amount) as total_bonus FROM bonus 
                                WHERE employee_id = ? AND start_period <= ? AND end_period >= ?");
    $bonus_stmt->execute([$employee_id, $month_end, $month_start]);
    $bonus = $bonus_stmt->fetch(PDO::FETCH_ASSOC);
    $total_bonus = $bonus['total_bonus'] ?? 0;

    $net_pay = ($gross_salary + $total_bonus) - $total_deductions;

    echo json_encode([
        'daily_hours' => $formatted_logs,
        'regular_hours' => $regular_hours,
        'overtime_hours' => $overtime_hours,
        'regular_earnings' => $regular_earnings,
        'overtime_earnings' => $overtime_earnings,
        'total_bonus' => $total_bonus,
        'deductions' => $deductions,
        'total_deductions' => $total_deductions,
        'net_pay' => $net_pay,
        'net_pay_words' => strtoupper(num2words($net_pay)) . ' PESOS ONLY'
    ]);
    exit;
}

// ======================
// Main Page
// ======================
include "../super_admin/user_func.php";
include "../super_admin/header.php";
include "system_func.php";
include "../super_admin/bonus_func.php";

// Validate user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-warning text-center mt-5'>Invalid user ID!</div>";
    exit;
}

$user_id = $_GET['id'];
$user = getUserDetails($user_id);
if (!$user) {
    echo "<div class='alert alert-danger text-center mt-5'>User not found!</div>";
    exit;
}

// Define color scheme
$insuranceBlue = '#2A3F54';
$professionalTeal = '#1ABC9C';
$trustworthyNavy = '#0F1C2D';
$accentSky = '#3498DB';
$textPrimary = '#4A6572';

// Format personal information
$formatted_birthday = !empty($user['birthday']) ? date("F d, Y", strtotime($user['birthday'])) : "N/A";
$age = !empty($user['birthday']) ? (new DateTime())->diff(new DateTime($user['birthday']))->y : "N/A";
$full_name = "{$user['fname']} " . (!empty($user['mname']) ? "{$user['mname']} " : "") . "{$user['lname']} " . (!empty($user['suffix']) ? "{$user['suffix']}" : "");
$address = "{$user['brgy']}, {$user['city_municipality']}, {$user['province']}, {$user['country']}";
$profile_picture = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://www.w3schools.com/howto/img_avatar.png';
$position_name = !empty($user['position_name']) ? htmlspecialchars($user['position_name']) : "Not Assigned";

// Payslip calculations
$position_id = $user['position_id'];
$position = getPositionById($position_id);
$hourly_rate = $position['hourly_rate'] ?? 0;
$current_period = date('Y-m-01') . " to " . date('Y-m-t');

// Calculate working hours
$hours_stmt = $conn->prepare("
    SELECT 
        SUM(
            TIMESTAMPDIFF(SECOND, am_time_in, am_time_out) +
            TIMESTAMPDIFF(SECOND, pm_time_in, pm_time_out) +
            TIMESTAMPDIFF(SECOND, ot_time_in, ot_time_out)
        ) AS total_seconds 
    FROM time_logs 
    WHERE employee_id = ? 
    AND check_in BETWEEN ? AND ?
");

$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$hours_stmt->execute([$user['employee_id'], $month_start, $month_end]);
$hours_data = $hours_stmt->fetch(PDO::FETCH_ASSOC);

$total_seconds = $hours_data['total_seconds'] ?? 0;
$total_hours = round($total_seconds / 3600, 2);

// Calculate earnings
$regular_hours_limit = 8 * 22;
$regular_hours = min($total_hours, $regular_hours_limit);
$overtime_hours = max($total_hours - $regular_hours_limit, 0);
$regular_earnings = $regular_hours * $hourly_rate;
$overtime_earnings = $overtime_hours * ($hourly_rate * 1.5);
$gross_salary = $regular_earnings + $overtime_earnings;

// Get deductions and bonuses
$deductions = $position_id ? getDeductionsByPosition($position_id) : [];
$total_deductions = array_sum(array_column($deductions, 'amount'));

$bonus_stmt = $conn->prepare("SELECT SUM(amount) as total_bonus FROM bonus 
                            WHERE employee_id = ? AND start_period <= ? AND end_period >= ?");
$bonus_stmt->execute([$user['employee_id'], date('Y-m-t'), date('Y-m-01')]);
$bonus = $bonus_stmt->fetch(PDO::FETCH_ASSOC);
$total_bonus = $bonus['total_bonus'] ?? 0;

$net_pay = ($gross_salary + $total_bonus) - $total_deductions;

?>

<style>
    body {
        background-color: #f8f9fa;
        color: <?= $textPrimary ?>;
    }
    
    .profile-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 3px solid <?= $professionalTeal ?>;
    }
    
    .info-card {
        margin-bottom: 10px;
        border-left: 3px solid <?= $professionalTeal ?>;
        padding: 10px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 3px;
    }
    
    .info-value {
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .section-title {
        font-size: 1.1rem;
        color: <?= $insuranceBlue ?>;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    
    .quick-link {
        font-size: 0.9rem;
        padding: 5px 0;
        color: <?= $insuranceBlue ?>;
        text-decoration: none;
        display: block;
    }
    
    .quick-link:hover {
        color: <?= $professionalTeal ?>;
    }
    
    .action-btn {
        margin-right: 5px;
        margin-bottom: 5px;
    }
    
    .card-header-custom {
        background: <?= $insuranceBlue ?>;
        color: white;
        padding: 12px 15px;
    }
    
    .badge-status {
        font-size: 0.8rem;
        padding: 5px 8px;
    }
</style>

<div class="container py-3">
    <div class="row">
        <!-- Left Panel (Profile Card) -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
                <div class="card-body text-center p-4">
                    <img src="<?= $profile_picture ?>" 
                         class="rounded-circle profile-img mb-3 shadow">
                    
                    <h5 class="fw-bold mb-1" style="color: <?= $insuranceBlue ?>;"><?= htmlspecialchars($full_name) ?></h5>
                    <span class="badge mb-3" style="background: <?= $professionalTeal ?>; color: white;">
                        <?= $position_name ?>
                    </span>
                    
                    <div class="d-grid gap-2 mb-3">
                        <a href="user_update.php?id=<?= $user['id'] ?>" class="btn btn-sm" style="background: <?= $insuranceBlue ?>; color: white;">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                    
                    <div class="border-top pt-3">
                        <h6 class="text-muted mb-3">Quick Actions</h6>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-sm btn-outline-primary text-start" data-bs-toggle="modal" data-bs-target="#cardTypeModal">
                                <i class="fas fa-id-card me-2"></i>View ID Card
                            </button>
                            <button class="btn btn-sm btn-outline-success text-start">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </button>
                            <button class="btn btn-sm btn-outline-warning text-start" data-bs-toggle="modal" data-bs-target="#payslipModal">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Pay Slip
                            </button>
                            <button class="btn btn-sm btn-outline-info text-start" onclick="exportUserInfoAsImage()">
                                <i class="fas fa-download me-2"></i>Export Info
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel (User Info) -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                <div class="card-header-custom" style="border-radius: 10px 10px 0 0 !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Employee Information</h5>
                        <small><?= date('F j, Y') ?></small>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Personal Information -->
                    <h6 class="section-title"><i class="fas fa-user-circle me-2"></i>Personal Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-id-card me-2"></i>Employee ID</div>
                                <div class="info-value"><?= htmlspecialchars($user['employee_id']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user-tag me-2"></i>Username</div>
                                <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-birthday-cake me-2"></i>Birthday</div>
                                <div class="info-value"><?= $formatted_birthday ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-hourglass-half me-2"></i>Age</div>
                                <div class="info-value"><?= $age ?></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user me-2"></i>Full Name</div>
                                <div class="info-value"><?= htmlspecialchars($full_name) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-info-circle me-2"></i>Status</div>
                                <div class="info-value">
                                    <span class="badge badge-status bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-briefcase me-2"></i>Position</div>
                                <div class="info-value"><?= $position_name ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h6 class="section-title mt-4"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-envelope me-2"></i>Email</div>
                                <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-phone me-2"></i>Phone</div>
                                <div class="info-value"><?= htmlspecialchars($user['phone']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <h6 class="section-title mt-4"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-home me-2"></i>Complete Address</div>
                                <div class="info-value"><?= htmlspecialchars($address) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer" style="background: #f8f9fa; border-radius: 0 0 10px 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Profile last updated: <?= date('F j, Y') ?></small>
                        <small class="text-muted" style="color: <?= $insuranceBlue ?>;"><?= htmlspecialchars($user['fname']) ?>'s Profile</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: <?= $insuranceBlue ?>; color: white;">
                <h5 class="modal-title">Employee Payslip</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Month and Year Selection -->
                <form id="payslipFilterForm" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="filterMonth" class="form-label">Select Month</label>
                            <select id="filterMonth" name="month" class="form-select">
                                <?php
                                $currentMonth = date('m');
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                    $selected = ($m == $currentMonth) ? 'selected' : '';
                                    echo "<option value='$m' $selected>$monthName</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filterYear" class="form-label">Select Year</label>
                            <select id="filterYear" name="year" class="form-select">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= ($currentYear - 10); $y--) {
                                    $selected = ($y == $currentYear) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-primary" onclick="filterPayslipRecords()">Filter</button>
                    </div>
                </form>

                <div class="payslip-container p-4" id="payslipContainer">
                    <!-- Payslip content will be dynamically updated based on the selected month and year -->
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Company Name</h3>
                        <p class="mb-0">123 Business Street, City, Country</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Employee:</strong> <?= htmlspecialchars($full_name) ?></p>
                            <p class="mb-1"><strong>ID:</strong> <?= htmlspecialchars($user['employee_id']) ?></p>
                            <p class="mb-1"><strong>Position:</strong> <?= $position_name ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>Pay Period:</strong> <span id="payPeriod"><?= htmlspecialchars($current_period) ?></span></p>
                            <p class="mb-1"><strong>Issue Date:</strong> <?= date('F j, Y') ?></p>
                        </div>
                    </div>

                    <div id="payslipDetails">
                        <!-- Earnings and Deductions will be dynamically updated -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Earnings</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Hourly Rate</td>
                                                <td class="text-end">₱<?= number_format($hourly_rate, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>Regular Hours (<?= $regular_hours ?> hrs)</td>
                                                <td class="text-end">₱<?= number_format($regular_earnings, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>Overtime Hours (<?= $overtime_hours ?> hrs)</td>
                                                <td class="text-end">₱<?= number_format($overtime_earnings, 2) ?></td>
                                            </tr>
                                            <tr>
                                                <td>Bonus</td>
                                                <td class="text-end">₱<?= number_format($total_bonus, 2) ?></td>
                                            </tr>
                                            <tr class="table-active">
                                                <th>Total Earnings</th>
                                                <th class="text-end">₱<?= number_format($gross_salary + $total_bonus, 2) ?></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">Deductions</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <?php foreach ($deductions as $deduction): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($deduction['deduction_name']) ?></td>
                                                <td class="text-end">₱<?= number_format($deduction['amount'], 2) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-active">
                                                <th>Total Deductions</th>
                                                <th class="text-end">₱<?= number_format($total_deductions, 2) ?></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Net Pay</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="fw-bold">₱<?= number_format($net_pay, 2) ?></h2>
                                <p class="mb-0"><?= strtoupper(num2words($net_pay)) ?> PESOS ONLY</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" onclick="printPayslip()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ID Card Modal -->
<div class="modal fade" id="cardTypeModal" tabindex="-1" aria-labelledby="cardTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <div class="modal-header" style="background: <?= $insuranceBlue ?>; color: white;">
                <h5 class="modal-title">Employee ID Card</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="employeeIDCardContainer" class="p-4 border rounded shadow-lg" style="background: white; width: 350px; margin: auto;">
                    <img src="<?= $profile_picture ?>" class="rounded-circle mb-3 shadow" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid <?= $professionalTeal ?>;">
                    <h5 class="fw-bold" style="color: <?= $insuranceBlue ?>;"><?= htmlspecialchars($full_name) ?></h5>
                    <p class="text-muted"><i class="fas fa-id-badge me-2"></i> Employee ID: <?= htmlspecialchars($user['employee_id']) ?></p>
                    <p><i class="fas fa-briefcase me-2"></i> Position: <?= htmlspecialchars($position_name) ?></p>
                    <p><i class="fas fa-envelope me-2"></i> Email: <?= htmlspecialchars($user['email']) ?></p>
                    <p><i class="fas fa-phone-alt me-2"></i> Phone: <?= htmlspecialchars($user['phone']) ?></p>
                </div>
                <button class="btn btn-success mt-3" onclick="exportIDCardAsImage()" style="background: <?= $professionalTeal ?>; border-color: <?= $professionalTeal ?>;">
                    <i class="fas fa-download me-2"></i> Download ID as PNG
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
function exportUserInfoAsImage() {
    let userInfoElement = document.querySelector('.card.shadow-sm.border-0');
    html2canvas(userInfoElement, { scale: 2 }).then(canvas => {
        let link = document.createElement('a');
        link.download = 'User_Info_<?= $user["username"] ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}

function exportIDCardAsImage() {
    let cardElement = document.getElementById('employeeIDCardContainer');
    html2canvas(cardElement, { scale: 2 }).then(canvas => {
        let link = document.createElement('a');
        link.download = 'Employee_ID_<?= $user["username"] ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}

function printPayslip() {
    const printWindow = window.open('', '_blank');
    const content = document.getElementById('payslipContainer').innerHTML;
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Payslip - <?= $full_name ?></title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    .payslip-container { max-width: 800px; margin: 0 auto; }
                    .table { margin-bottom: 0; }
                    .card-header { padding: 0.5rem 1rem; }
                </style>
            </head>
            <body>
                ${content}
                <script>
                    window.print();
                    window.onafterprint = function() { window.close(); };
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

function filterPayslipRecords() {
    const month = document.getElementById('filterMonth').value;
    const year = document.getElementById('filterYear').value;

    // Update the pay period display
    const payPeriod = new Date(year, month - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
    document.getElementById('payPeriod').textContent = `${payPeriod}`;

    // Fetch and update payslip details dynamically (AJAX or API call can be implemented here)
    // Example: Fetch data from the server and update the modal content
    console.log(`Fetching records for ${month}/${year}`);
    // You can implement an AJAX request here to fetch the data for the selected month and year
}
</script>

<?php include "../super_admin/footer.php"; ?>