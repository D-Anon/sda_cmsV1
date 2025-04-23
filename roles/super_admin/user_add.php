<?php
include "../super_admin/user_func.php";
include "../super_admin/system_companyfunc.php"; // Assuming this file contains the function to get companies

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $employee_id = $_POST['employee_id'];
    $position_id = $_POST['position_id'];
    $role_id = $_POST['role_id'];
    $company_id = $_POST['company_id'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $suffix = $_POST['suffix'];
    $birthday = $_POST['birthday'];
    $phone = $_POST['phone'];
    $country = $_POST['country'];
    $province = $_POST['province'];
    $city_municipality = $_POST['city_municipality'];
    $brgy = $_POST['brgy'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $profile_picture = null;

    // Handle file upload for profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/profile_pictures/";
        $file_name = basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $file_name;
        }
    }

    // Save user to the database using registerUser
    $result = registerUser($employee_id, $username, $_POST['password'], $email, $fname, $mname, $lname, $suffix, $birthday, $phone, $country, $province, $city_municipality, $brgy, $position_id, $role_id, $profile_picture, $company_id);

    if ($result === "User registered successfully.") {
        // Redirect to user management page with success message
        header("Location: user_management.php?success=1");
        exit;
    } else {
        $error_message = $result; // Display the error message returned by registerUser
    }
}

include "../super_admin/header.php";
$roles = getRoles();
$positions = getPositions();
$companies = listCompanies(); // Assuming a function `getCompanies()` exists in `user_func.php`
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .user-form-card {
            border-radius: 12px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 24px rgba(42, 63, 84, 0.1);
        }

        .form-header {
            color: var(--insurance-blue);
            border-bottom: 2px solid var(--professional-teal);
            padding-bottom: 1rem;
        }

        .form-label {
            color: var(--insurance-blue);
            font-weight: 500;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-sky);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .btn-insurance {
            background: var(--insurance-blue);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-insurance:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.15);
        }

        .btn-outline-insurance {
            border: 2px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
        }

        .password-toggle-btn {
            border: 2px solid #e9ecef;
            border-left: none;
            background: white;
            transition: all 0.2s;
        }

        .password-toggle-btn:hover {
            background: #f8f9fa;
        }

        .input-group-text {
            background: white;
            border-right: none;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .form-control:focus + .password-toggle-btn {
            border-color: var(--accent-sky);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card user-form-card p-4 mx-auto" style="max-width: 1200px;">
            <h2 class="text-center form-header mb-4">
                <i class="fas fa-user-plus me-2"></i>Add New User
            </h2>

            <form action="user_add.php" method="POST" enctype="multipart/form-data">
                <div class="row g-4">

                    <!-- Employee ID -->
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id" required placeholder="Enter employee ID">
                    </div>

                    <!-- Position -->
                    <div class="col-md-4">
                        <label for="position_id" class="form-label">Position</label>
                        <select class="form-select" id="position_id" name="position_id" required>
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?= $position['id']; ?>"><?= $position['position_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Role -->
                    <div class="col-md-4">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id']; ?>"><?= $role['role_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Company -->
                    <div class="col-md-4">
                        <label for="company_id" class="form-label">Company</label>
                        <select class="form-select" id="company_id" name="company_id" required>
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id']; ?>"><?= $company['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- First Name -->
                    <div class="col-md-4">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="fname" name="fname" required placeholder="Enter first name">
                    </div>

                    <!-- Middle Name -->
                    <div class="col-md-4">
                        <label for="mname" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="mname" name="mname" placeholder="Enter middle name">
                    </div>

                    <!-- Last Name -->
                    <div class="col-md-4">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lname" name="lname" required placeholder="Enter last name">
                    </div>

                    <!-- Suffix -->
                    <div class="col-md-4">
                        <label for="suffix" class="form-label">Suffix</label>
                        <select class="form-select" id="suffix" name="suffix">
                            <option value="">None</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                        </select>
                    </div>

                    <!-- Birthday -->
                    <div class="col-md-4">
                        <label for="birthday" class="form-label">Birthday</label>
                        <input type="date" class="form-control" id="birthday" name="birthday" required>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-4">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required placeholder="Enter phone number">
                    </div>

                    <!-- Address -->
                    <div class="col-md-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" required placeholder="Enter country">
                    </div>

                    <div class="col-md-3">
                        <label for="province" class="form-label">Province</label>
                        <input type="text" class="form-control" id="province" name="province" required placeholder="Enter province">
                    </div>

                    <div class="col-md-3">
                        <label for="city_municipality" class="form-label">City/Municipality</label>
                        <input type="text" class="form-control" id="city_municipality" name="city_municipality" required placeholder="Enter city/municipality">
                    </div>

                    <div class="col-md-3">
                        <label for="brgy" class="form-label">Barangay</label>
                        <input type="text" class="form-control" id="brgy" name="brgy" required placeholder="Enter barangay">
                    </div>

                    <!-- Email -->
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
                    </div>

                    <!-- Username -->
                    <div class="col-md-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
                    </div>

                    <!-- Password -->
                    <div class="col-md-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                            <button class="btn password-toggle-btn" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Profile Picture -->
                    <div class="col-md-12">
                        <label for="profile_picture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="mt-4 d-flex justify-content-between">
                    <a href="user_management.php" class="btn btn-outline-insurance">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-insurance">
                        <i class="fas fa-save me-2"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            let passwordField = document.getElementById('password');
            let icon = this.querySelector('i');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    </script>

    <?php include "../super_admin/footer.php"; ?>
</body>
</html>