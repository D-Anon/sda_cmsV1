<?php
require_once 'db.php'; // Include database connection

// ========================
// CRUD FUNCTIONS FOR POSITIONS
// ========================

// Create Position
function createPosition($position_name, $description, $salary, $deductions = [], $customDeductions = [])
{
    global $conn;

    try {
        $conn->beginTransaction();

        // Insert the position into the `positions` table
        $sql = "INSERT INTO positions (position_name, description, salary) VALUES (:position_name, :description, :salary)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':position_name' => $position_name,
            ':description' => $description,
            ':salary' => $salary
        ]);

        // Get the ID of the newly created position
        $positionId = $conn->lastInsertId();

        // Insert predefined deductions into the `position_deductions` table
        foreach ($deductions as $deductionId) {
            $sql = "INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':position_id' => $positionId,
                ':deduction_id' => $deductionId
            ]);
        }

        // Insert custom deductions into the `deductions` table and link them in `position_deductions`
        foreach ($customDeductions as $customDeduction) {
            $sql = "INSERT INTO deductions (deduction_name, amount) VALUES (:deduction_name, :amount)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':deduction_name' => $customDeduction['name'],
                ':amount' => $customDeduction['amount']
            ]);

            // Get the ID of the newly created custom deduction
            $deductionId = $conn->lastInsertId();

            // Link the custom deduction to the position in `position_deductions`
            $sql = "INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':position_id' => $positionId,
                ':deduction_id' => $deductionId
            ]);
        }

        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error creating position with deductions: " . $e->getMessage());
        return false;
    }
}

// Update Position
function updatePosition($id, $position_name, $description, $salary)
{
    global $conn;

    try {
        $sql = "UPDATE positions SET position_name = :position_name, description = :description, salary = :salary WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':position_name' => $position_name,
            ':description' => $description,
            ':salary' => $salary,
            ':id' => $id
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating position: " . $e->getMessage());
        return false;
    }
}

// Delete Position
function deletePosition($id)
{
    global $conn;

    try {
        $sql = "DELETE FROM positions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error deleting position: " . $e->getMessage());
        return false;
    }
}

function getPositionById($id)
{
    global $conn;

    try {
        $sql = "SELECT * FROM positions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the position as an associative array
    } catch (PDOException $e) {
        error_log("Error fetching position by ID: " . $e->getMessage());
        return false; // Return false if an error occurs
    }
}

function addPositionDeduction($positionId, $deductionId)
{
    global $conn;

    try {
        $sql = "INSERT INTO position_deductions (position_id, deduction_id) VALUES (:position_id, :deduction_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':position_id' => $positionId,
            ':deduction_id' => $deductionId
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error adding position deduction: " . $e->getMessage());
        return false;
    }
}

// ========================
// CRUD FUNCTIONS FOR DEDUCTIONS
// ========================

// Create Deduction
function createDeduction($deduction_name, $amount, $description)
{
    global $conn;

    try {
        $sql = "INSERT INTO deductions (deduction_name, amount, description) VALUES (:deduction_name, :amount, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':deduction_name' => $deduction_name,
            ':amount' => $amount,
            ':description' => $description
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating deduction: " . $e->getMessage());
        return false;
    }
}

// Read All Deductions
function getAllDeductions()
{
    global $conn;

    try {
        $sql = "SELECT * FROM deductions ORDER BY created_at DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching deductions: " . $e->getMessage());
        return [];
    }
}

// Update Deduction
function updateDeduction($id, $deduction_name, $amount, $description)
{
    global $conn;

    try {
        $sql = "UPDATE deductions SET deduction_name = :deduction_name, amount = :amount, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':deduction_name' => $deduction_name,
            ':amount' => $amount,
            ':description' => $description,
            ':id' => $id
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating deduction: " . $e->getMessage());
        return false;
    }
}

// Delete Deduction
function deleteDeduction($id)
{
    global $conn;

    try {
        $sql = "DELETE FROM deductions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error deleting deduction: " . $e->getMessage());
        return false;
    }
}

function getDeductionById($id)
{
    global $conn;

    try {
        $sql = "SELECT * FROM deductions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the deduction as an associative array
    } catch (PDOException $e) {
        error_log("Error fetching deduction by ID: " . $e->getMessage());
        return false; // Return false if an error occurs
    }
}

