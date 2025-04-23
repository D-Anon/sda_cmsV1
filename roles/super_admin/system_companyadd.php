<?php
include "../super_admin/system_companyfunc.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $result = addCompany($name, $address, $phone);

    if ($result === true) {
        header("Location: system_companies.php?success=1");
        exit;
    } else {
        $error_message = $result;
    }
}

include "../super_admin/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company</title>
    <style>
        :root {
            --insurance-blue: #2A3F54;
            --professional-teal: #1ABC9C;
            --trustworthy-navy: #0F1C2D;
            --accent-sky: #3498DB;
            --text-primary: #4A6572;
        }

        .company-form-card {
            border-radius: 12px;
            border: 1px solid rgba(42, 63, 84, 0.1);
            box-shadow: 0 4px 12px rgba(42, 63, 84, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .company-form-header {
            background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
        }

        .btn-insurance {
            background: var(--insurance-blue);
            color: white;
            border: none;
            transition: all 0.2s;
        }

        .btn-insurance:hover {
            background: var(--trustworthy-navy);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(42, 63, 84, 0.1);
        }

        .btn-outline-insurance {
            border: 1px solid var(--insurance-blue);
            color: var(--insurance-blue);
            background: transparent;
        }

        .btn-outline-insurance:hover {
            background: var(--insurance-blue);
            color: white;
        }

        .form-label {
            color: var(--insurance-blue);
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--accent-sky);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="company-form-card">
            <div class="card-header company-form-header py-3">
                <h2 class="mb-0 text-center">
                    <i class="fas fa-building me-2"></i>Add New Company
                </h2>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label for="name" class="form-label">Company Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-building" style="color: var(--insurance-blue);"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Enter company name" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-map-marker-alt" style="color: var(--insurance-blue);"></i>
                            </span>
                            <input type="text" class="form-control" id="address" name="address" 
                                   placeholder="Enter full address" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone" style="color: var(--insurance-blue);"></i>
                            </span>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   placeholder="Enter contact number" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="submit" class="btn btn-insurance px-4">
                            <i class="fas fa-save me-2"></i>Create Company
                        </button>
                        <a href="system_companies.php" class="btn btn-outline-insurance px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php include "../super_admin/footer.php"; ?>