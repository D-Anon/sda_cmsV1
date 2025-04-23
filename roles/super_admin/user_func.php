<?php
// Start session
session_start();

// Include database connection
require "../super_admin/db.php";

// Function to register a new user
function registerUser($employee_id, $username, $password, $email, $fname, $mname, $lname, $suffix, $birthday, $phone, $country, $province, $city_municipality, $brgy, $position_id, $role_id, $profile_picture, $company_id) {
    global $conn;

    try {
        // Check if username or email already exists
        $checkSql = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([':username' => $username, ':email' => $email]);
        if ($checkStmt->fetchColumn() > 0) {
            return "Username or Email already exists.";
        }

        // Hash the password using SHA-256
        $hashed_password = hash('sha256', $password);

        $sql = "INSERT INTO users (employee_id, username, password, email, fname, mname, lname, suffix, birthday, phone, country, province, city_municipality, brgy, position_id, role_id, profile_picture, company_id, status)
                VALUES (:employee_id, :username, :password, :email, :fname, :mname, :lname, :suffix, :birthday, :phone, :country, :province, :city_municipality, :brgy, :position_id, :role_id, :profile_picture, :company_id, 'active')";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':employee_id' => $employee_id,
            ':username' => $username,
            ':password' => $hashed_password,
            ':email' => $email,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':suffix' => $suffix,
            ':birthday' => $birthday,
            ':phone' => $phone,
            ':country' => $country,
            ':province' => $province,
            ':city_municipality' => $city_municipality,
            ':brgy' => $brgy,
            ':position_id' => $position_id,
            ':role_id' => $role_id,
            ':profile_picture' => $profile_picture,
            ':company_id' => $company_id
        ]);

        return "User registered successfully.";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

// Function to update user profile, including password
function updateUser($user_id, $email, $fname, $mname, $lname, $suffix, $birthday, $phone, $country, $province, $city_municipality, $brgy, $position_id, $role_id, $profile_picture, $status, $company_id, $new_password = null) {
    global $conn;

    try {
        // If a new password is provided, hash it using SHA-256
        $password_sql = "";
        $params = [
            ':email' => $email,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':suffix' => $suffix,
            ':birthday' => $birthday,
            ':phone' => $phone,
            ':country' => $country,
            ':province' => $province,
            ':city_municipality' => $city_municipality,
            ':brgy' => $brgy,
            ':position_id' => $position_id,
            ':role_id' => $role_id,
            ':profile_picture' => $profile_picture,
            ':status' => $status,
            ':company_id' => $company_id,
            ':user_id' => $user_id
        ];

        if (!empty($new_password)) {
            $password = hash('sha256', $new_password);
            $password_sql = ", password = :password";
            $params[':password'] = $password;
        }

        $sql = "UPDATE users 
                SET email = :email,
                    fname = :fname,
                    mname = :mname,
                    lname = :lname,
                    suffix = :suffix,
                    birthday = :birthday,
                    phone = :phone,
                    country = :country,
                    province = :province,
                    city_municipality = :city_municipality,
                    brgy = :brgy,
                    position_id = :position_id,
                    role_id = :role_id,
                    profile_picture = :profile_picture,
                    status = :status,
                    company_id = :company_id
                    $password_sql
                WHERE id = :user_id";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);

        return $result;
    } catch (PDOException $e) {
        echo "Error updating user: " . $e->getMessage();
        return false;
    }
}

// Function to delete a user
function deleteUser($user_id) {
    global $conn;

    try {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $user_id]);
        return $stmt->rowCount() > 0; // Return true if a row was deleted
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}

// Function to list all users
function listUsers() {
    global $conn;

    try {
        $sql = "SELECT 
                    users.id,
                    users.employee_id,
                    users.username,
                    users.email,
                    users.fname,
                    users.mname,
                    users.lname,
                    users.suffix,
                    users.birthday,
                    users.phone,
                    users.country,
                    users.province,
                    users.city_municipality,
                    users.brgy,
                    positions.position_name,
                    roles.role_name,
                    COALESCE(companies.name, 'No Company') AS company_name, -- Handle NULL values
                    users.status
                FROM users
                LEFT JOIN positions ON users.position_id = positions.id
                LEFT JOIN roles ON users.role_id = roles.id
                LEFT JOIN companies ON users.company_id = companies.id";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

function getUserDetails($user_id) {
    global $conn;

    try {
        $sql = "SELECT 
                    users.id,
                    users.employee_id,
                    users.username,
                    users.email,
                    users.fname,
                    users.mname,
                    users.lname,
                    users.suffix,
                    users.birthday,
                    users.phone,
                    users.country,
                    users.province,
                    users.city_municipality,
                    users.brgy,
                    users.position_id,
                    users.role_id,
                    users.profile_picture,
                    users.status,
                    users.company_id, -- Include company_id
                    companies.name AS company_name -- Optional: Fetch company name
                FROM users
                LEFT JOIN companies ON users.company_id = companies.id
                WHERE users.id = :user_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching user details: " . $e->getMessage();
        return false;
    }
}

// Function to list all roles
function getRoles() {
    global $conn;

    try {
        $sql = "SELECT id, role_name FROM roles";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Function to get positions
function getPositions() {
    global $conn;

    try {
        $sql = "SELECT id, position_name FROM positions";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching positions: " . $e->getMessage();
        return [];
    }
}

function getCompanies() {
    global $conn;
    try {
        $sql = "SELECT id, name FROM companies";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching companies: " . $e->getMessage());
        return [];
    }
}