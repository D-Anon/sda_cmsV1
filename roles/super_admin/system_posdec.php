<?php
include "system_func.php";

if (isset($_GET['position_id'])) {
    $positionId = intval($_GET['position_id']);
    $deductions = getDeductionsByPosition($positionId); // Fetch deductions for the position
    echo json_encode($deductions); // Return deductions as JSON
} else {
    echo json_encode([]); // Return an empty array if no position_id is provided
}