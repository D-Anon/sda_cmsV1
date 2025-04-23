<?php
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

// Format birthday and personal info
$formatted_birthday = !empty($user['birthday']) ? date("F d, Y", strtotime($user['birthday'])) : "N/A";
$age = !empty($user['birthday']) ? (new DateTime())->diff(new DateTime($user['birthday']))->y : "N/A";
$full_name = "{$user['fname']} " . (!empty($user['mname']) ? "{$user['mname']} " : "") . "{$user['lname']} " . (!empty($user['suffix']) ? "{$user['suffix']}" : "");
$address = "{$user['brgy']}, {$user['city_municipality']}, {$user['province']}, {$user['country']}";
$profile_picture = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://www.w3schools.com/howto/img_avatar.png';
$position_name = !empty($user['position_name']) ? htmlspecialchars($user['position_name']) : "Not Assigned";

// Get position and hourly rate
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

// Calculate regular and overtime hours (assuming 8hrs/day × 22 working days)
$regular_hours_limit = 8 * 22; // 176 hours
$regular_hours = min($total_hours, $regular_hours_limit);
$overtime_hours = max($total_hours - $regular_hours_limit, 0);

// Calculate earnings
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

// Calculate net pay
$net_pay = ($gross_salary + $total_bonus) - $total_deductions;

// Number to words function
function num2words($num) {
    $ones = array(0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen');
    $tens = array(2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety');
    
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
?>

<div class="container mt-4">
    <div class="row">
        <!-- Left Panel (Card Type) -->
        <div class="col-md-3">
            <div class="card p-3 text-center shadow-lg">
                <h5 class="fw-bold">PROFILE</h5>
                <img src="<?= $profile_picture ?>" 
                     class="rounded-circle border border-primary shadow-lg mt-4 align-self-center mb-4"
                     style="width: 150px; height: 150px; object-fit: cover;">
                <p class="mt-3 fw-bold"><?= htmlspecialchars($full_name) ?></p>
                <p class="text-muted"><?= $position_name ?></p>
            </div>
        </div>

        <!-- Right Panel (User Info) -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="user_update.php?id=<?= $user['id'] ?>" class="btn btn-outline-dark me-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#cardTypeModal">
                        <i class="fas fa-id-card"></i> View ID Card
                    </button>
                    <button class="btn btn-outline-warning me-2">
                        <i class="fas fa-envelope"></i> Send Email
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payslipModal">
                        <i class="fas fa-file-invoice-dollar"></i> Pay Slip
                    </button>
                </div>
                <button class="btn btn-success" onclick="exportUserInfoAsImage()">
                    <i class="fas fa-download"></i> Export Info
                </button>
            </div>

            <div class="card shadow-lg mt-3 p-4">
                <h4 class="fw-bold text-center">User Information</h4>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="fas fa-id-badge me-2 text-primary"></i> <strong>Employee ID:</strong> <?= htmlspecialchars($user['employee_id']) ?></p>
                        <p><i class="fas fa-user me-2 text-primary"></i> <strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                        <p><i class="fas fa-envelope me-2 text-success"></i> <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><i class="fas fa-phone me-2 text-warning"></i> <strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-birthday-cake me-2 text-warning"></i> <strong>Birthday:</strong> <?= $formatted_birthday ?></p>
                        <p><i class="fas fa-hourglass-half me-2 text-info"></i> <strong>Age:</strong> <?= $age ?></p>
                        <p><i class="fas fa-map-marker-alt me-2 text-danger"></i> <strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
                        <p><i class="fas fa-briefcase me-2 text-secondary"></i> <strong>Position:</strong> <?= $position_name ?></p>
                        <p><i class="fas fa-toggle-on me-2 text-success"></i> <strong>Status:</strong> 
                            <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Updated Payslip Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Employee Payslip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="payslip-container p-4" id="payslipContainer">
                    <!-- Company Header -->
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Company Name</h3>
                        <p class="mb-0">123 Business Street, City, Country</p>
                    </div>
                    
                    <!-- Employee and Period Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Employee:</strong> <?= htmlspecialchars($full_name) ?></p>
                            <p class="mb-1"><strong>ID:</strong> <?= htmlspecialchars($user['employee_id']) ?></p>
                            <p class="mb-1"><strong>Position:</strong> <?= $position_name ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>Pay Period:</strong> <?= htmlspecialchars($current_period) ?></p>
                            <p class="mb-1"><strong>Issue Date:</strong> <?= date('F j, Y') ?></p>
                        </div>
                    </div>

                    <!-- Earnings Section -->
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
                        
                        <!-- Deductions Section -->
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

                    <!-- Net Pay Section -->
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
            <div class="modal-footer">
                <button class="btn btn-success" onclick="printPayslip()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Employee ID Modal -->
<div class="modal fade" id="cardTypeModal" tabindex="-1" aria-labelledby="cardTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title" id="cardTypeModalLabel">Employee ID Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="employeeIDCardContainer" class="p-3 border rounded shadow-lg bg-white" style="width: 350px; margin: auto;">
                    <img src="<?php echo $profile_picture; ?>" class="rounded-circle mb-3 shadow" style="width: 100px; height: 100px; object-fit: cover;">
                    <h5 class="fw-bold"><?php echo htmlspecialchars($full_name); ?></h5>
                    <p class="text-muted"><i class="fas fa-id-badge"></i> Employee ID: <?php echo htmlspecialchars($user['employee_id']); ?></p>
                    <p><i class="fas fa-briefcase"></i> Position: <?php echo htmlspecialchars($position_name); ?></p>
                    <p><i class="fas fa-envelope"></i> Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><i class="fas fa-phone-alt"></i> Phone: <?php echo htmlspecialchars($user['phone']); ?></p>
                </div>
                <button class="btn btn-success mt-3" onclick="exportIDCardAsImage()">
                    <i class="fas fa-download"></i> Download ID as PNG
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
function exportUserInfoAsImage() {
    let userInfoElement = document.querySelector('.card.shadow-lg.mt-3');
    html2canvas(userInfoElement, { scale: 2 }).then(canvas => {
        let link = document.createElement('a');
        link.download = 'User_Info_<?= $user["username"] ?>.png';
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
</script>

<?php include "../super_admin/footer.php"; ?>