<?php
// Start session
session_start();

// Include database connection
require_once "db.php";

// Function to add a payroll entry
function addPayroll($user_id, $salary, $deductions, $bonuses, $pay_date) {
    global $conn;

    try {
        $net_salary = ($salary + $bonuses) - $deductions; // Calculate net salary

        $sql = "INSERT INTO payroll (user_id, salary, deductions, bonuses, net_salary, pay_date) 
                VALUES (:user_id, :salary, :deductions, :bonuses, :net_salary, :pay_date)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':salary' => $salary,
            ':deductions' => $deductions,
            ':bonuses' => $bonuses,
            ':net_salary' => $net_salary,
            ':pay_date' => $pay_date
        ]);
        return "Payroll entry added successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get payroll details for a specific user
function getPayroll($payroll_id) {
    global $conn;

    try {
        $sql = "SELECT payroll.*, users.full_name AS employee_name FROM payroll 
                JOIN users ON payroll.user_id = users.id 
                WHERE payroll.id = :payroll_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':payroll_id' => $payroll_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// Function to update a payroll entry
function updatePayroll($payroll_id, $salary, $deductions, $bonuses, $pay_date) {
    global $conn;

    try {
        $net_salary = ($salary + $bonuses) - $deductions; // Recalculate net salary

        $sql = "UPDATE payroll SET salary = :salary, deductions = :deductions, bonuses = :bonuses, 
                net_salary = :net_salary, pay_date = :pay_date WHERE id = :payroll_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':salary' => $salary,
            ':deductions' => $deductions,
            ':bonuses' => $bonuses,
            ':net_salary' => $net_salary,
            ':pay_date' => $pay_date,
            ':payroll_id' => $payroll_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to delete a payroll entry
function deletePayroll($payroll_id) {
    global $conn;

    try {
        $sql = "DELETE FROM payroll WHERE id = :payroll_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':payroll_id' => $payroll_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to list all payroll entries
function listPayrolls() {
    global $conn;

    try {
        $sql = "SELECT payroll.*, users.full_name AS employee_name FROM payroll 
                JOIN users ON payroll.user_id = users.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
