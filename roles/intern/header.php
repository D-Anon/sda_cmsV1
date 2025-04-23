<?php
$activePage = basename($_SERVER['PHP_SELF'], ".php");
$systemPages = ['position', 'deduction', 'bonuses', 'system_management'];
?>

<head>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <title>Company Management System</title>
    
    <!-- FontAwesome for "fa" icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Bootstrap Icons for "bi" icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <link rel="icon" type="image/png" href="../../assets/img/SDALOGO.png">
</head>

<style>
    :root {
        --insurance-blue: #2A3F54;
        --professional-teal: #1ABC9C;
        --trustworthy-navy: #0F1C2D;
        --accent-sky: #3498DB;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 60px;
    }

    * {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar {
        background: white;
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        box-shadow: 2px 0 10px rgba(42, 63, 84, 0.1);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar.collapsed .sidebar-header h2,
    .sidebar.collapsed .sidebar-nav li a span {
        display: none;
    }

    .sidebar.collapsed .sidebar-nav li a {
        padding: 15px;
        justify-content: center;
    }

    .sidebar.collapsed .sidebar-nav li a i {
        margin-right: 0;
        font-size: 1.2rem;
    }

    .sidebar.collapsed .toggle-btn {
        right: 10px;
        left: auto;
        border-radius: 5px;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(42, 63, 84, 0.1);
        text-align: center;
    }

    .sidebar-header img {
        width: 70%;
        height: auto;
        margin-bottom: 10px;
        transition: all 0.3s;
    }

    .sidebar.collapsed .sidebar-header img {
        width: 80%;
    }

    .sidebar-nav ul {
        list-style: none;
        padding: 20px 0;
        margin: 0;
    }

    .sidebar-nav li a {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: var(--insurance-blue);
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 15px;
        font-weight: 500;
    }

    .sidebar-nav li a:hover {
        background: rgba(42, 63, 84, 0.05);
        color: var(--professional-teal);
    }

    .sidebar-nav li a i {
        margin-right: 10px;
        width: 20px;
        color: var(--insurance-blue);
    }

    .sidebar-nav .active a {
        background: var(--insurance-blue);
        color: white;
    }

    .sidebar-nav .active a i {
        color: white;
    }

    .toggle-btn {
        position: absolute;
        right: -35px;
        top: 15px;
        background: var(--insurance-blue);
        color: white;
        border: none;
        padding: 8px 10px;
        cursor: pointer;
        border-radius: 0 5px 5px 0;
        z-index: 1001;
        transition: all 0.3s;
    }

    .toggle-btn:hover {
        background: var(--trustworthy-navy);
    }

    .main-content {
        margin-left: var(--sidebar-width);
        transition: margin-left 0.3s;
        padding: 20px;
    }

    .sidebar.collapsed~.main-content {
        margin-left: var(--sidebar-collapsed-width);
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .sidebar:not(.collapsed) {
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
        }
    }

    .submenu {
        display: none;
        list-style: none;
        padding-left: 20px;
        background: rgba(42, 63, 84, 0.03);
    }

    .submenu li a {
        padding: 12px 20px 12px 35px;
        font-size: 14px;
        position: relative;
    }

    .submenu li a:before {
        content: "";
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: var(--professional-teal);
        border-radius: 50%;
    }

    .sidebar-nav li.active .submenu,
    .sidebar-nav li.open .submenu {
        display: block;
    }

    /* Collapsed sidebar submenu */
    .sidebar.collapsed .submenu {
        position: absolute;
        left: var(--sidebar-collapsed-width);
        top: 0;
        background: white;
        min-width: 200px;
        box-shadow: 2px 2px 10px rgba(42, 63, 84, 0.1);
        z-index: 1001;
        padding-left: 0;
    }

    .sidebar.collapsed .submenu li a {
        padding: 10px 15px 10px 25px;
    }

    .sidebar.collapsed .submenu li a:before {
        left: 10px;
    }

    .sidebar.collapsed .submenu li a span {
        display: inline-block;
    }
</style>

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="toggleBtn"><i class="fas fa-bars"></i></button>
    <div class="sidebar-header">
        <img src="../../assets/img/SDALOGO.png" alt="Company Logo">
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
            </li>
            <li class="<?= ($activePage == 'assigned_task') ? 'active' : '' ?>">
                <a href="assigned_task.php"><i class="fas fa-tasks"></i><span>Assigned Task</span></a>
            </li>
            <li class="<?= ($activePage == 'intern_timelogs') ? 'active' : '' ?>">
                <a href="intern_timelogs.php"><i class="fas fa-clock"></i><span> Time Logs</span></a>
            </li>
            <li class="<?= ($activePage == 'intern_profile') ? 'active' : '' ?>">
                <a href="intern_profile.php"><i class="fas fa-users-cog"></i><span> User Profile</span></a>
            </li>
            <li class="<?= ($activePage == 'logout') ? 'active' : '' ?>">
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </li>
        </ul>
    </nav>
</div>

<div class="main-content" id="main-content">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        // Toggle sidebar and close any open submenus
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const wasCollapsed = sidebar.classList.contains('collapsed');
            sidebar.classList.toggle('collapsed');

            // If collapsing the sidebar, close all submenus
            if (!wasCollapsed) {
                document.querySelectorAll('.sidebar-nav li.open').forEach(li => {
                    li.classList.remove('open');
                });
            }
        });

        // Handle dropdown toggles
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const parentLi = this.parentElement;
                const wasOpen = parentLi.classList.contains('open');

                // Close all dropdowns first
                document.querySelectorAll('.sidebar-nav li.open').forEach(li => {
                    li.classList.remove('open');
                });

                // Toggle current dropdown only if it wasn't already open
                if (!wasOpen) {
                    parentLi.classList.add('open');
                }
            });
        });

        // Close submenus when clicking outside
        document.addEventListener('click', (e) => {
            // Close all submenus
            document.querySelectorAll('.sidebar-nav li.open').forEach(li => {
                li.classList.remove('open');
            });

            // Collapse sidebar on mobile when clicking outside
            if (window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.add('collapsed');
            }
        });

        // Keep dropdowns open when clicking inside
        document.querySelectorAll('.submenu').forEach(submenu => {
            submenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>