function getPaginatedPositions($limit, $offset)
{
    global $conn; // Use the correct database connection variable
    $stmt = $conn->prepare("SELECT * FROM positions LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countAllPositions()
{
    global $conn; // Use the correct database connection variable
    $stmt = $conn->query("SELECT COUNT(*) as total FROM positions");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getPaginatedDeductions($limit, $offset)
{
    global $conn; // Use the correct database connection variable
    $stmt = $conn->prepare("SELECT * FROM deductions LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countAllDeductions()
{
    global $conn; // Use the correct database connection variable
    $stmt = $conn->query("SELECT COUNT(*) as total FROM deductions");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getPositionWithDeductions($positionId)
{
    global $conn;

    try {
        $sql = "SELECT 
                    p.id AS position_id, 
                    p.position_name, 
                    p.description, 
                    p.salary, 
                    d.id AS deduction_id, 
                    d.deduction_name, 
                    d.amount 
                FROM positions p
                LEFT JOIN position_deduction pd ON p.id = pd.position_id
                LEFT JOIN deductions d ON pd.deduction_id = d.id
                WHERE p.id = :position_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':position_id' => $positionId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$results) {
            return false; // No deductions assigned to this position
        }

        $positionData = [
            'position_id' => $results[0]['position_id'],
            'position_name' => $results[0]['position_name'],
            'description' => $results[0]['description'],
            'salary' => $results[0]['salary'],
            'deductions' => []
        ];

        foreach ($results as $row) {
            if (!empty($row['deduction_id'])) {
                $positionData['deductions'][] = [
                    'deduction_id' => $row['deduction_id'],
                    'deduction_name' => $row['deduction_name'],
                    'amount' => $row['amount']
                ];
            }
        }

        return $positionData;
    } catch (PDOException $e) {
        error_log("Error fetching position with deductions: " . $e->getMessage());
        return false;
    }
}


/**
 * Create new user
 * @param array $user_data
 * @return int|bool New user ID or false
 */
function createUser($user_data) {
    global $conn;
    try {
        $sql = "
            INSERT INTO users (
                employee_id, username, password_hash, email, 
                fname, mname, lname, suffix, birthday, phone,
                brgy, city_municipality, province, country,
                position_id, role_id, status, profile_picture
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            $user_data['employee_id'],
            $user_data['username'],
            password_hash($user_data['password'], PASSWORD_DEFAULT),
            $user_data['email'],
            $user_data['fname'],
            $user_data['mname'] ?? null,
            $user_data['lname'],
            $user_data['suffix'] ?? null,
            $user_data['birthday'],
            $user_data['phone'],
            $user_data['brgy'],
            $user_data['city_municipality'],
            $user_data['province'],
            $user_data['country'],
            $user_data['position_id'] ?? null,
            $user_data['role_id'],
            $user_data['status'],
            $user_data['profile_picture'] ?? null
        ]);
        
        return $success ? $conn->lastInsertId() : false;
    } catch(PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Count total number of users
 * @return int Total users
 */
function countAllUsers() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log("Error counting users: " . $e->getMessage());
        return 0;
    }
}

// POSITION/DEDUCTION FUNCTIONS (Would typically be in system_func.php)

/**
 * Get position by ID
 * @param int $position_id
 * @return array|bool Position data or false
 */
// Removed duplicate function declaration to avoid redeclaration error.

/**
 * Get deductions for a position
 * @param int $position_id
 * @return array List of deductions
 */
function getDeductionsByPosition($position_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT d.* 
            FROM position_deductions pd
            JOIN deductions d ON pd.deduction_id = d.id
            WHERE pd.position_id = ?
        ");
        $stmt->execute([$position_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting deductions: " . $e->getMessage());
        return [];
    }
}

function getAllPositions() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.position_name,
                p.hourly_rate,
                p.description,
                COUNT(u.id) AS total_employees
            FROM positions p
            LEFT JOIN users u ON p.id = u.position_id
            GROUP BY p.id
            ORDER BY p.position_name ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error fetching positions: " . $e->getMessage());
        return array();
    }
}