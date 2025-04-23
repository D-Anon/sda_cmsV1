<?php
include "../super_admin/db.php"; // Ensure the correct path to the database connection file

function getEmployees() {
    global $conn;
    // Use the correct column name (e.g., employee_id instead of employee_code)
    $query = "SELECT employee_id, fname, mname, lname, suffix FROM users";
    $stmt = $conn->prepare($query); // Use prepare() for PDO
    $stmt->execute(); // Execute the prepared statement
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Use fetchAll() for PDO
}

// function getPositions() {
//     global $conn;
//     $query = "SELECT position_name FROM positions";
//     $stmt = $conn->prepare($query); // Use prepare() for PDO
//     $stmt->execute(); // Execute the prepared statement
//     return $stmt->fetchAll(PDO::FETCH_ASSOC); // Use fetchAll() for PDO
// }

function getBonuses() {
    global $conn;
    $query = "SELECT * FROM bonus";
    $stmt = $conn->prepare($query); // Use prepare() for PDO
    $stmt->execute(); // Execute the prepared statement
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Use fetchAll() for PDO
}

/**
 * Get paginated bonuses from the database
 * 
 * @param int $perPage Number of records per page
 * @param int $offset Offset for pagination
 * @return array Array of bonus records
 */
function getPaginatedBonuses($perPage, $offset) {
    global $conn;
    
    try {
        $query = "SELECT b.*, 
                 CONCAT(u.fname, ' ', COALESCE(u.mname, ''), ' ', u.lname, ' ', COALESCE(u.suffix, '')) as employee_name,
                 u.position as employee_position
                 FROM bonus b
                 LEFT JOIN users u ON b.employee_id = u.employee_id
                 ORDER BY b.id DESC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting paginated bonuses: " . $e->getMessage());
        return [];
    }
}

/**
 * Count all bonuses in the database
 * 
 * @return int Total number of bonuses
 */
function countAllBonuses() {
    global $conn;
    
    try {
        $query = "SELECT COUNT(*) as total FROM bonus";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    } catch (PDOException $e) {
        error_log("Error counting bonuses: " . $e->getMessage());
        return 0;
    }
}

function deleteBonus($conn, $bonusId) {
    try {
        $query = "DELETE FROM bonus WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $bonusId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log the error or handle it as needed
        error_log("Error deleting bonus: " . $e->getMessage());
        return false;
    }
}
?>