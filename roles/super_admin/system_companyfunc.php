<?php
include "../super_admin/db.php"; // Include the database connection file

function getCompany($id) {
    global $conn; // Use the global $conn variable
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createCompany($data) {
    global $conn; // Use the global $conn variable
    $stmt = $conn->prepare("INSERT INTO companies (name, address, phone) VALUES (?, ?, ?)");
    return $stmt->execute([$data['name'], $data['address'], $data['phone']]);
}

function updateCompany($id, $data) {
    global $conn; // Use the global $conn variable
    $stmt = $conn->prepare("UPDATE companies SET name = ?, address = ?, phone = ? WHERE id = ?");
    return $stmt->execute([$data['name'], $data['address'], $data['phone'], $id]);
}

function deleteCompany($id) {
    global $conn; // Use the global $conn variable
    $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
    return $stmt->execute([$id]);
}

function getCompanyById($id) {
    global $conn; // Ensure you have a valid database connection

    try {
        $sql = "SELECT * FROM companies WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching company by ID: " . $e->getMessage());
        return false;
    }
}


function addCompany($name, $address, $phone) {
    global $conn; // Assuming you're using a database connection

    // Check if the company already exists
    $checkSql = "SELECT COUNT(*) FROM companies WHERE name = ? AND address = ? AND phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$name, $address, $phone]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // Return an error message if the company already exists
        return "A company with the same details already exists.";
    }

    // Insert the new company
    $sql = "INSERT INTO companies (name, address, phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$name, $address, $phone])) {
        return true;
    } else {
        error_log("Database error: " . implode(", ", $stmt->errorInfo()));
        return "Failed to add the company. Please try again.";
    }
}