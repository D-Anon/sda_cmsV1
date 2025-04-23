<?php
ob_start();

include "../super_admin/header.php";
include "system_func.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: system_management.php?error=Invalid deduction ID');
    exit();
}

$deduction_id = intval($_GET['id']);
$deduction = getDeductionById($deduction_id);

if (!$deduction) {
    header('Location: system_management.php?error=Deduction not found');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deduction_name = trim($_POST['deduction_name']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);

    if (updateDeduction($deduction_id, $deduction_name, $amount, $description)) {
        header('Location: system_management.php?success=Deduction updated successfully');
        exit();
    } else {
        $error = "Failed to update deduction.";
    }
}

ob_end_flush(); // End output buffering
?>

<div class="container mt-5">
    <h1 class="text-center">Edit Deduction</h1>
    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
    <form action="system_editduc.php?id=<?php echo $deduction_id; ?>" method="POST">
        <div class="mb-3">
            <label for="deduction_name" class="form-label">Deduction Name</label>
            <input type="text" class="form-control" id="deduction_name" name="deduction_name" value="<?php echo htmlspecialchars($deduction['deduction_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($deduction['amount']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($deduction['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Deduction</button>
        <a href="system_management.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include "../super_admin/footer.php"; ?>