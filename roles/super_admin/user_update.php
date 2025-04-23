<?php
include "../super_admin/user_func.php";

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id === 0) {
    die("Invalid user ID");
}

$user = getUserDetails($user_id);
$roles = getRoles();
$positions = getPositions();
include "../super_admin/header.php";

// Define color scheme
$insuranceBlue = '#2A3F54';
$professionalTeal = '#1ABC9C';
$trustworthyNavy = '#0F1C2D';
$accentSky = '#3498DB';
$textPrimary = '#4A6572';
?>

<style>
    .modal-content {
        border-radius: 10px;
    }
    
    .modal-header {
        background: <?= $insuranceBlue ?>;
        color: white;
        border-radius: 10px 10px 0 0 !important;
    }
    
    .nav-tabs .nav-link {
        color: <?= $textPrimary ?>;
        font-size: 0.9rem;
        padding: 8px 12px;
    }
    
    .nav-tabs .nav-link.active {
        color: <?= $insuranceBlue ?>;
        font-weight: 500;
        border-bottom: 2px solid <?= $professionalTeal ?>;
    }
    
    .form-label {
        font-size: 0.85rem;
        color: <?= $textPrimary ?>;
    }
    
    .form-control, .form-select {
        font-size: 0.85rem;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: <?= $professionalTeal ?>;
        box-shadow: 0 0 0 0.25rem rgba(26, 188, 156, 0.25);
    }
    
    .profile-picture-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 2px solid <?= $professionalTeal ?>;
        border-radius: 50%;
    }
    
    .btn-save {
        background-color: <?= $professionalTeal ?>;
        border-color: <?= $professionalTeal ?>;
        color: white;
    }
    
    .btn-save:hover {
        background-color: #17a78b;
        border-color: #17a78b;
    }
</style>

<!-- Button to trigger modal (you can place this wherever you want in your page) -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
    <i class="fas fa-edit me-2"></i> Edit User
</button>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="user_update.php?id=<?= $user['id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">Personal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button">Address</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button">Employment</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="userTabsContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="mname" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="mname" value="<?= htmlspecialchars($user['mname']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <input type="text" class="form-control" name="suffix" value="<?= htmlspecialchars($user['suffix']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="../uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>" 
                                             class="profile-picture-preview mt-2" 
                                             id="profilePicturePreview">
                                    <?php else: ?>
                                        <img src="https://www.w3schools.com/howto/img_avatar.png" 
                                             class="profile-picture-preview mt-2" 
                                             id="profilePicturePreview">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information Tab -->
                        <div class="tab-pane fade" id="address" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" name="country" value="<?= htmlspecialchars($user['country']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" class="form-control" name="province" value="<?= htmlspecialchars($user['province']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="city_municipality" class="form-label">City/Municipality</label>
                                    <input type="text" class="form-control" name="city_municipality" value="<?= htmlspecialchars($user['city_municipality']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="brgy" class="form-label">Barangay</label>
                                    <input type="text" class="form-control" name="brgy" value="<?= htmlspecialchars($user['brgy']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employment Information Tab -->
                        <div class="tab-pane fade" id="employment" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="position_id" class="form-label">Position <span class="text-danger">*</span></label>
                                    <select class="form-select" name="position_id" required>
                                        <?php foreach ($positions as $position): ?>
                                            <option value="<?= $position['id'] ?>" <?= ($user['position_id'] == $position['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($position['position_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role_id" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>" <?= ($user['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($role['role_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" name="status" required>
                                        <option value="active" <?= ($user['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($user['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview profile picture when selected
document.querySelector('input[name="profile_picture"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('profilePicturePreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// AJAX form submission
document.querySelector('#editUserModal form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.json();
        }
    })
    .then(data => {
        if (data && data.success) {
            // Show success toast
            const toast = new bootstrap.Toast(document.createElement('div'));
            toast._element.className = 'toast align-items-center text-white bg-success';
            toast._element.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">User updated successfully!</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast._element);
            toast.show();
            
            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1500);
        } else if (data && data.error) {
            showError(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred while updating the user.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save Changes';
    });
});

function showError(message) {
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger alert-dismissible fade show mb-3';
    errorAlert.role = 'alert';
    errorAlert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const modalBody = document.querySelector('#editUserModal .modal-body');
    modalBody.prepend(errorAlert);
    
    setTimeout(() => {
        errorAlert.classList.remove('show');
        setTimeout(() => errorAlert.remove(), 150);
    }, 5000);
}
</script>

<?php include "../super_admin/footer.php"; ?>