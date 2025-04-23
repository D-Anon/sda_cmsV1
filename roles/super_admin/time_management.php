<?php
include "../super_admin/header.php";
include "time_func.php"; // Include the time functions
$activePage = 'time_management'; // for dashboard.php

// Fetch time logs for all users
$time_logs = getTimeLogs(); // Call the function to fetch all time logs

// Filter by date
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$search_query = $_GET['search'] ?? null;

// Filter logs based on the date range and search query
$filtered_logs = array_filter($time_logs, function ($log) use ($start_date, $end_date, $search_query) {
    $log_date = $log['date'];
    $matches_date = (!$start_date || $log_date >= $start_date) && (!$end_date || $log_date <= $end_date);
    $matches_search = !$search_query || stripos($log['name'], $search_query) !== false || stripos($log['emp_id'], $search_query) !== false;
    return $matches_date && $matches_search;
});

// Pagination
$logs_per_page = 10;
$total_logs = count($filtered_logs);
$current_page = $_GET['page'] ?? 1;
$current_page = max(1, (int)$current_page);
$start_index = ($current_page - 1) * $logs_per_page;
$paginated_logs = array_slice($filtered_logs, $start_index, $logs_per_page);
?>

<style>
    :root {
        --insurance-blue: #2A3F54;
        --professional-teal: #1ABC9C;
        --trustworthy-navy: #0F1C2D;
        --accent-sky: #3498DB;
        --text-primary: #4A6572;
    }

    .time-log-card {
        border-radius: 12px;
        border: 1px solid rgba(42, 63, 84, 0.1);
        box-shadow: 0 4px 12px rgba(42, 63, 84, 0.05);
    }

    .time-log-header {
        background: linear-gradient(135deg, var(--insurance-blue) 0%, var(--trustworthy-navy) 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
    }

    .badge-insurance {
        background: var(--professional-teal);
        color: white;
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

    .table th {
        background: rgba(42, 63, 84, 0.05);
        color: var(--insurance-blue);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .table-hover tbody tr:hover {
        background: rgba(42, 63, 84, 0.03);
    }

    .time-am {
        color: var(--insurance-blue);
    }

    .time-pm {
        color: var(--accent-sky);
    }

    .time-ot {
        color: var(--professional-teal);
    }

    .total-hours {
        background: rgba(26, 188, 156, 0.1);
        color: var(--professional-teal);
    }

    .page-item.active .page-link {
        background: var(--insurance-blue);
        border-color: var(--insurance-blue);
    }

    .page-link {
        color: var(--insurance-blue);
    }

    .empty-state-icon {
        color: rgba(42, 63, 84, 0.2);
    }
</style>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-clock me-2" style="color: var(--insurance-blue);"></i>Employee Time Logs
        </h2>
        <div class="badge badge-insurance rounded-pill">Total Entries: <?= $total_logs ?></div>
    </div>

    <!-- Filter Card -->
    <div class="card time-log-card mb-3">
        <div class="card-header time-log-header py-3">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Logs</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label small text-muted mb-1">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label small text-muted mb-1">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label small text-muted mb-1">Search Employee</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="background: rgba(42, 63, 84, 0.1);">
                            <i class="fas fa-search" style="color: var(--insurance-blue);"></i>
                        </span>
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Name or Employee ID" value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-insurance btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <a href="time_management.php" class="btn btn-outline-insurance btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Time Logs Table -->
    <div class="card time-log-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">Employee ID</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Date</th>
                            <th class="py-3 text-center">AM</th>
                            <th class="py-3 text-center">PM</th>
                            <th class="py-3 text-center">OT</th>
                            <th class="py-3 text-center">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($paginated_logs)) { ?>
                            <?php foreach ($paginated_logs as $log) {
                                // Calculate hours
                                $am_hours = ($log['am_time_in'] && $log['am_time_out'])
                                    ? (strtotime($log['am_time_out']) - strtotime($log['am_time_in'])) / 3600
                                    : 0;

                                $pm_hours = ($log['pm_time_in'] && $log['pm_time_out'])
                                    ? (strtotime($log['pm_time_out']) - strtotime($log['pm_time_in'])) / 3600
                                    : 0;

                                $ot_hours = ($log['ot_time_in'] && $log['ot_time_out'])
                                    ? (strtotime($log['ot_time_out']) - strtotime($log['ot_time_in'])) / 3600
                                    : 0;

                                $total_hours = $am_hours + $pm_hours + $ot_hours;
                            ?>
                                <tr>
                                    <td class="align-middle"><?= htmlspecialchars($log['emp_id']) ?></td>
                                    <td class="align-middle fw-medium"><?= htmlspecialchars($log['name']) ?></td>
                                    <td class="align-middle"><?= date('M j, Y', strtotime($log['date'])) ?></td>

                                    <!-- AM Column -->
                                    <td class="text-center small time-am">
                                        <div class="text-nowrap"><?= $log['am_time_in'] ? date('g:i A', strtotime($log['am_time_in'])) : 'N/A' ?></div>
                                        <div class="text-nowrap"><?= $log['am_time_out'] ? date('g:i A', strtotime($log['am_time_out'])) : 'N/A' ?></div>
                                    </td>

                                    <!-- PM Column -->
                                    <td class="text-center small time-pm">
                                        <div class="text-nowrap"><?= $log['pm_time_in'] ? date('g:i A', strtotime($log['pm_time_in'])) : 'N/A' ?></div>
                                        <div class="text-nowrap"><?= $log['pm_time_out'] ? date('g:i A', strtotime($log['pm_time_out'])) : 'N/A' ?></div>
                                    </td>

                                    <!-- OT Column -->
                                    <td class="text-center small time-ot">
                                        <div class="text-nowrap"><?= $log['ot_time_in'] ? date('g:i A', strtotime($log['ot_time_in'])) : 'N/A' ?></div>
                                        <div class="text-nowrap"><?= $log['ot_time_out'] ? date('g:i A', strtotime($log['ot_time_out'])) : 'N/A' ?></div>
                                    </td>

                                    <td class="align-middle text-center">
                                        <span class="badge total-hours rounded-pill py-2 px-3">
                                            <?= number_format($total_hours, 2) ?> hrs
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-clock empty-state-icon fa-3x mb-3"></i>
                                    <p class="text-muted">No time logs found for selected filters</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_logs > $logs_per_page) { ?>
        <nav class="mt-3 d-flex justify-content-center">
            <ul class="pagination pagination-sm">
                <?php
                $total_pages = ceil($total_logs / $logs_per_page);
                $query_params = $_GET;
                unset($query_params['page']);

                // Previous Page
                if ($current_page > 1) {
                    $query_params['page'] = $current_page - 1;
                    echo '<li class="page-item">
                            <a class="page-link" href="?' . http_build_query($query_params) . '">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                          </li>';
                }

                // Page Numbers
                for ($i = 1; $i <= $total_pages; $i++) {
                    $query_params['page'] = $i;
                    $active = $i == $current_page ? ' active' : '';
                    echo '<li class="page-item' . $active . '">
                            <a class="page-link" href="?' . http_build_query($query_params) . '">' . $i . '</a>
                          </li>';
                }

                // Next Page
                if ($current_page < $total_pages) {
                    $query_params['page'] = $current_page + 1;
                    echo '<li class="page-item">
                            <a class="page-link" href="?' . http_build_query($query_params) . '">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                          </li>';
                }
                ?>
            </ul>
        </nav>
    <?php } ?>
</div>

<?php
include "../super_admin/footer.php";
?>