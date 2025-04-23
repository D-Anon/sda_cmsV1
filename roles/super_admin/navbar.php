<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">

    <!-- FontAwesome for "fa" icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Bootstrap Icons for "bi" icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #3e25af;
            color: white;
            padding: 20px;
            transition: all 0.3s ease-in-out;
            overflow: hidden;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar.collapsed {
            width: 70px;
            padding: 20px 10px;
        }

        .sidebar img {
            width: 120px;
            height: 120px;
            display: block;
            margin: 5px auto 0;
            transition: all 0.3s;
        }

        .sidebar.collapsed img {
            display: none;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar ul li {
            margin: 10px 0;
            padding: 10px;
            transition: background 0.3s ease-in-out;
            border-radius: 5px;
        }

        .sidebar ul li:hover {
            background: rgba(255, 255, 255, 0.09);
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .sidebar ul li a i {
            margin-right: 15px;
            font-size: 20px;
        }

        .sidebar.collapsed ul li a span {
            display: none;
        }

        .sidebar.collapsed ul li a {
            justify-content: center;
        }

        .sidebar.collapsed ul li a i {
            margin-right: 0;
        }

        /* Toggle Button */
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 220px;
            background: #3e25af;
            border: none;
            color: white;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar.collapsed+.toggle-btn {
            left: 40px;
        }

        .toggle-btn:hover {
            background: #2a1a7e;
        }

        /* Main Content */
        .content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            transition: all 0.3s ease-in-out;
            width: calc(100% - 250px);
        }

        .sidebar.collapsed~.content {
            margin-left: 70px;
            width: calc(100% - 70px);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <img src="../../assets/img/SDALOGO.png" alt="SDA Logo">
        <ul>
            <li><a href="dashboard.php"><i class="bi bi-house-door"></i> <span>Home</span></a></li>
            <li><a href="task_management.php"><i class="bi bi-list-task"></i> <span>Task Management</span></a></li>
            <li><a href="time_management.php"><i class="bi bi-clock"></i> <span>Time Management</span></a></li>
            <li><a href="event_management.php"><i class="bi bi-calendar-event"></i> <span>Event Management</span></a></li>
            <li><a href="user_management.php"><i class="bi bi-people"></i> <span>User Management</span></a></li>
            <li><a href="payroll_management.php"><i class="bi bi-cash"></i> <span>Payroll Management</span></a></li>
            <!-- Dropdown for System Management -->
            <li>
                <a class="nav-link" data-bs-toggle="collapse" href="#menuCollapseSystem">
                    <i class="bi bi-three-dots"></i> <span>System Management</span>
                </a>
                <div class="collapse" id="menuCollapseSystem">
                    <ul class="nav flex-column ps-3">
                        <li><a class="nav-link text-light" href="systempositions.php"><i class="bi bi-person"></i>Positions</a></li>
                        <li><a class="nav-link text-light" href="systemdeductions.php"><i class="bi bi-dash-circle"></i>Deductions</a></li>
                        <li><a class="nav-link text-light" href="bonus.php"><i class="bi bi-coin"></i>Bonuses</a></li>
                        <i><a class="nav-link text-light" href="system_companies.php"><i class="bi bi-coin"></i></a>Companies</i>
                    </ul>
                </div>
            </li>
            <li><a href="../../logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Toggle Button -->
    <button class="toggle-btn" id="toggleBtn">☰</button>

    <script>
        document.getElementById('toggleBtn').addEventListener('click', function() {
            let sidebar = document.getElementById('sidebar');
            let content = document.getElementById('mainContent');
            let toggleBtn = document.getElementById('toggleBtn');

            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');

            // Adjust toggle button position dynamically
            if (sidebar.classList.contains('collapsed')) {
                toggleBtn.style.left = "80px";
            } else {
                toggleBtn.style.left = "260px";
            }
        });
    </script>
</body>

</html>