<?php
// Start session
session_start();

// Include database connection
require_once "../../includes/db.php";

// Function to register a new user
function registerUser($username, $password, $email, $full_name, $phone, $address, $role_id) {
    global $conn;

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (username, password, email, full_name, phone, address, role_id) 
                VALUES (:username, :password, :email, :full_name, :phone, :address, :role_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':role_id' => $role_id
        ]);
        return "User registered successfully!";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to get user details
function getUserDetails($user_id) {
    global $conn;

    try {
        $sql = "SELECT id, username, email, full_name, phone, address, role_id FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// Function to update user profile
function updateUser($user_id, $email, $full_name, $phone, $address) {
    global $conn;

    try {
        $sql = "UPDATE users SET email = :email, full_name = :full_name, phone = :phone, address = :address WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to delete a user
function deleteUser($user_id) {
    global $conn;

    try {
        $sql = "DELETE FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':user_id' => $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Function to list all users
function listUsers() {
    global $conn;

    try {
        $sql = "SELECT id, username, email, full_name, phone, address, role_id FROM users";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
