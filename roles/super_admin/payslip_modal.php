<?php
// payslip_modal.php
if (!isset($_SESSION)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_payslip') {
    header('Content-Type: application/json');
    
    try {
        // Validate input parameters
        $required = ['employee_id', 'start_date', 'end_date'];
        foreach ($required as $param) {
            if (!isset($_GET[$param]) || empty($_GET[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }

        $employee_id = $_GET['employee_id'];
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];

        // Database connection
        require_once '../super_admin/db_conn.php'; // Update with your DB connection file

        // Get employee basic info
        $stmt = $conn->prepare("SELECT * FROM users WHERE employee_id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            throw new Exception("Employee not found");
        }

        // Get position info
        $stmt = $conn->prepare("SELECT * FROM positions WHERE id = ?");
        $stmt->execute([$employee['position_id']]);
        $position = $stmt->fetch(PDO::FETCH_ASSOC);
        $hourly_rate = $position['hourly_rate'] ?? 0;

        // Calculate hours worked
        $stmt = $conn->prepare("
            SELECT SUM(total_hours) AS total_hours 
            FROM time_logs 
            WHERE employee_id = ?
            AND check_in BETWEEN ? AND ?
        ");
        $stmt->execute([$employee_id, $start_date, $end_date]);
        $hours_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_hours = (float)($hours_data['total_hours'] ?? 0);

        // Calculate pay components
        $regular_hours_limit = 8 * 22; // 8 hours/day * 22 working days
        $regular_hours = min($total_hours, $regular_hours_limit);
        $overtime_hours = max($total_hours - $regular_hours_limit, 0);
        
        $regular_earnings = $regular_hours * $hourly_rate;
        $overtime_earnings = $overtime_hours * ($hourly_rate * 1.5);
        $gross_salary = $regular_earnings + $overtime_earnings;

        // Get deductions
        $stmt = $conn->prepare("
            SELECT d.deduction_name, d.amount 
            FROM deductions d
            JOIN position_deductions pd ON d.id = pd.deduction_id
            WHERE pd.position_id = ?
        ");
        $stmt->execute([$employee['position_id']]);
        $deductions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_deductions = array_sum(array_column($deductions, 'amount'));

        // Get bonuses
        $stmt = $conn->prepare("
            SELECT SUM(amount) AS total_bonus 
            FROM bonus 
            WHERE employee_id = ?
            AND start_period <= ? 
            AND end_period >= ?
        ");
        $stmt->execute([$employee_id, $end_date, $start_date]);
        $bonus = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_bonus = $bonus['total_bonus'] ?? 0;

        // Calculate net pay
        $net_pay = ($gross_salary + $total_bonus) - $total_deductions;

        // Build response
        echo json_encode([
            'success' => true,
            'data' => [
                'employee_id' => $employee_id,
                'full_name' => htmlspecialchars($employee['fname'].' '.$employee['lname']),
                'position' => htmlspecialchars($position['position_name'] ?? 'Not Assigned'),
                'pay_period' => date('M d, Y', strtotime($start_date)).' - '.date('M d, Y', strtotime($end_date)),
                'total_hours' => $total_hours,
                'regular_hours' => $regular_hours,
                'overtime_hours' => $overtime_hours,
                'hourly_rate' => $hourly_rate,
                'gross_salary' => $gross_salary,
                'total_bonus' => $total_bonus,
                'deductions' => $deductions,
                'total_deductions' => $total_deductions,
                'net_pay' => $net_pay,
                'company_name' => 'Your Company Name',
                'company_address' => '123 Business Street, City, Country'
            ]
        ]);
        exit;

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}
?>

<!-- Modal HTML remains the same as previous answer -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <!-- ... (keep the same modal structure as before) ... -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('payslipModal');
    let currentRequest = null;

    modal.addEventListener('show.bs.modal', function(e) {
        const button = e.relatedTarget;
        const employeeId = button.dataset.employeeId;
        const startDate = button.dataset.startDate;
        const endDate = button.dataset.endDate;
        
        showLoadingState();
        loadPayslip(employeeId, startDate, endDate);
    });

    function showLoadingState() {
        modal.querySelector('.modal-loading').classList.remove('d-none');
        modal.querySelector('.modal-payslip-content').classList.add('d-none');
        modal.querySelector('.modal-error').classList.add('d-none');
    }

    function showContentState() {
        modal.querySelector('.modal-loading').classList.add('d-none');
        modal.querySelector('.modal-payslip-content').classList.remove('d-none');
    }

    function showErrorState(message) {
        modal.querySelector('#error-message').textContent = message || 'Error loading payslip';
        modal.querySelector('.modal-loading').classList.add('d-none');
        modal.querySelector('.modal-payslip-content').classList.add('d-none');
        modal.querySelector('.modal-error').classList.remove('d-none');
    }

    function loadPayslip(employeeId, startDate, endDate) {
        currentRequest = new AbortController();
        
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'get_payslip');
        url.searchParams.set('employee_id', employeeId);
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);

        fetch(url, {
            signal: currentRequest.signal
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.error);
            updateModalContent(data.data);
            showContentState();
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                showErrorState(error.message);
            }
        });
    }

    function updateModalContent(data) {
        const content = `
            <div class="payslip-container">
                <div class="text-center payslip-header mb-4">
                    <div class="payslip-company">${escapeHTML(data.company_name)}</div>
                    <div class="text-muted mb-2">${escapeHTML(data.company_address)}</div>
                    <div class="payslip-title">PAYSLIP</div>
                    <div class="text-muted small">${escapeHTML(data.pay_period)}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light py-2">
                                <strong>Employee Information</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Name:</strong> ${escapeHTML(data.full_name)}</p>
                                <p class="mb-1"><strong>ID:</strong> ${escapeHTML(data.employee_id)}</p>
                                <p class="mb-1"><strong>Position:</strong> ${escapeHTML(data.position)}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light py-2">
                                <strong>Pay Details</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Total Hours:</strong> ${data.total_hours.toFixed(2)}</p>
                                <p class="mb-1"><strong>Regular Hours:</strong> ${data.regular_hours.toFixed(2)}</p>
                                <p class="mb-1"><strong>Overtime Hours:</strong> ${data.overtime_hours.toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white py-2">
                                <strong>Earnings</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Hourly Rate</td>
                                        <td class="text-end">₱${data.hourly_rate.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td>Regular Pay</td>
                                        <td class="text-end">₱${data.regular_earnings.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td>Overtime Pay</td>
                                        <td class="text-end">₱${data.overtime_earnings.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td>Bonus</td>
                                        <td class="text-end">₱${data.total_bonus.toFixed(2)}</td>
                                    </tr>
                                    <tr class="table-success">
                                        <th>Total Earnings</th>
                                        <th class="text-end">₱${(data.gross_salary + data.total_bonus).toFixed(2)}</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-danger text-white py-2">
                                <strong>Deductions</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    ${data.deductions.map(d => `
                                        <tr>
                                            <td>${escapeHTML(d.deduction_name)}</td>
                                            <td class="text-end">₱${parseFloat(d.amount).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                    <tr class="table-danger">
                                        <th>Total Deductions</th>
                                        <th class="text-end">₱${data.total_deductions.toFixed(2)}</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm bg-light mb-4">
                    <div class="card-body text-center py-3">
                        <h5 class="card-title text-uppercase text-muted mb-1">Net Pay</h5>
                        <h2 class="payslip-net-pay mb-0">₱${data.net_pay.toFixed(2)}</h2>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('payslip-modal-body').innerHTML = content;
        document.getElementById('payslipModalLabel').textContent = `Payslip: ${escapeHTML(data.full_name)}`;
    }

    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    window.retryLoadPayslip = function() {
        const button = document.querySelector('[data-bs-target="#payslipModal"]');
        if (button) {
            const employeeId = button.dataset.employeeId;
            const startDate = button.dataset.startDate;
            const endDate = button.dataset.endDate;
            showLoadingState();
            loadPayslip(employeeId, startDate, endDate);
        }
    };
});
</script>