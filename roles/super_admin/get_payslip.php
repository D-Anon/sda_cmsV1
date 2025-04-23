<?php
require_once "../super_admin/db_conn.php";

if (isset($_GET['employee_id'], $_GET['start_date'], $_GET['end_date'])) {
    $employeeId = $_GET['employee_id'];
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    // Fetch payslip details from the database
    $stmt = $conn->prepare("
        SELECT u.fname, u.lname, u.employee_id, 
               SUM(t.total_hours) AS total_hours, 
               p.salary AS hourly_rate, 
               (SUM(t.total_hours) * p.salary) AS gross_salary,
               (SELECT SUM(amount) FROM deductions WHERE position_id = u.position_id) AS total_deductions,
               ((SUM(t.total_hours) * p.salary) - (SELECT SUM(amount) FROM deductions WHERE position_id = u.position_id)) AS net_pay
        FROM users u
        JOIN time_logs t ON u.employee_id = t.employee_id
        JOIN positions p ON u.position_id = p.id
        WHERE u.employee_id = ? AND t.check_in BETWEEN ? AND ?
        GROUP BY u.employee_id
    ");
    $stmt->execute([$employeeId, $startDate, $endDate]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payslip) {
        echo json_encode([
            'full_name' => $payslip['fname'] . ' ' . $payslip['lname'],
            'employee_id' => $payslip['employee_id'],
            'total_hours' => $payslip['total_hours'],
            'gross_salary' => $payslip['gross_salary'],
            'total_deductions' => $payslip['total_deductions'],
            'net_pay' => $payslip['net_pay'],
        ]);
    } else {
        echo json_encode(['error' => 'Payslip not found.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>