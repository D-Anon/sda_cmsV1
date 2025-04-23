<?php
session_start();
include "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = hash("sha256", $password); // Hash input password before checking

    $stmt = $conn->prepare("SELECT u.id, u.username, u.password, r.role_name 
                            FROM users u
                            JOIN roles r ON u.role_id = r.id
                            WHERE u.username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the hashed input password matches the stored hashed password
    if ($user && $hashed_password === $user['password']) {
        // ✅ Store user data in session
        $_SESSION['user_id'] = $user['id'];  
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];

        // Redirect based on role
        header("Location: roles/" . strtolower($user['role_name']) . "/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center">Login</h3>
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <form method="post">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
