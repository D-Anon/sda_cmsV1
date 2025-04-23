<?php
session_start();
include "../intern/db.php";

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];

try {
    // Handle file upload if present
    $profile_picture = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = '../../assets/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF images are allowed.");
        }
        
        // Check file size (max 2MB)
        if ($_FILES['profile_picture']['size'] > 2097152) {
            throw new Exception("File size must be less than 2MB.");
        }
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            $profile_picture = $fileName;
            
            // Delete old profile picture if it exists
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $oldPicture = $stmt->fetchColumn();
            
            if ($oldPicture && file_exists($uploadDir . $oldPicture)) {
                unlink($uploadDir . $oldPicture);
            }
        }
    }
    
    // Get position name from position ID
    $positionStmt = $conn->prepare("SELECT position_name FROM positions WHERE id = :position_id");
    $positionStmt->execute([':position_id' => $_POST['position_id']]);
    $position = $positionStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$position) {
        throw new Exception("Invalid position selected");
    }
    
    // Prepare update query
    $updateFields = [
        'fname' => $_POST['fname'],
        'mname' => $_POST['mname'],
        'lname' => $_POST['lname'],
        'suffix' => $_POST['suffix'],
        'birthday' => $_POST['birthday'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'country' => $_POST['country'],
        'province' => $_POST['province'],
        'city_municipality' => $_POST['city_municipality'],
        'brgy' => $_POST['brgy'],
        'status' => $_POST['status']
    ];
    
    if ($profile_picture) {
        $updateFields['profile_picture'] = $profile_picture;
    }
    
    $updateQuery = "UPDATE users SET ";
    $params = [];
    foreach ($updateFields as $field => $value) {
        $updateQuery .= "$field = :$field, ";
        $params[":$field"] = $value;
    }
    $updateQuery = rtrim($updateQuery, ', ') . " WHERE id = :user_id";
    $params[':user_id'] = $user_id;
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute($params);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}