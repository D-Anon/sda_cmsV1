<?php
// Start the session
session_start();
include "../intern/header.php";
include "../intern/db.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get the user's ID from the session
$user_id = $_SESSION['user_id'];

// Query to retrieve the user's information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user exists
if (!$user) {
    header("Location: ../../login.php");
    exit();
}

// Define color scheme from event management system
$insuranceBlue = '#2A3F54';
$professionalTeal = '#1ABC9C';
$trustworthyNavy = '#0F1C2D';
$accentSky = '#3498DB';
$textPrimary = '#4A6572';
?>

<style>
    body {
        background-color: #f8f9fa;
        color: <?= $textPrimary ?>;
        font-size: 0.9rem;
    }
    
    .container-fluid {
        padding: 15px;
    }
    
    .card {
        border-radius: 8px;
        margin-bottom: 0;
    }
    
    .profile-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid <?= $professionalTeal ?>;
    }
    
    .section-title {
        font-size: 1rem;
        color: <?= $insuranceBlue ?>;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }
    
    .info-card {
        margin-bottom: 10px;
        border-left: 3px solid <?= $professionalTeal ?>;
        padding: 8px;
        background: white;
        border-radius: 4px;
    }
    
    .info-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 2px;
    }
    
    .info-value {
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .quick-link {
        font-size: 0.85rem;
        padding: 5px 0;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .form-label {
        font-size: 0.85rem;
        margin-bottom: 3px;
    }
    
    .form-control, .form-select {
        font-size: 0.85rem;
        padding: 5px 10px;
        height: calc(1.5em + 0.75rem + 2px);
    }
    
    .nav-tabs .nav-link {
        font-size: 0.85rem;
        padding: 8px 12px;
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 0.85rem;
    }
</style>

<div class="container py-2">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center p-3">
                    <img src="<?= $user['profile_picture'] ? $user['profile_picture'] : '../../assets/img/SDALOGO.png' ?>"
                         class="rounded-circle profile-img mb-2">
                    
                    <h5 class="mb-1" style="color: <?= $insuranceBlue ?>;"><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></h5>
                    <span class="badge mb-2" style="background: <?= $professionalTeal ?>; color: white; font-size: 0.8rem;">
                        <?= htmlspecialchars($user['position']) ?>
                    </span>
                    
                    <button class="btn btn-sm w-100 mb-3" style="background: <?= $insuranceBlue ?>; color: white;" 
                            data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </button>
                    
                    <div class="border-top pt-2">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem;">Quick Links</h6>
                        <div>
                            <a href="intern_timelogs.php" class="d-block quick-link text-decoration-none" style="color: <?= $insuranceBlue ?>;">
                                <i class="fas fa-calendar-alt me-2"></i>My Time Logs
                            </a>
                            <a href="assigned_task.php" class="d-block quick-link text-decoration-none" style="color: <?= $insuranceBlue ?>;">
                                <i class="fas fa-tasks me-2"></i>My Tasks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header" style="background: <?= $insuranceBlue ?>; color: white; padding: 10px 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="font-size: 1.1rem;"><i class="fas fa-user-circle me-2"></i>Employee Profile</h5>
                        <small style="font-size: 0.8rem;"><?= date('F j, Y') ?></small>
                    </div>
                </div>
                
                <div class="card-body p-3">
                    <!-- Personal Information -->
                    <h6 class="section-title"><i class="fas fa-user-circle me-2"></i>Personal Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-id-card me-2"></i>Employee ID</div>
                                <div class="info-value"><?= htmlspecialchars($user['employee_id']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user-tag me-2"></i>Username</div>
                                <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-birthday-cake me-2"></i>Birthday</div>
                                <div class="info-value"><?= !empty($user['birthday']) ? date('F j, Y', strtotime($user['birthday'])) : 'Not set' ?></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-user me-2"></i>Full Name</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($user['fname'] . ' ' .
                                        (!empty($user['mname']) ? $user['mname'] . ' ' : '') .
                                        $user['lname'] .
                                        (!empty($user['suffix']) ? ' ' . $user['suffix'] : '')) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-info-circle me-2"></i>Status</div>
                                <div class="info-value">
                                    <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'secondary' ?>" style="font-size: 0.8rem;">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h6 class="section-title mt-3"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-envelope me-2"></i>Email</div>
                                <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-phone me-2"></i>Phone</div>
                                <div class="info-value"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not set' ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <h6 class="section-title mt-3"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-home me-2"></i>Full Address</div>
                                <div class="info-value"><?= !empty($user['address']) ? htmlspecialchars($user['address']) : 'Not set' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-map me-2"></i>Location Details</div>
                                <div class="info-value">
                                    <?php
                                    $locationParts = [];
                                    if (!empty($user['brgy'])) $locationParts[] = $user['brgy'];
                                    if (!empty($user['city_municipality'])) $locationParts[] = $user['city_municipality'];
                                    if (!empty($user['province'])) $locationParts[] = $user['province'];
                                    if (!empty($user['country'])) $locationParts[] = $user['country'];
                                    echo !empty($locationParts) ? htmlspecialchars(implode(', ', $locationParts)) : 'Not set';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Company Information -->
                    <h6 class="section-title mt-3"><i class="fas fa-building me-2"></i>Company Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-briefcase me-2"></i>Position</div>
                                <div class="info-value"><?= !empty($user['position']) ? htmlspecialchars($user['position']) : 'Not set' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-label"><i class="fas fa-calendar-day me-2"></i>Member Since</div>
                                <div class="info-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer" style="background: #f8f9fa; padding: 8px 15px; font-size: 0.8rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Profile last updated: <?= date('F j, Y') ?></small>
                        <small class="text-muted" style="color: <?= $insuranceBlue ?>;"><?= htmlspecialchars($user['fname']) ?>'s Profile</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal (Compact Version) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: <?= $insuranceBlue ?>; color: white; padding: 10px 15px;">
                <h5 class="modal-title" style="font-size: 1.1rem;" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>
            
            <form id="profileForm" enctype="multipart/form-data">
                <div class="modal-body p-3">
                    <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist" style="font-size: 0.85rem;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">Address</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">Employment</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="fname" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="mname" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control form-control-sm" id="mname" name="mname" value="<?= htmlspecialchars($user['mname']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="lname" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <input type="text" class="form-control form-control-sm" id="suffix" name="suffix" value="<?= htmlspecialchars($user['suffix']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="birthday" name="birthday" value="<?= $user['birthday'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control form-control-sm" id="profile_picture" name="profile_picture" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-sm" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control form-control-sm" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information Tab -->
                        <div class="tab-pane fade" id="address" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label for="address" class="form-label">Complete Address</label>
                                    <textarea class="form-control form-control-sm" id="address" name="address" rows="2"><?= htmlspecialchars($user['address']) ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control form-control-sm" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" class="form-control form-control-sm" id="province" name="province" value="<?= htmlspecialchars($user['province']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="city_municipality" class="form-label">City/Municipality</label>
                                    <input type="text" class="form-control form-control-sm" id="city_municipality" name="city_municipality" value="<?= htmlspecialchars($user['city_municipality']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="brgy" class="form-label">Barangay</label>
                                    <input type="text" class="form-control form-control-sm" id="brgy" name="brgy" value="<?= htmlspecialchars($user['brgy']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Employment Information Tab -->
                        <div class="tab-pane fade" id="employment" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select form-select-sm" id="status" name="status">
                                        <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const editProfileModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        
        const formData = new FormData(this);
        
        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success toast
                const toast = new bootstrap.Toast(document.createElement('div'));
                toast._element.className = 'toast align-items-center text-white bg-success';
                toast._element.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">Profile updated successfully!</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                document.body.appendChild(toast._element);
                toast.show();
                
                // Close modal and reload
                editProfileModal.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.error || 'Failed to update profile');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while updating the profile.');
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
        
        const modalBody = document.querySelector('#editProfileModal .modal-body');
        modalBody.prepend(errorAlert);
        
        setTimeout(() => {
            errorAlert.classList.remove('show');
            setTimeout(() => errorAlert.remove(), 150);
        }, 5000);
    }
});
</script>